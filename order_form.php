<?php
session_start();
include("header.php");

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: displaycart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place order</title>

    <style>
        table{
            border-collapse: collapse;
        }
        th{
            color: blue;
        }
        th,td,tr{
            border: 1px solid black;
        }
    </style>
</head>
<body>

<h2>Fill in Delivery Details</h2>
<form id="orderForm" action="place_order.php" method="post">
    <label>Location:</label><br>
    <input type="text" name="location" required><br><br>

    <label>Payment Option:</label><br>
    <select name="payment_option" id="payment_option" required>
        <option value="">Select</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="Online Payment">Online Payment</option>
    </select><br><br>

    <h3>Your Cart Items:</h3>
    <table>
        <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>
        <?php
        $grand_total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $discount = ($item['discount'] / 100) * $item['price'];
            $price = $item['price'] - $discount + $item['shipping'];
            $total = $price * $item['quantity'];
            $grand_total += $total;
            echo "<tr>
                <td>{$item['name']}</td>
                <td>{$item['quantity']}</td>
                <td>{$price}</td>
                <td>{$total}</td>
            </tr>";
        }
        ?>
        <tr style="color: Green;"><td colspan="3" ><b>Grand Total</b></td><td><b id="grand_total"><?= $grand_total ?></b></td></tr>
    </table>
    <br>
    <button type="submit" name="submit_order">Place Order</button>
</form>

<script>
document.getElementById('orderForm').addEventListener('submit', function(event) {
    var paymentOption = document.getElementById('payment_option').value;
    
    // If "Online Payment" is selected, redirect to E-Sewa
    if (paymentOption === 'Online Payment') {
        event.preventDefault();  // Prevent the form from submitting right away

        var orderId = '12345';  // Replace with dynamic order ID
        var totalAmount = document.getElementById('grand_total').innerText;  // Grand total is in text, not value
        
        // E-Sewa URL and parameters (replace with real E-Sewa URL)
        var esewaUrl = 'https://www.esewa.com.np/epay/main';
        var esewaParams = '?amt=' + totalAmount + '&pidx=' + orderId;  // Add required params

        // Construct full URL for redirection
        var redirectUrl = esewaUrl + esewaParams;

        // Redirect to the E-Sewa payment gateway
        window.location.href = redirectUrl;
    }
});
</script>

</body>
</html>
