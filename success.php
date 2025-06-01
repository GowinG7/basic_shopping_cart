<?php
session_start();
include("dbconnect.php");

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Capture response from eSewa
$total_amount = $_RESPONSE['total_amount'] ?? '';
$transaction_uuid = $_RESPONSE['transaction_uuid'] ?? '';
$product_code = $_RESPONSE['product_code'] ?? '';
$signed_field_names = $_RESPONSE['signed_field_names'] ?? '';
$received_signature = $_RESPONSE['signature'] ?? '';

// Secret key
$secret_key = '8gBm/:&EnhH.1/q';

// Verify HMAC Signature
$signature_data = '';
$fields = explode(',', $signed_field_names);
foreach ($fields as $field) {
    $signature_data .= "$field=" . $_POST[$field] . ",";
}
$signature_data = rtrim($signature_data, ',');

$expected_signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));

// Check if the signature is valid
if ($received_signature !== $expected_signature) {
    echo "<h3>Invalid payment signature. Order rejected.</h3>";
    exit();
}

// Fetch cart items
$cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
if (!$cart_query || mysqli_num_rows($cart_query) == 0) {
    echo "Cart is empty or invalid.";
    exit();
}

// Get user delivery info (you can store it temporarily in session or request it again)
$name = $_SESSION['order_name'] ?? 'eSewa User';
$location = $_SESSION['order_location'] ?? 'eSewa Address';

// Insert into orders
while ($item = mysqli_fetch_assoc($cart_query)) {
    $product_id = $item['product_id'];
    $pname = $item['pname'];
    $image = $item['image'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $discount = $item['discount'];
    $shipping = $item['shipping'];

    $discount_amt = ($discount / 100) * $price;
    $final_price = $price - $discount_amt;
    $item_total = $final_price * $quantity;

    mysqli_query($conn, "INSERT INTO orders(user_id, name, location, payment_option, transaction_id, product_id, pname, image, quantity, price, discount, shipping, item_total, grand_total, order_status)
        VALUES('$user_id', '$name', '$location', 'Online Payment', '$transaction_uuid', '$product_id', '$pname', '$image', '$quantity', '$price', '$discount', '$shipping', '$item_total', '$total_amount', 'Pending')");
}

// Clear cart
mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

// Clear temporary session values
unset($_SESSION['order_name']);
unset($_SESSION['order_location']);

echo "<h3>✅ Payment successful. Order placed successfully!</h3>";
?>
