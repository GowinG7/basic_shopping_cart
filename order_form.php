<?php
session_start();
include("dbconnect.php");
include("header.php");


// Save order details to session only for Cash on Delivery (normal form submit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_order"]) && $_POST["payment_option"] === "Cash on Delivery") {
    $_SESSION['name'] = $_POST["name"];
    $_SESSION['location'] = $_POST["location"];
    $_SESSION['payment_option'] = $_POST["payment_option"];
}

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

// Calculate total amount
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

// eSewa payment parameters
$transaction_id = date("Ymd-His") . "-" . $user_id . "-" . uniqid();
$product_code = 'EPAYTEST';
$secret_key = '8gBm/:&EnhH.1/q';

// Only use these fields for signature as per eSewa docs
$signed_field_names = "total_amount,transaction_uuid,product_code";
$signature_data = "total_amount=$total_amount,transaction_uuid=$transaction_id,product_code=$product_code";
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
        <input type="hidden" name="transaction_uuid" value="<?= $transaction_id ?>">
        <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
        <button type="submit" name="submit_order" id="submitBtn">Place Order</button>
    </form>

    <!-- eSewa Payment Form -->
    <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
        <input type="hidden" name="amount" value="<?= $total_amount ?>">
        <input type="hidden" name="tax_amount" value="0">
        <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
        <input type="hidden" name="transaction_uuid" value="<?= $transaction_id ?>">
        <input type="hidden" name="product_code" value="<?= $product_code ?>">
        <input type="hidden" name="product_service_charge" value="0">
        <input type="hidden" name="product_delivery_charge" value="0">
        <input type="hidden" name="success_url" value="http://localhost/shopping_cart_session/success.php">
        <input type="hidden" name="failure_url" value="http://localhost/shopping_cart_session/failure.php">
        <input type="hidden" name="signed_field_names" value="<?= $signed_field_names ?>">
        <input type="hidden" name="signature" value="<?= $signature ?>">
        <input value="Submit" type="submit">
    </form>

    <script>
        document.getElementById("orderForm").addEventListener("submit", function (e) {
            var paymentOption = document.getElementById("payment_option").value;
            if (paymentOption === "Online Payment") {
                e.preventDefault();
                document.getElementById("submitBtn").disabled = true; // Prevent double submit

                // Save user details to session via AJAX (asynchronously)
                var name = document.querySelector('[name="name"]').value;
                var location = document.querySelector('[name="location"]').value;
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "save_order_session.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        document.getElementById("esewaForm").submit();
                    }
                };
                xhr.send("name=" + encodeURIComponent(name) + "&location=" + encodeURIComponent(location) + "&payment_option=Online Payment");
            }
        });
    </script>
</body>

</html>