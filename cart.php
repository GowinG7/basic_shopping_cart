<?php
session_start();
include("dbconnect.php");

// Add product to cart
if (isset($_GET['cart']) && isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    // Fetch product details from the database
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Debugging: check if product is found
    var_dump($product); // Check if this gives the expected product data

    if ($product) {
        $cartItem = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'description' => $product['description'],
            'category' => $product['category'],
            'discount' => $product['discount'],
            'quantity' => 1, // Default to 1
            'image' => $product['image'],
            'shipping' => $product['shipping']
        ];

        // Initialize cart session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        //loop to check if item already exits
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $pid) {
                $item['quantity']++; //increment quantity if found
                $found = true;
                break;
            }
        }
        //if not found , add new product to cart
        if (!$found) {
            $_SESSION['cart'][] = $cartItem;
        }

        // Redirect to cart page
        header("Location: displaycart.php");
        exit();
    } else {
        echo "Product not found.";
    }
}
?>
