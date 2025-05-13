<?php
session_start();
include("dbconnect.php");

// Check if cart is not empty
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {

    // Optional: Save order to DB (for now, just print confirmation)

    // Clear cart after placing order
    $_SESSION['cart'] = [];

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Order Confirmation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 50px;
            }
            .success-message {
                font-size: 24px;
                color: green;
                margin: 20px;
            }
            .btn {
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }
            .btn:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="success-message">
            🎉 Thank you! Your order has been placed successfully.
        </div>
        <a href="displayproduct.php" class="btn">Continue Shopping</a>
    </body>
    </html>
    <?php
} else {
    // If cart is empty, redirect to cart page
    header("Location: displaycart.php");
    exit();
}
?>
