<?php
session_start();
include("dbconnect.php");
include("header.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: displaycart.php");
    exit();
}

$subtotal_total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $qty = $row['quantity'];
    $price = $row['price'];
    $discount = $row['discount'];
    $shipping = $row['shipping'];
    $discount_amt = ($discount / 100) * $price;
    $final_price = $price - $discount_amt;
    $subtotal = ($final_price * $qty) + $shipping;
    $subtotal_total += $subtotal;
}
$total_amount = round($subtotal_total);
?>

<!DOCTYPE html>
<html>
<head><title>Place Order</title></head>
<body>
<h2>Delivery Details</h2>
<form action="place_order.php" method="POST">
    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>
    <label>Location:</label><br>
    <input type="text" name="location" required><br><br>
    <label>Payment Option:</label><br>
    <select name="payment_option" required>
        <option value="">Select</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="Online Payment">Online Payment (eSewa)</option>
    </select><br><br>
    <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
    <button type="submit" name="submit_order">Place Order</button>
</form>
</body>
</html>
