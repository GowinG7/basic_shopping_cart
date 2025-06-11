<?php
session_start();
include("dbconnect.php");

// 1. Check if user session data is available
if (!isset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['location'])) {
    echo "Session expired. Please log in again.";
    exit();
}

// 2. Check if eSewa sent payment data
if (isset($_REQUEST['data'])) {
    // 3. Decode the payment data from eSewa
    $response_base64 = $_REQUEST['data'];
    $response_json = base64_decode($response_base64);
    $response = json_decode($response_json, true);

    // 4. Get important fields from the response
    $transaction_code = $response['transaction_code'] ?? '';
    $status = $response['status'] ?? '';
    $total_amount = $response['total_amount'] ?? '';
    $transaction_uuid = $response['transaction_uuid'] ?? '';
    $product_code = $response['product_code'] ?? '';
    $signed_fields_names = $response['signed_field_names'] ?? '';
    $provided_signature = $response['signature'] ?? '';

    // 5. Create the signature string and compare with eSewa's signature
    $secret_key = '8gBm/:&EnhH.1/q'; // Your eSewa secret key
    $message = "transaction_code={$transaction_code},status={$status},total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code},signed_field_names={$signed_fields_names}";
    $expected_signature = base64_encode(hash_hmac('sha256', $message, $secret_key, true));

    // 6. If signature matches and payment is complete, place the order
    if ($expected_signature === $provided_signature && $status === "COMPLETE") {
        $user_id = $_SESSION['user_id'];
        $name = mysqli_real_escape_string($conn, $_SESSION['name']);
        $location = mysqli_real_escape_string($conn, $_SESSION['location']);
        $payment_option = "Online Payment";
        $grand_total = 0;

        // 7. Get all cart items for this user
        $cart_items = [];
        $select_cart = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
        if (mysqli_num_rows($select_cart) == 0) {
            echo "Your cart is empty.";
            exit();
        }
        while ($row = mysqli_fetch_assoc($select_cart)) {
            $cart_items[] = $row;
        }

        // 8. Calculate the total price
        foreach ($cart_items as $row) {
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

        // 9. Save the order in the database
        $insert_order = mysqli_query($conn, "INSERT INTO orders (grand_total, payment_option, location, payment_status, order_date, name, user_id, order_status, transaction_id) 
        VALUES ($grand_total, '$payment_option', '$location', 'Paid', NOW(), '$name', $user_id, 'Pending', '$transaction_code')");

        if ($insert_order) {
            $order_id = mysqli_insert_id($conn);

            // 10. Save each cart item as an order item
            foreach ($cart_items as $row) {
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

            // 11. Clear the cart and session order details
            mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");
            unset($_SESSION['name'], $_SESSION['location'], $_SESSION['payment_option']);

            // 12. Show success message (redirect to thank you page in production)
            echo "<br>Order placed successfully! <a href='thankyou.php?order_id=$order_id'>Continue</a>";
        } else {
            echo "Failed to insert order.";
        }
    } else {
        // 13. If signature or payment status is wrong, show error
        echo "<br>Signature mismatch or payment not complete.";
    }
} else {
    // 14. If no payment data, show error
    echo "Invalid or missing payment data.";
}

?>