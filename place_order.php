<?php
session_start();
include("dbconnect.php");

// Check form submission and user login
if (!isset($_POST['submit_order']) || !isset($_SESSION['user_id'])) {
    header("Location: displaycart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = mysqli_real_escape_string($conn, $_POST['name']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$payment_status = mysqli_real_escape_string($conn, $_POST['payment_option']);

// Fetch user's cart items
$cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");

if (!$cart_query || mysqli_num_rows($cart_query) == 0) {
    header("Location: displaycart.php");
    exit();
}

$grand_total = 0;
$cart_items = [];

while ($item = mysqli_fetch_assoc($cart_query)) {
    $original_price = (float)$item['price'];
    $discount = (float)$item['discount'];
    $shipping = (float)$item['shipping'];
    $quantity = (int)$item['quantity'];

    $discount_amount = ($discount / 100) * $original_price;
    $final_price = $original_price - $discount_amount + $shipping;
    $subtotal = $final_price * $quantity;
    $grand_total += $subtotal;

    $cart_items[] = [
        'product_id' => $item['product_id'],
        'image' => $item['image'],
        'price' => $final_price,
        'qty' => $quantity,
        'subtotal' => $subtotal
    ];
}

// Insert into orders table
$order_sql = "INSERT INTO orders (user_id, grand_total, location, name, payment_status) 
              VALUES ($user_id, $grand_total, '$location', '$name', '$payment_status')";

if (!mysqli_query($conn, $order_sql)) {
    die("Order Insert Error: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

// Insert each item into order_items table
foreach ($cart_items as $item) {
    $item_sql = "INSERT INTO order_items 
        (order_id, product_id, product_image, price, quantity, subtotal)
        VALUES (
            $order_id,
            {$item['product_id']},
            '{$item['image']}',
            {$item['price']},
            {$item['qty']},
            {$item['subtotal']}
        )";
    if (!mysqli_query($conn, $item_sql)) {
        die("Item Insert Error: " . mysqli_error($conn));
    }
}

// Clear user's cart
mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

// Redirect to thank you page
header("Location: thankyou.php?order_id=$order_id");
exit();
?>
