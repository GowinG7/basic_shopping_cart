<?php
session_start();
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: displaycart.php");
    exit();
}
?>

<h2>Fill in Delivery Details</h2>
<form action="place_order.php" method="post">
    <label>Location:</label><br>
    <input type="text" name="location" required><br><br>

    <label>Payment Option:</label><br>
    <select name="payment_option" required>
        <option value="">Select</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="Online Payment">Online Payment</option>
    </select><br><br>

    <h3>Your Cart Items:</h3>
    <table border="1">
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
        <tr><td colspan="3"><b>Grand Total</b></td><td><b><?= $grand_total ?></b></td></tr>
    </table>
    <br>
    <button type="submit" name="submit_order">Place Order</button>
</form>
