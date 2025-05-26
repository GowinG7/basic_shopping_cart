<?php
session_start();
include("dbconnect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['pid'])) {
    $user_id = $_SESSION['user_id'];
    $pid = $_GET['pid'];

    // Delete the product from the cart_items table
    $delete = mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id AND product_id = $pid");

    if ($delete) {
        header("Location: displaycart.php");
        exit();
    } else {
        echo "Failed to remove item from cart.";
    }
} else {
    header("Location: displaycart.php");
    exit();
}
?>
