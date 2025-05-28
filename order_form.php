<?php
session_start();
include("dbconnect.php");

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

$grand_total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $qty = $row['quantity'];
    $price = $row['price'];
    $discount = $row['discount'];
    $shipping = $row['shipping'];

    $discount_amt = ($discount / 100) * $price;
    $final_price = $price - $discount_amt + $shipping;
    $subtotal = $final_price * $qty;

    $grand_total += $subtotal;
    $items[] = $row;
}

$txn_uuid = 'TXN' . time();
$product_code = 'EPAYTEST';
$total_amount = number_format($grand_total, 2, '.', '');
$secret_key = '8gBm/:&EnhH.1/q';
$signature_data = "total_amount=$total_amount,transaction_uuid=$txn_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));


?>
<!DOCTYPE html>
<html>
<head>
    <title>Place Order</title>
</head>
<body>

<h2>Delivery Details</h2>

<form id="orderForm" action="place_order.php" method="post">
    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" required><br><br>

    <label>Payment Option:</label><br>
    <select name="payment_option" id="payment_option" required>
        <option value="">Select</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="Online Payment">Online Payment</option>
    </select><br><br>

    <input type="hidden" name="txn_uuid" value="<?= $txn_uuid ?>">
    <input type="hidden" name="grand_total" value="<?= $grand_total ?>">

    <button type="submit" name="submit_order">Place Order</button>
</form>

<form id="esewaForm" method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" style="display:none;">
    <input type="hidden" name="amount" value="<?= $total_amount ?>">
    <input type="hidden" name="tax_amount" value="0">
    <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
    <input type="hidden" name="transaction_uuid" value="<?= $txn_uuid ?>">
    <input type="hidden" name="product_code" value="<?= $product_code ?>">
    <input type="hidden" name="product_service_charge" value="0">
    <input type="hidden" name="product_delivery_charge" value="0">
    <input type="hidden" name="success_url" value="http://localhost/shopping_cart_session/success.php">
    <input type="hidden" name="failure_url" value="http://localhost/shopping_cart_session/failure.php">
    <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
    <input type="hidden" name="signature" value="<?= $signature ?>">
</form>

<script>
document.getElementById("orderForm").addEventListener("submit", function(e) {
    let paymentOption = document.getElementById("payment_option").value;

    if (paymentOption === "Online Payment") {
        e.preventDefault(); // ✅ Prevent normal form submission
        document.getElementById("esewaForm").submit(); // ✅ Submit eSewa form instead
    }
    // else: it will naturally submit to place_order.php for COD
});
</script>


</body>
</html>
