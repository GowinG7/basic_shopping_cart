<?php
session_start();
include 'db_connect.php';

$received_fields = $_POST;
$secret_key = '8gBm/:&EnhH.1/q'; // eSewa secret key
$signed_fields = explode(",", $_POST['signed_field_names']);
$data_string = "";

foreach ($signed_fields as $field) {
    $data_string .= "$field=" . $received_fields[$field] . ",";
}
$data_string = rtrim($data_string, ",");

$expected_signature = hash_hmac('sha256', $data_string, $secret_key, false);

if ($expected_signature === $_POST['signature']) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['name']) || !isset($_SESSION['location'])) {
        header("Location: displaycart.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $location = $_SESSION['location'];
    $payment_option = "Online Payment";
    $payment_status = "Paid";
    $transaction_id = $_POST['transaction_uuid'];

    $cart = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
    if (mysqli_num_rows($cart) == 0) {
        header("Location: displaycart.php");
        exit();
    }

    $grand_total = 0;
    $items = [];

    while ($row = mysqli_fetch_assoc($cart)) {
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

    // Insert order
    $order_sql = "INSERT INTO orders 
    (grand_total, payment_option, location, payment_status, order_date, name, user_id, transaction_id, order_status)
    VALUES 
    ($grand_total, '$payment_option', '$location', '$payment_status', NOW(), '$name', $user_id, '$transaction_id', 'Pending')";

    mysqli_query($conn, $order_sql);
    $order_id = mysqli_insert_id($conn);

    // Insert order items
    foreach ($items as $item) {
        $sql = "INSERT INTO order_items (order_id, product_id, product_image, price, quantity, subtotal)
                VALUES ($order_id, {$item['pid']}, '{$item['image']}', {$item['price']}, {$item['qty']}, {$item['subtotal']})";
        mysqli_query($conn, $sql);
    }

    // Clear cart
    mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

    header("Location: thankyou.php?transaction_id=$transaction_id");
    exit();
} else {
    header("Location: failure.php?reason=Invalid Signature");
    exit();
}
?>
