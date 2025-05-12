<?php
$hostname = "localhost";
$username = "root";
$pass = "";
$db = "shoppingcart";

//create connection
$conn = mysqli_connect($hostname, $username, $pass, $db);

if (!$conn)
    die("Connection failed:" . mysqli_connect_error());
?>