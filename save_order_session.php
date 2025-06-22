<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['name'] = $_POST['name'];
    $_SESSION['location'] = $_POST['location'];
    $_SESSION['payment_option'] = $_POST['payment_option'];
}
?>