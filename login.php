<?php
session_start();
include 'dbconnect.php';

if (isset($_POST['login'])) {
    $username = $_POST['uname'];
    $password = $_POST['pass'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $row = mysqli_fetch_assoc($res);

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: index.php");
    } else {
        echo "Invalid login.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        body{
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form{
            border: 1px solid black;
            border-radius: 7px;
            padding: 10px;
        }
        .footer a{
            color: green;
        }
        .footer p{
            color: black;
        }
        button{
            font-size: 17px;
            cursor: pointer;
            color: blue;
        }
    </style>
</head>
<body>
    <form method="POST" action="">
    <h2 style="color: green;" >User login</h2>
    <label for="uname">Username:</label>
    <input type="text" id="uname" name="uname" required placeholder="Username"><br><br>
    <label for="pass">Password:</label>
    <input type="password" id="pass" name="pass" required placeholder="Password"> <br><br>
    <button type="submit" name="login">login</button>
    <hr>
    <div class="footer">
        <p>Don't have an account? <a href="register.php">Register</a> </p>
    </div>
</form> 
</body>
</html>
