<?php
session_start();
include("dbconnect.php");

// Read the response from eSewa
$raw_input = file_get_contents("php://input");
$response = json_decode($raw_input, true);

// Check if the response is valid
if ($response && isset($response['signature'])) {
    
    $transaction_code = $response['transaction_code'];
    $status = $response['status'];
    $total_amount = $response['total_amount'];
    $transaction_uuid = $response['transaction_uuid'];
    $product_code = $response['product_code'];
    $signed_fields = $response['signed_field_names'];
    $signature = $response['signature'];

    // Verify Signature
    $secret_key = '8gBm/:&EnhH.1/q'; // your secret key
    $fields = explode(',', $signed_fields);
    $signed_data = "";

    foreach ($fields as $index => $field) {
        if (isset($response[$field])) {
            $signed_data .= $field . "=" . $response[$field];
            if ($index < count($fields) - 1) {
                $signed_data .= ",";
            }
        }
    }

    $calculated_signature = base64_encode(hash_hmac('sha256', $signed_data, $secret_key, true));

    if ($signature == $calculated_signature && $status == "COMPLETE") {

        // Get session data
        $user_id = $_SESSION['user_id'];
        $name = $_SESSION['name'];
        $location = $_SESSION['location'];
        $payment_option = "Online Payment";

        // Get cart data
        $select_cart = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
        $grand_total = 0;

        while ($row = mysqli_fetch_assoc($select_cart)) {
            $price = $row['price'];
            $discount = $row['discount'];
            $shipping = $row['shipping'];
            $quantity = $row['quantity'];

            $discount_amount = ($discount / 100) * $price;
            $final_price = $price - $discount_amount + $shipping;
            $subtotal = $final_price * $quantity;
            $grand_total += $subtotal;
        }

        // Insert into orders table
        $insert_order = mysqli_query($conn, "INSERT INTO orders (grand_total, payment_option, location, payment_status, order_date, name, user_id, order_status, transaction_id)
        VALUES ($grand_total, '$payment_option', '$location', 'Paid', NOW(), '$name', $user_id, 'Pending', '$transaction_code')");

        if ($insert_order) {
            $order_id = mysqli_insert_id($conn);

            // Insert order items
            mysqli_data_seek($select_cart, 0); // reset result pointer
            while ($row = mysqli_fetch_assoc($select_cart)) {
                $product_id = $row['product_id'];
                $image = $row['image'];
                $price = $row['price'];
                $discount = $row['discount'];
                $shipping = $row['shipping'];
                $quantity = $row['quantity'];

                $discount_amount = ($discount / 100) * $price;
                $final_price = $price - $discount_amount + $shipping;
                $subtotal = $final_price * $quantity;

                mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_image, price, quantity, subtotal) 
                VALUES ($order_id, $product_id, '$image', $final_price, $quantity, $subtotal)");
            }

            // Clear cart
            mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

            // Redirect to thank you page
            header("Location: thankyou.php?order_id=$order_id");
            exit();
        } else {
            echo "Order insert failed.";
        }
    } else {
        echo "Signature verification failed or payment not completed.";
    }
} else {
    echo "Invalid payment response.";
}
?>
