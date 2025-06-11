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

// Update quantity logic
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['update_quantity'])) {
    $pid = $_POST['pid'];
    $quantity = $_POST['quantity'];

    if ($quantity < 1) {
        $quantity = 1;
    }

    mysqli_query($conn, "UPDATE cart_items SET quantity = $quantity WHERE user_id = $user_id AND product_id = $pid");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Cart</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            padding: 0;
        }

        .cart-container {
            margin: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }

        th {
            color: blue;
        }

        .img-thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .btn {
            padding: 8px 14px;
            background-color: #45a049;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #3d8b40;
        }
    </style>
</head>

<body>

    <div class="cart-container">
        <h2>Shopping Cart</h2>

        <?php
        $query = "SELECT c.*, p.name, p.description, p.price, p.category, p.discount, p.image, p.shipping
              FROM cart_items c
              JOIN products p ON c.product_id = p.id
              WHERE c.user_id = $user_id";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $grand_total = 0;
            ?>

            <table>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Shipping</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>

                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    $price = $row['price'];
                    $discount = $row['discount'];
                    $shipping = $row['shipping'];
                    $quantity = $row['quantity'];

                    $discount_amount = ($discount / 100) * $price;
                    $final_price = $price - $discount_amount + $shipping;
                    $total = $final_price * $quantity;
                    $grand_total += $total;
                    ?>
                    <tr>
                        <td><img src="productimage/<?php echo $row['image']; ?>" class="img-thumb"></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td>Rs. <?php echo number_format($price, 2); ?></td>
                        <td><?php echo $discount > 0 ? $discount . "%" : "No Discount"; ?></td>
                        <td><?php echo $shipping > 0 ? "Rs. " . $shipping : "Free"; ?></td>
                        <td>
                            <form method="POST" action="displaycart.php" onsubmit="return validateQuantity(this);">
                                <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1"
                                    oninput="showUpdateButton(this)">
                                <input type="hidden" name="pid" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" name="update_quantity" class="btn update-btn"
                                    style="display:none;">Update</button>
                            </form>
                        </td>

                        <td>
                            <a href="remove.php?pid=<?php echo $row['product_id']; ?>" class="btn"
                                style="background-color: red;"
                                onclick="return confirm('Are you sure you want to remove this item from your cart?');">
                                Remove
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr style="color: green;">
                    <td colspan="8"><b>Grand Total</b></td>
                    <td colspan="2"><b>Rs. <?php echo number_format($grand_total, 2); ?></b></td>
                </tr>
            </table>

            <br>
            <form method="POST" action="order_form.php">
                <button type="submit" name="order" class="btn" style="background-color: teal;">Order Now</button>
            </form>
            <br><br>
            <a href="index.php" class="btn">Continue Shopping</a>

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