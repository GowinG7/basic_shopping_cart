<?php

// This is only for cash on delivery not online pyment

session_start();
include("dbconnect.php");

if (!isset($_POST['submit_order']) || !isset($_SESSION['user_id'])) {
    header("Location: displaycart.php");
    exit();
}

if ($_POST['payment_option'] === 'Online Payment') {
    header("Location: order_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$location = $_POST['location'];
$payment_status = $_POST['payment_option'];

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

$order_sql = "INSERT INTO orders 
(grand_total, payment_option, location, payment_status, order_date, name, user_id, order_status)
VALUES 
($grand_total, 'Cash on Delivery', '$location', 'Unpaid', NOW(), '$name', $user_id, 'Pending')";

mysqli_query($conn, $order_sql);
$order_id = mysqli_insert_id($conn);

foreach ($items as $item) {
    $sql = "INSERT INTO order_items (order_id, product_id, product_image, price, quantity, subtotal)
            VALUES ($order_id, {$item['pid']}, '{$item['image']}', {$item['price']}, {$item['qty']}, {$item['subtotal']})";
    mysqli_query($conn, $sql);
}

mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

header("Location: thankyou.php?order_id=$order_id");
exit();
?>