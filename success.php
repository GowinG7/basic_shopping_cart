<?php
session_start();
include("dbconnect.php");

// Verify if eSewa sent a response
if (isset($_REQUEST['data'])) {
    $data_base64 = $_REQUEST['data'];
    $decoded_json = base64_decode($data_base64);
    $res = json_decode($decoded_json, true);

    // Extract response fields
    $status = $res['status'] ?? '';
    $txn = $res['transaction_uuid'] ?? '';
    $product_code = $res['product_code'] ?? '';
    $amount = $res['total_amount'] ?? '';
    $transaction_code = $res['transaction_code'] ?? '';
    $signed_fields = $res['signed_field_names'] ?? '';
    $provided_signature = $res['signature'] ?? '';

    // ✅ Rebuild message string for HMAC
    $field_names = explode(',', $signed_fields);
    $message_parts = [];
    foreach ($field_names as $field) {
        if (isset($res[$field])) {
            $message_parts[] = "$field={$res[$field]}";
        }
    }
    $message = implode(',', $message_parts);

    // ✅ Recalculate HMAC signature
    $secret_key = "8gBm/:&EnhH.1/q"; // eSewa UAT secret key
    $expected_signature = base64_encode(hash_hmac('sha256', $message, $secret_key, true));

    if ($provided_signature === $expected_signature && $status === "COMPLETE") {
        // Proceed with inserting the order
        if (!isset($_SESSION['user_id'])) {
            echo "<p>❌ Session expired. Please log in again.</p>";
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
        if (!$cart_query || mysqli_num_rows($cart_query) == 0) {
            echo "<p>❌ No cart items found.</p>";
            exit();
        }

        $grand_total = 0;
        $items = [];

        while ($row = mysqli_fetch_assoc($cart_query)) {
            $price = $row['price'];
            $discount = $row['discount'];
            $shipping = $row['shipping'];
            $qty = $row['quantity'];

            $final_price = $price - ($discount / 100 * $price) + $shipping;
            $subtotal = $final_price * $qty;
            $grand_total += $subtotal;

            $items[] = $row;
        }

        // Insert order
        $order_sql = "INSERT INTO orders 
            (grand_total, payment_option, location, payment_status, order_date, name, user_id, order_status, transaction_id)
            VALUES 
            ($grand_total, 'Online Payment', 'Online', 'Paid', NOW(), 'Online User', $user_id, 'Pending', '$txn')";
        mysqli_query($conn, $order_sql);
        $order_id = mysqli_insert_id($conn);

        // Insert items
        foreach ($items as $item) {
            $product_id = $item['product_id'];
            $image = $item['image'];
            $qty = $item['quantity'];
            $price = $item['price'];
            $discount = $item['discount'];
            $shipping = $item['shipping'];
            $final_price = $price - ($discount / 100 * $price) + $shipping;
            $subtotal = $final_price * $qty;

            $sql = "INSERT INTO order_items 
                    (order_id, product_id, product_image, price, quantity, subtotal)
                    VALUES 
                    ($order_id, $product_id, '$image', $price, $qty, $subtotal)";
            mysqli_query($conn, $sql);
        }

        mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

        echo "<h2>✅ Payment Successful</h2>";
        echo "<p><strong>Transaction ID:</strong> $txn</p>";
        echo "<p><strong>Order ID:</strong> $order_id</p>";
        echo "<a href='index.php'>Back to Home</a>";
    } else {
        echo "<h3>❌ Signature mismatch or payment not completed.</h3>";
        echo "<p>Status: $status</p>";
    }
} else {
    echo "<p>❌ No response received from eSewa.</p>";
}
?>
