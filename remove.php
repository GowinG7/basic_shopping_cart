<?php
session_start();

// Redirect early if pid is not set
if (!isset($_GET['pid'])) {
    header("Location: displaycart.php");
    exit();
}

$pid = $_GET['pid'];

// Loop through cart items and remove the item that matches the product ID
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $pid) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex the array
        break;
    }
}

// Redirect back to displaycart.php after removal
header("Location: displaycart.php");
exit();
?>
