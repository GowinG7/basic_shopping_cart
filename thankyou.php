<?php
session_start();
include("dbconnect.php");

$order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? $_GET['order_id'] : null;
$order = null;
$order_items = [];

if ($order_id) {
    $order_result = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = $order_id");
    if ($order_result && mysqli_num_rows($order_result) > 0) {
        $order = mysqli_fetch_assoc($order_result);

        $items_result = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $order_id");
        while ($row = mysqli_fetch_assoc($items_result)) {
            $order_items[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f4f4f4;
            padding: 30px;
        }
        .thankyou-box {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 850px;
            margin: auto;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
        }
        th {
            color: blue;
        }
        img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #388E3C;
        }
    </style>
</head>
<body>

<div class="thankyou-box">
    <h2>Thank you for your order!</h2>

    <?php
    if ($order) {
        echo "<p>Order ID: <strong>" . htmlspecialchars($order_id) . "</strong></p>";
        echo "<p>Location: <strong>" . htmlspecialchars($order['location']) . "</strong></p>";
        echo "<p>Ordered By: <strong>" . htmlspecialchars($order['name']) . "</strong></p>";

        if (isset($_SESSION['username'])) {
            echo "<p>Dear <strong>" . htmlspecialchars($_SESSION['username']) . "</strong>, your order has been placed successfully.</p>";
        } else {
            echo "<p>We will contact you soon for delivery.</p>";
        }

        echo "<h3>Ordered Details:</h3>";
        echo "<table>";
        echo "<tr>
                <th>Product ID</th>
                <th>Image</th>
                <th>Price after discount and shipping cost</th>
                <th>Quantity</th>
                <th>Subtotal</th>
              </tr>";

        foreach ($order_items as $item) {
            echo "<tr>";
            echo "<td>" . $item['product_id'] . "</td>";
            echo "<td><img src='productimage/" . htmlspecialchars($item['product_image']) . "' alt='Product'></td>";
            echo "<td>Rs. " . number_format($item['price'], 2) . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>Rs. " . number_format($item['subtotal'], 2) . "</td>";
            echo "</tr>";
        }

        echo "<tr style='color: green;'>
                <td colspan='4'><b>Grand Total</b></td>
                <td><b>Rs. " . number_format($order['grand_total'], 2) . "</b></td>
              </tr>";
        echo "</table>";

    } else {
        echo "<p><strong>Order details not found.</strong></p>";
    }
    ?>
    <br>
    <a href="index.php" class="btn">Continue Shopping</a>
</div>

</body>
</html>