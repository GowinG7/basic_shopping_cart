<?php
session_start();
include("dbconnect.php");
include("header.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
     echo "<script>alert('Please log in to see added items to your cart.'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $pid = (int) $_POST['pid'];
    $newQty = (int) $_POST['quantity'];

    if ($newQty < 1) {
        $newQty = 1;
    }

    $query = "UPDATE cart_items SET quantity = $newQty WHERE user_id = $user_id AND product_id = $pid";
    mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <style>
        body { 
            font-family: Arial; 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
         }
        .cart-container {
            margin: 50px 20px;
         }
        .table-responsive {
             overflow-x: auto;
            }
        table { 
            width: 100%; 
            border-collapse: collapse;
             min-width: 800px;
             }
        th, td { 
            border: 1px solid black;
             padding: 10px;
              text-align: left; 
              white-space: nowrap;
             }
        .btn { 
            padding: 10px;
             background: #45a049; 
             color: white;
              border: none;
               cursor: pointer;
                text-decoration: none;
             }
        .btn:hover { 
            background: #3d8b40;
         }
        .img-thumbnail { 
            width: 100px; 
            height: 100px; 
            object-fit: contain;
         }
        @media (max-width: 768px) { 
            .cart-container { 
                margin: 20px 10px;
             }
         }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>My Shopping Cart</h2>

    <?php
    $query = "SELECT c.*, p.name, p.price, p.description, p.category, p.discount, p.image, p.shipping
              FROM cart_items c
              JOIN products p ON c.product_id = p.id
              WHERE c.user_id = $user_id";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $grand_total = 0;
        ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Image</th><th>Name</th><th>Description</th><th>Category</th>
                        <th>Price</th><th>Discount</th><th>Shipping</th>
                        <th>Quantity</th><th>Total</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                while ($item = mysqli_fetch_assoc($result)) {
                    $price = $item['price'];
                    $discount = $item['discount'];
                    $shipping = $item['shipping'];
                    $quantity = $item['quantity'];

                    $discountAmount = ($discount / 100) * $price;
                    $buyingCost = $price - $discountAmount + $shipping;
                    $item_total = $buyingCost * $quantity;
                    $grand_total += $item_total;
                    ?>
                    <tr>
                        <td><img src="productimage/<?php echo $item['image']; ?>" class="img-thumbnail" alt=""></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['description']; ?></td>
                        <td><?php echo $item['category']; ?></td>
                        <td>Rs. <?php echo number_format($price, 2); ?></td>
                        <td><?php echo $discount > 0 ? $discount . "%" : "No Discount"; ?></td>
                        <td><?php echo $shipping > 0 ? "Rs" . $shipping : "Free"; ?></td>
                   
                        <td>
                            <form method="POST" action="displaycart.php" onsubmit="return validateQuantity(this);">
                                <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1" oninput="showUpdateButton(this)">
                                <input type="hidden" name="pid" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" name="update_quantity" class="btn update-btn" style="display:none;">Update</button>
                            </form>


                        </td>

                        <td>Rs. <?php echo number_format($item_total, 2); ?></td>
                        <td>
                            <a href="remove.php?pid=<?php echo $item['product_id']; ?>" class="btn" style="background:#d63114;" onclick="return confirm('Are you sure you want to remove this item?');">Remove</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            <p><strong>Grand Total: Rs. <?php echo number_format($grand_total, 2); ?></strong></p>
            <form method="POST" action="order_form.php" style="display:inline;">
                <button type="submit" name="order" class="btn" style="background-color:rgb(16,137,158);">Order Now</button>
            </form>
            <a href="index.php" class="btn">Continue Shopping</a>
        </div>
    <?php
    } else {
        echo "<p>Your cart is empty. <a href='index.php'>Start Shopping</a></p>";
    }
    ?>
</div>


    <script>
        function showUpdateButton(inputElement) {
        const form = inputElement.closest('form');
        const button = form.querySelector('.update-btn');
        button.style.display = 'inline-block';
        }

        function validateQuantity(form) {
        const quantityInput = form.querySelector('input[name="quantity"]');
        if (parseInt(quantityInput.value) < 1) {
            quantityInput.value = 1;
        }
        return true;
        }
    </script>


</body>
</html>