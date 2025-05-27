<?php
session_start();
include("dbconnect.php");

// Check if user is logged in and form is submitted
if (!isset($_POST['submit_order']) || !isset($_SESSION['user_id'])) {
    header("Location: displaycart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$location = $_POST['location'];
$payment_status = $_POST['payment_option'];

// Get user's cart items
$cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");

if (mysqli_num_rows($cart_query) == 0) {
    header("Location: displaycart.php");
    exit();
}

// Calculate total and store items
$grand_total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($cart_query)) {
    $price = $row['price'];
    $discount = $row['discount'];
    $shipping = $row['shipping'];
    $quantity = $row['quantity'];

    $discount_amount = ($discount / 100) * $price;
    $final_price = $price - $discount_amount + $shipping;
    $total = $final_price * $quantity;
    $grand_total += $total;

    $items[] = [
        'pid' => $row['product_id'],
        'image' => $row['image'],
        'price' => $final_price,
        'qty' => $quantity,
        'subtotal' => $total
    ];
}

// Insert into orders table
$insert_order = "INSERT INTO orders (user_id, grand_total, location, name, payment_status, order_status)
                 VALUES ($user_id, $grand_total, '$location', '$name', '$payment_status', 'Pending')";
mysqli_query($conn, $insert_order);

$order_id = mysqli_insert_id($conn);

// Insert each item to order_items table
foreach ($items as $item) {
    $insert_item = "INSERT INTO order_items (order_id, product_id, product_image, price, quantity, subtotal)
                    VALUES (
                        $order_id,
                        {$item['pid']},
                        '{$item['image']}',
                        {$item['price']},
                        {$item['qty']},
                        {$item['subtotal']}
                    )";
    mysqli_query($conn, $insert_item);
}

// Clear user's cart
mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

// Redirect to thankyou.php with sending oder ID in the URL
header("Location: thankyou.php?order_id=$order_id");
exit();
?>
