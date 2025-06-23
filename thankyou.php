<?php
session_start();
include("dbconnect.php");

// Get the order ID from the URL if it's valid
$order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? $_GET['order_id'] : null;

// Initialize variables
$order = null;
$order_items = [];

// If valid order_id is found
if ($order_id) {
    // Fetch order details
    $order_query = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = $order_id");
    if ($order_query && mysqli_num_rows($order_query) > 0) {
        $order = mysqli_fetch_assoc($order_query);

        // Fetch items related to this order
        $items_query = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $order_id");
        while ($row = mysqli_fetch_assoc($items_query)) {
            $order_items[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f0f0f0;
            padding: 30px;
        }

        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 900px;
            margin: auto;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #999;
            padding: 10px;
        }

        th {
            background-color: #e0e0ff;
        }

        img {
            width: 60px;
            height: 60px;
        }

        .btn {
            background-color: green;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }

        .btn:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Thank You for Your Order!</h2>

    <?php
    if ($order) {
        // Display basic order info
        echo "<p><strong>Order ID:</strong> " . htmlspecialchars($order_id) . "</p>";
        echo "<p><strong>Customer Name:</strong> " . htmlspecialchars($order['name']) . "</p>";
        echo "<p><strong>Delivery Location:</strong> " . htmlspecialchars($order['location']) . "</p>";
        echo "<p><strong>Payment Option:</strong> " . htmlspecialchars($order['payment_option']) . "</p>";

        // Show payment status in color
        $payment_status = $order['payment_status'];
        $status_color = ($payment_status === 'Completed') ? 'green' : 'orange';
        echo "<p><strong>Payment Status:</strong> <span style='color: $status_color;'>$payment_status</span></p>";

        // Greet user if logged in
        if (isset($_SESSION['username'])) {
            echo "<p>Hello <b>" . htmlspecialchars($_SESSION['username']) . "</b>, your order has been placed successfully.</p>";
        } else {
            echo "<p>Your order has been received. We will contact you soon for delivery.</p>";
        }

        // Show order items
        echo "<h3>Order Summary</h3>";
        echo "<table>";
        echo "<tr>
                <th>Product ID</th>
                <th>Image</th>
                <th>Price (after discount + shipping)</th>
                <th>Quantity</th>
                <th>Subtotal</th>
              </tr>";

        foreach ($order_items as $item) {
            echo "<tr>";
            echo "<td>" . $item['product_id'] . "</td>";
            echo "<td><img src='productimage/" . htmlspecialchars($item['product_image']) . "'></td>";
            echo "<td>Rs. " . number_format($item['price'], 2) . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>Rs. " . number_format($item['subtotal'], 2) . "</td>";
            echo "</tr>";
        }

        // Grand total row
        echo "<tr style='font-weight:bold; color:green;'>
                <td colspan='4'>Grand Total</td>
                <td>Rs. " . number_format($order['grand_total'], 2) . "</td>
              </tr>";
        echo "</table>";
    } else {
        echo "<p><strong>Sorry! No order found with this ID.</strong></p>";
    }
    ?>

    <a href="index.php" class="btn">Continue Shopping</a>
</div>

</body>
</html>
