<?php
session_start();
include("dbconnect.php");



// Debugging (optional - remove in production)
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check session
if (!isset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['location'])) {
    echo "Session expired. Please log in again.";
    exit();
}

if (isset($_REQUEST['data'])) {
    $response_base64 = $_REQUEST['data'];
    $response_json = base64_decode($response_base64);
    $response = json_decode($response_json, true);

    // Extract response fields
    $transaction_code = $response['transaction_code'];
    $status = $response['status'];
    $total_amount = $response['total_amount'];
    $transaction_uuid = $response['transaction_uuid']; 
    $product_code = $response['product_code'];
    $signed_fields_names = $response['signed_field_names'];
    $provided_signature = $response['signature'];

    // Signature validation
    $secret_key = '8gBm/:&EnhH.1/q';
    $message = "transaction_code={$transaction_code},status={$status},total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code},signed_field_names={$signed_fields_names}";
    $expected_signature = base64_encode(hash_hmac('sha256', $message, $secret_key, true));

    if ($expected_signature === $provided_signature && $status === "COMPLETE") {
        // Payment is verified
        $user_id = $_SESSION['user_id'];
        $name = mysqli_real_escape_string($conn, $_SESSION['name']);
        $location = mysqli_real_escape_string($conn, $_SESSION['location']);
        $payment_option = "Online Payment";
        $grand_total = 0;

        // Fetch cart
        $select_cart = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
        if (mysqli_num_rows($select_cart) == 0) {
            echo "Your cart is empty.";
            exit();
        }

        // Calculate grand total
        while ($row = mysqli_fetch_assoc($select_cart)) {
            $price = $row['price'];
            $discount = $row['discount'];
            $shipping = $row['shipping'];
            $quantity = $row['quantity'];

            $discount_amount = ($discount / 100) * $price;
            $final_price = $price - $discount_amount + $shipping;
            $subtotal = round($final_price * $quantity, 2);
            $grand_total += $subtotal;
        }

        $grand_total = round($grand_total, 2);

        // Insert into orders table
        $insert_order = mysqli_query($conn, "INSERT INTO orders (grand_total, payment_option, location, payment_status, order_date, name, user_id, order_status, transaction_id) 
        VALUES ($grand_total, '$payment_option', '$location', 'Paid', NOW(), '$name', $user_id, 'Pending', '$transaction_code')");

        if ($insert_order) {
            $order_id = mysqli_insert_id($conn);
            mysqli_data_seek($select_cart, 0); // Reset result pointer

            // Insert order items
            while ($row = mysqli_fetch_assoc($select_cart)) {
                $product_id = $row['product_id'];
                $image = mysqli_real_escape_string($conn, $row['image']);
                $price = $row['price'];
                $discount = $row['discount'];
                $shipping = $row['shipping'];
                $quantity = $row['quantity'];

                $discount_amount = ($discount / 100) * $price;
                $final_price = $price - $discount_amount + $shipping;
                $subtotal = round($final_price * $quantity, 2);

                mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_image, price, quantity, subtotal) 
                VALUES ($order_id, $product_id, '$image', $final_price, $quantity, $subtotal)");
            }

            // Clear cart after successful order
            mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

            // Redirect to thank you page
            header("Location: thankyou.php?order_id=$order_id");
            exit();
        } else {
            echo "Failed to insert order.";
        }
    } else {
        echo "Signature mismatch or payment not complete.";
    }
} else {
    echo "Invalid or missing payment data.";
}
?>