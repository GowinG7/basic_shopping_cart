<?php
session_start();
include("dbconnect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $uname = $_POST['uname'];
    $pass = $_POST['pass'];

    $query = "SELECT * FROM users WHERE username = '$uname'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['name'] = $row['name'];
            header("Location: index.php");
            exit();
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            border: 1px solid black;
            border-radius: 7px;
            padding: 15px;
        }
        label, input {
           
            margin-bottom: 10px;
        }
        button {
            font-size: 14px;
            padding: 5px 10px;
            cursor: pointer;
            color: blue;
        }
        .footer {
            margin-top: 15px;
        }
        .footer a {
            color: green;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <h2 style="color: green;">User Login</h2>

    <label for="uname">Username:</label>
    <input type="text" id="uname" name="uname" required placeholder="Enter username">
        <br>
    <label for="pass">Password:</label>
    <input type="password" id="pass" name="pass" required placeholder="Enter password">
        <br>
    <button type="submit" name="login">Login</button>
        <hr>
    <div class="footer">
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</form>

</body>
</html>
