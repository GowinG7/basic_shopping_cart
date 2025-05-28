<?php

// Only insert into DB after payment is successful through online payment

session_start();
include("dbconnect.php");

if (isset($_GET['data'])) {
    $decoded = base64_decode($_GET['data']);
    $res = json_decode($decoded, true);

    if ($res['status'] === "COMPLETE") {
        $txn = $res['transaction_uuid'];
        $product_code = $res['product_code'];
        $amount = $res['total_amount'];

        $user_id = $_SESSION['user_id'];
        $cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");

        if (mysqli_num_rows($cart_query) > 0) {
            $grand_total = 0;
            $items = [];

            while ($row = mysqli_fetch_assoc($cart_query)) {
                $price = $row['price'];
                $discount = $row['discount'];
                $shipping = $row['shipping'];
                $qty = $row['quantity'];

                $final = $price - ($discount / 100 * $price) + $shipping;
                $sub = $final * $qty;
                $grand_total += $sub;

                $items[] = $row;
            }

            $order_sql = "INSERT INTO orders (user_id, grand_total, location, name, payment_status, order_status)
                          VALUES ($user_id, $grand_total, 'Online', 'Online Customer', 'Online Payment', 'Pending')";
            mysqli_query($conn, $order_sql);
            $order_id = mysqli_insert_id($conn);

            foreach ($items as $item) {
                $sql = "INSERT INTO order_items (order_id, product_id, product_image, price, quantity, subtotal)
                        VALUES ($order_id, {$item['product_id']}, '{$item['image']}', {$item['price']}, {$item['quantity']}, {$item['price']})";
                mysqli_query($conn, $sql);
            }

            mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

            echo "<h2>✅ Payment Success</h2>";
            echo "<p>Transaction ID: $txn</p>";
            echo "<p>Order ID: $order_id</p>";
            echo "<a href='index.php'>Back to Home</a>";
        }
    } else {
        echo "<p>Invalid payment response.</p>";
    }
} else {
    echo "<p>Invalid eSewa response.</p>";
}
?>
