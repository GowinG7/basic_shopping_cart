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
$shipping_total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $qty = $row['quantity'];
    $price = $row['price'];
    $discount = $row['discount'];
    $shipping = $row['shipping'];

    $discount_amt = ($discount / 100) * $price;
    $final_price = $price - $discount_amt;
    $subtotal = $final_price * $qty;

    $subtotal_total += $subtotal;
    $shipping_total += $shipping;
    $items[] = $row;
}

$amount = ceil($subtotal_total);
$delivery_charge = ceil($shipping_total);
$tax = 0;
$service_charge = 0;
$total_amount = $amount + $delivery_charge + $tax + $service_charge;

$txn_uuid = 'TXN' . time();
$product_code = 'EPAYTEST';
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
            <option value="Online Payment">Online Payment (eSewa)</option>
        </select><br><br>

        <!-- For COD submission -->
        <input type="hidden" name="txn_uuid" value="<?= $txn_uuid ?>">
        <input type="hidden" name="grand_total" value="<?= $total_amount ?>">

        <button type="submit" name="submit_order">Place Order</button>
    </form>

    <!-- eSewa Payment Form -->
    <form id="esewaForm" method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
        <input type="hidden" name="amount" value="<?= $amount ?>" required>
        <input type="hidden" name="tax_amount" value="<?= $tax ?>" required>
        <input type="hidden" name="product_service_charge" value="<?= $service_charge ?>" required>
        <input type="hidden" name="product_delivery_charge" value="<?= $delivery_charge ?>" required>
        <input type="hidden" name="total_amount" value="<?= $total_amount ?>" required>

        <input type="hidden" name="transaction_uuid" value="<?= $txn_uuid ?>" required>
        <input type="hidden" name="product_code" value="<?= $product_code ?>" required>

        <!-- NOTE: Replace with your ngrok/live URLs -->
        <input type="hidden" name="success_url" value="https://c367-103-75-49-37.ngrok-free.app/shopping_cart_session/success.php" required>
        <input type="hidden" name="failure_url" value="https://c367-103-75-49-37.ngrok-free.app/shopping_cart_session/failure.php" required>

        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
        <input type="hidden" name="signature" value="<?= $signature ?>" required>
        
    </form>

    <script>
        document.getElementById("orderForm").addEventListener("submit", function (e) {
            let paymentOption = document.getElementById("payment_option").value;
            if (paymentOption === "Online Payment") {
                e.preventDefault(); // prevent normal form submit
                document.getElementById("esewaForm").submit(); // redirect to eSewa
            }
        });
    </script>

</body>

</html>