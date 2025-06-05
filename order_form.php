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
    }

    $amount = round($subtotal_total);
    $tax = 0;
    $service_charge = 0;
    $product_delivery_charge = round($shipping_total);
    $total_amount = $amount + $tax + $service_charge + $product_delivery_charge;

    $transaction_id = date("Ymd-His") . "-" . $user_id . "-" . uniqid();
    $product_code = 'EPAYTEST';
    $secret_key = '8gBm/:&EnhH.1/q';

  $signed_field_names = "amount,tax_amount,total_amount,transaction_uuid,product_code,product_service_charge,product_delivery_charge";
$signature_data = "amount=$amount,tax_amount=$tax,total_amount=$total_amount,transaction_uuid=$transaction_id,product_code=$product_code,product_service_charge=$service_charge,product_delivery_charge=$product_delivery_charge";
$signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));

?>


<!DOCTYPE html>
<html>
<head>
    <title>Place Order</title>

    <style>
        #esewaForm input[type="submit"] {
        display: none;
        }
    </style>


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

        <input type="hidden" name="txn_uuid" value="<?= $transaction_id ?>">
        <input type="hidden" name="grand_total" value="<?= $total_amount ?>">

        <button type="submit" name="submit_order">Place Order</button>
    </form>

    <!-- eSewa Payment Form -->
    <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
        <input type="hidden" id="amount" name="amount" value="<?= $amount ?>" required>
        <input type="hidden" id="tax_amount" name="tax_amount" value ="0" required>
        <input type="hidden" id="total_amount" name="total_amount" value="<?= $total_amount ?>" required>
        <input type="hidden" id="transaction_uuid" name="transaction_uuid" value="<?= $transaction_id ?>" required>
        <input type="hidden" id="product_code" name="product_code" value ="EPAYTEST" required>
        <input type="hidden" id="product_service_charge" name="product_service_charge" value="0" required>
        <input type="hidden" id="product_delivery_charge" name="product_delivery_charge" value="0" required>
        <input type="hidden" name="success_url" value="https://e468-103-148-23-229.ngrok-free.app/shopping_cart_session/success.php" required>
        <input type="hidden" name="failure_url" value="https://e468-103-148-23-229.ngrok-free.app/shopping_cart_session/failure.php" required>

        <input type="hidden" name="signed_field_names" value="amount,tax_amount,total_amount,transaction_uuid,product_code,product_service_charge,product_delivery_charge" required>


        <input type="hidden" name="signature" value="<?= $signature ?>" required>
        <input value="Submit" type="submit" >
        
    </form>

<script>
   document.getElementById("orderForm").addEventListener("submit", function (e) {
    var paymentOption = document.getElementById("payment_option").value;
    if (paymentOption === "Online Payment") {
        e.preventDefault();
        document.getElementById("esewaForm").submit(); // Submit in same window
    }
});

</script>


</body>
</html>





