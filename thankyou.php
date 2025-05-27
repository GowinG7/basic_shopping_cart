<?php
session_start();
$order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? $_GET['order_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f9f9f9;
        }
        .thankyou-box {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }
        .thankyou-box h2 {
            color: green;
        }
        .btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="thankyou-box">
    <h2>Thank you for your order!</h2>

    <?php 
        if ($order_id){
            echo "<p>Your Order ID is: <strong>" . htmlspecialchars($order_id) . "</strong></p>";
        }else{
            echo "<p><strong>Order ID is not available.</strong></p>";
        }
        if (isset($_SESSION['username'])){
            echo "<p>Dear <strong>" . htmlspecialchars($_SESSION['username']) . "</strong>, we will contact you shortly.</p>";
        }else{
            echo "<p>We will contact you soon for delivery.</p>";
        }
        ?>

    <a href="index.php" class="btn">Continue Shopping</a>
</div>

</body>
</html>
