<?php
session_start();
include("dbconnect.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to add items to your cart.'); window.location.href='index.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['cart']) && isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    // Get full product data
    $product_res = mysqli_query($conn, "SELECT * FROM products WHERE id = $pid");

    if (mysqli_num_rows($product_res) > 0) {
        $product = mysqli_fetch_assoc($product_res);

        // Check if already in cart
        $cart_check = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id AND product_id = $pid");

        if (mysqli_num_rows($cart_check) > 0) {
            // Already exists → update quantity
            mysqli_query($conn, "UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $pid");
        } else {
            // Insert full product into cart_items (no escaping)
            $name = $product['name'];
            $category = $product['category'];
            $price = $product['price'];
            $discount = $product['discount'];
            $shipping = $product['shipping'];
            $quantity = 1;
            $image = $product['image'];
            $description = $product['description'];

            $insert_sql = "INSERT INTO cart_items 
                (user_id, product_id, pname, category, price, discount, shipping, quantity, image, description)
                VALUES 
                ($user_id, $pid, '$name', '$category', $price, $discount, $shipping, $quantity, '$image', '$description')";

            mysqli_query($conn, $insert_sql);
        }

        header("Location: displaycart.php");
        exit();
    } else {
        echo "Product not found.";
    }
}
?>
