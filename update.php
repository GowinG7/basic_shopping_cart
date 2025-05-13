<?php
session_start();

// UPDATE QUANTITY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $pid = intval($_POST['pid']);
    $newQty = max(1, intval($_POST['quantity'])); // Ensure at least 1

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $pid) {
            $item['quantity'] = $newQty;
            break;
        }
    }

    header("Location: displaycart.php");
    exit();
}
?>
