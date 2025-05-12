<?php
session_start();
include("dbconnect.php");

// Add product to cart
if (isset($_GET['cart']) && isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);

    // Fetch product details from the database
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $cartItem = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'description' => $product['description'],
            'category' => $product['category'],
            'discount' => $product['discount'],
            'quantity' => 1, // Default to 1
            'image' => $product['image']
        ];

        // Initialize cart session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product is already in the cart, and update quantity if so
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $pid) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = $cartItem;
        }

        // Redirect to cart page
        header("Location: cart.php");
        exit();
    } else {
        echo "Product not found.";
    }
}

// Remove item from cart
if (isset($_GET['remove']) && isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);

    // Loop through cart items and remove the item that matches the product ID
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $pid) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex the array after removal
            break;
        }
    }

    // Redirect back to cart page after removal
    header("Location: cart.php");
    exit();
}

// Display cart contents
?>
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
    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product Image</th>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                foreach ($_SESSION['cart'] as $item):
                    $item_total = $item['price'] * $item['quantity'];
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
                            <form method="POST" action="cart.php">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required>
                                <input type="hidden" name="pid" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="update_quantity" class="btn">Update</button>
                            </form>
                        </td>
                        <td>Rs. <?php echo number_format($item_total, 2); ?></td>
                        <td>
                            <a href="cart.php?remove=true&pid=<?php echo $item['id']; ?>" class="btn">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            <p>Grand Total: Rs. <?php echo number_format($grand_total, 2); ?></p>
        </div>

        <a href="displayproduct.php" class="btn">Continue Shopping</a>
    <?php else: ?>
        <p>Your cart is empty. <a href="displayproduct.php">Start Shopping</a></p>
    <?php endif; ?>

</div>

</body>
</html>
