 <?php
 session_start();
 ?>
 
 <!-- Display cart contents -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .cart-container {
            margin: 50px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .img-thumbnail {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>My Shopping Cart</h2>
    <?php
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Product Image</th>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Shipping</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;

                foreach ($_SESSION['cart'] as $item) {

                    $originalPrice = $item['price'];
                    $discount = $item['discount'];
                    $shipping = $item['shipping'];
                    // Calculate discount amount and buying cost for each item
                    $discountAmount = ($discount / 100) * $originalPrice;
                    $buyingCost = $originalPrice - $discountAmount + $shipping;
                    // Calculate the total for this item
                    $item_total = $buyingCost * $item['quantity'];
                    // Add this item's total to the grand total
                    $grand_total += $item_total;

                    ?>
                    <tr>
                        <td >
                            <img src="productimage/<?php echo htmlspecialchars($item['image']); ?>" 
                            style="width: 100%; height: 100%; object-fit: cover; display: block; border: none;" 
                            alt="Product Image">
                        </td>

                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
    
                        <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <?php
                            if ($item['discount'] > 0) {
                                echo htmlspecialchars($item['discount']) . "%";
                            } else {
                                echo "No Discount";
                            }
                            ?>
                        </td>
                        <td>
                        <?php
                        if ($item['shipping'] > 0) {
                            echo "Rs" . htmlspecialchars($item['shipping']);
                        } else {
                            echo "No Shipping Cost";
                        }
                        ?>
                        </td>
                        <td>
                            <form method="POST" action="update.php">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required>
                                <input type="hidden" name="pid" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="update_quantity" class="btn">Update</button>
                            </form>
                        </td>
                        <td>Rs. <?php echo number_format($item_total,2) ?></td>
                        <td>
                            <a href="remove.php?remove=true&pid=<?php echo $item['id']; ?>" class="btn">Remove</a>
                        </td>
                    </tr>
                  <?php } ?>
                </tbody>
        </table>

        <div class="total" style="display: flex; gap:10%;">
            <p>Grand Total: Rs. <?php echo number_format($grand_total,2) ?></p>
       
            <!-- Order Now button -->
            <form method="POST" action="order.php">
                <button type="submit" name="order" style="padding:5px; margin-top:10px; cursor:pointer;background-color: skyblue;">Order Now</button>
            </form>

        </div>

        <a href="displayproduct.php" class="btn">Continue Shopping</a>
       
        <?php
            }else{
            ?>
        <p>Your cart is empty. <a href="displayproduct.php">Start Shopping</a></p>
        <?php
        }
        ?>
</div>

</body>
</html>
