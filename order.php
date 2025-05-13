<?php
session_start();
include("dbconnect.php");

// If cart is empty, redirect
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: displaycart.php");
    exit();
}

// Static values (You can replace with form values later)
$location = "Default Location";
$payment_status = "pending";

// Calculate grand total
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $discountAmount = ($item['discount'] / 100) * $item['price'];
    $buyingCost = $item['price'] - $discountAmount + $item['shipping'];
    $item_total = $buyingCost * $item['quantity'];
    $grand_total += $item_total;
}

// Insert into orders table
$sql_order = "INSERT INTO orders (grand_total, location, payment_status) 
              VALUES ('$grand_total', '$location', '$payment_status')";
mysqli_query($conn, $sql_order);

// Get the last inserted order ID
$order_id = mysqli_insert_id($conn);

// Insert each item into order_items table
foreach ($_SESSION['cart'] as $item) {
    $discountAmount = ($item['discount'] / 100) * $item['price'];
    $buyingCost = $item['price'] - $discountAmount + $item['shipping'];
    $item_total = $buyingCost * $item['quantity'];

    $product_id = $item['id'];
    $product_image = $item['image'];
    $price = $buyingCost;
    $quantity = $item['quantity'];
    $subtotal = $item_total;

    $sql_item = "INSERT INTO order_items 
        (order_id, product_id, product_image, price, quantity, subtotal) 
        VALUES ('$order_id', '$product_id', '$product_image', '$price', '$quantity', '$subtotal')";
    
    mysqli_query($conn, $sql_item);
}

// Clear the cart
unset($_SESSION['cart']);

// Redirect
header("Location: thankyou.php?order_id=$order_id");
exit();
?>
