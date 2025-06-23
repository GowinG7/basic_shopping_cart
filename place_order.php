<?php
session_start();
include("dbconnect.php");
include("header.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['name'] = $_POST['name'];
    $_SESSION['location'] = $_POST['location'];
    $_SESSION['payment_option'] = $_POST['payment_option'];
    $total_amount = floatval($_POST['total_amount']);
    $_SESSION['total_amount'] = $total_amount;

    $user_id = $_SESSION['user_id'];

    if ($_POST['payment_option'] === 'Online Payment') {
        $transaction_id = date("Ymd-His") . "-$user_id-" . uniqid();
        $_SESSION['transaction_id'] = $transaction_id;

        $product_code = 'EPAYTEST';
        $secret_key = '8gBm/:&EnhH.1/q';

        $signed_field_names = "total_amount,transaction_uuid,product_code";
        $signature_data = "total_amount=$total_amount,transaction_uuid=$transaction_id,product_code=$product_code";
        $signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));
        ?>
        <!DOCTYPE html>
        <html>
        <head><title>Confirm Payment</title></head>
        <body>
            <h2>Confirm Your Payment</h2>
            <p>Name: <?= htmlspecialchars($_SESSION['name']) ?></p>
            <p>Location: <?= htmlspecialchars($_SESSION['location']) ?></p>
            <p>Total: Rs. <?= number_format($total_amount, 2) ?></p>

            <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
                <input type="hidden" name="amount" value="<?= $total_amount ?>" />
                <input type="hidden" name="tax_amount" value="0" />
                <input type="hidden" name="total_amount" value="<?= $total_amount ?>" />
                <input type="hidden" name="transaction_uuid" value="<?= $transaction_id ?>" />
                <input type="hidden" name="product_code" value="<?= $product_code ?>" />
                <input type="hidden" name="product_service_charge" value="0" />
                <input type="hidden" name="product_delivery_charge" value="0" />
                <input type="hidden" name="success_url" value="http://localhost/shoppingcart/success.php" />
                <input type="hidden" name="failure_url" value="http://localhost/shoppingcart/failure.php" />
                <input type="hidden" name="signed_field_names" value="<?= $signed_field_names ?>" />
                <input type="hidden" name="signature" value="<?= $signature ?>" />
                <button type="submit">Proceed to eSewa Payment</button>
            </form>
        </body>
        </html>
        <?php
        exit();
    } else {
        if (!isset($_SESSION['user_id'])) {
            header("Location: displaycart.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);

        $cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
        if (mysqli_num_rows($cart_query) == 0) {
            header("Location: displaycart.php");
            exit();
        }

        $grand_total = 0;
        $items = [];

        while ($row = mysqli_fetch_assoc($cart_query)) {
            $price = $row['price'];
            $discount = $row['discount'];
            $shipping = $row['shipping'];
            $quantity = $row['quantity'];

            $discount_amt = ($discount / 100) * $price;
            $final_price = $price - $discount_amt + $shipping;
            $subtotal = $final_price * $quantity;
            $grand_total += $subtotal;

            $items[] = [
                'pid' => $row['product_id'],
                'image' => $row['image'],
                'price' => $final_price,
                'qty' => $quantity,
                'subtotal' => $subtotal
            ];
        }

        // ✅ Insert order with payment_status = 'Pending' for Cash on Delivery
        $order_sql = "INSERT INTO orders 
            (grand_total, payment_option, location, payment_status, order_date, name, user_id, order_status)
            VALUES 
            ($grand_total, 'Cash on Delivery', '$location', 'Pending', NOW(), '$name', $user_id, 'Pending')";
        mysqli_query($conn, $order_sql);
        $order_id = mysqli_insert_id($conn);

        foreach ($items as $item) {
            mysqli_query($conn, "INSERT INTO order_items 
                (order_id, product_id, product_image, price, quantity, subtotal)
                VALUES 
                ($order_id, {$item['pid']}, '{$item['image']}', {$item['price']}, {$item['qty']}, {$item['subtotal']})");
        }

        mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");
        header("Location: thankyou.php?order_id=$order_id");
        exit();
    }
}
?>
