<?php
session_start();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Payment Failed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 40px;
        }

        .error-box {
            border: 1px solid #e74c3c;
            background-color: #fdecea;
            padding: 30px;
            border-radius: 10px;
            display: inline-block;
            color: #c0392b;
        }

        a {
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="error-box">
        <h2>❌ Payment Failed</h2>
        <p>We're sorry, but your payment could not be completed via eSewa.</p>
        <p>Please try again or choose a different payment method.</p>
        <br>
        <a href="order_form.php">← Return to Checkout</a>
    </div>

</body>

</html>