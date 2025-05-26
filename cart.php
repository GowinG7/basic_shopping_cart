<?php
session_start();
include("dbconnect.php");

// Check login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to add items to your cart.'); window.location.href='index.php';</script>";
    exit();
}


$user_id = $_SESSION['user_id'];

if (isset($_GET['cart']) && isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    // Check if product exists
    $product_check = mysqli_query($conn, "SELECT id FROM products WHERE id = $pid");
    
    if (mysqli_num_rows($product_check) > 0) {
        // Check if product is already in cart
        $cart_check = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id AND product_id = $pid");

        if (mysqli_num_rows($cart_check) > 0) {
            // Already exists → increase quantity
            mysqli_query($conn, "UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $pid");
        } else {
            // Not in cart → insert new item
            mysqli_query($conn, "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $pid, 1)");
        }

        header("Location: displaycart.php");
        exit();
    } else {
        echo "Product not found.";
    }
}
?>
