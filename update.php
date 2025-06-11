<?php
session_start();
include("dbconnect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// When update quantity form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $pid = (int) $_POST['pid'];
    $newQty = (int) $_POST['quantity'];

    // Ensure minimum quantity is 1
    if ($newQty < 1) {
        $newQty = 1;
    }

    $user_id = $_SESSION['user_id'];

    // Update the quantity in the database
    $query = "UPDATE cart_items SET quantity = $newQty WHERE user_id = $user_id AND product_id = $pid";
    $result = mysqli_query($conn, $query);

    if ($result) {
        header("Location: displaycart.php");
        exit();
    } else {
        echo "Failed to update quantity.";
    }
}
?>