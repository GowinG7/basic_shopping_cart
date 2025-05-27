<?php
session_start();
include("dbconnect.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items for the logged-in user
$result = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: displaycart.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Place Order</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            color: blue;
        }
        img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<h2>Delivery Details</h2>

<form id="orderForm" action="place_order.php" method="post">

    <label for="customer_name">Your Name:</label><br>
    <input type="text" id="customer_name" name="name" required><br><br>

    <label for="location">Delivery Location:</label><br>
    <input type="text" id="location" name="location" required><br><br>

    <label for="payment_option">Payment Option:</label><br>
    <select name="payment_option" id="payment_option" required>
        <option value="">Select</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="Online Payment">Online Payment</option>
    </select><br><br>

    <h3>Your Cart Items:</h3>
    <table>
        <tr>
            <th>Image</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Discount (%)</th>
            <th>Shipping</th>
            <th>Total</th>
        </tr>

        <?php
        $grand_total = 0;

        while ($item = mysqli_fetch_assoc($result)) {
            $product_name = isset($item['pname']) ? $item['pname'] : 'Unknown';
            $image = isset($item['image']) ? $item['image'] : '';
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];
            $discount = (float)$item['discount'];
            $shipping = (float)$item['shipping'];

            $discount_amount = ($discount / 100) * $price;
            $final_price = $price - $discount_amount + $shipping;
            $total = $final_price * $qty;
            $grand_total += $total;

            echo "<tr>
                <td><img src='productimage/{$image}' alt='Product'></td>
                <td>" . htmlspecialchars($product_name) . "</td>
                <td>$qty</td>
                <td>Rs. " . number_format($price, 2) . "</td>
                <td>" . number_format($discount, 1) . "%</td>
                <td>Rs. " . number_format($shipping, 2) . "</td>
                <td>Rs. " . number_format($total, 2) . "</td>
            </tr>";
        }
        ?>

        <tr style="color: green;">
            <td colspan="6"><b>Grand Total</b></td>
            <td><b id="grand_total"><?php echo number_format($grand_total, 2); ?></b></td>
        </tr>
    </table>

    <br>
    <button type="submit" name="submit_order">Place Order</button>
</form>

<!-- E-Sewa Payment Handling -->
<script>
document.getElementById("orderForm").addEventListener("submit", function(e) {
    var paymentOption = document.getElementById("payment_option").value;
    if (paymentOption === "Online Payment") {
        e.preventDefault();
        let amt = document.getElementById("grand_total").innerText.replace(/[^\d.]/g, '');
        let orderId = 'ORD' + Date.now();
        window.location.href = "https://www.esewa.com.np/epay/main?amt=" + amt + "&pidx=" + orderId;
    }
});
</script>

</body>
</html>
