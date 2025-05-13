<?php
session_start();
include("dbconnect.php");

if (!isset($_POST['submit_order'])) {
    header("Location: displaycart.php");
    exit();
}

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: displaycart.php");
    exit();
}

// Get form inputs
$location = mysqli_real_escape_string($conn, $_POST['location']);
$payment_status = mysqli_real_escape_string($conn, $_POST['payment_option']);

// Calculate grand total
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $discount = ($item['discount'] / 100) * $item['price'];
    $price = $item['price'] - $discount + $item['shipping'];
    $total = $price * $item['quantity'];
    $grand_total += $total;
}

// Insert into orders
$sql_order = "INSERT INTO orders (grand_total, location, payment_status) 
              VALUES ('$grand_total', '$location', '$payment_status')";
mysqli_query($conn, $sql_order);
$order_id = mysqli_insert_id($conn);

// Insert each product
foreach ($_SESSION['cart'] as $item) {
    $discount = ($item['discount'] / 100) * $item['price'];
    $price = $item['price'] - $discount + $item['shipping'];
    $total = $price * $item['quantity'];

    $product_id = $item['id'];
    $image = $item['image'];
    $qty = $item['quantity'];

    $sql_item = "INSERT INTO order_items 
        (order_id, product_id, product_image, price, quantity, subtotal)
        VALUES ('$order_id', '$product_id', '$image', '$price', '$qty', '$total')";
    mysqli_query($conn, $sql_item);
}

// Clear cart
unset($_SESSION['cart']);
header("Location: thankyou.php?order_id=$order_id");
exit();
?>
