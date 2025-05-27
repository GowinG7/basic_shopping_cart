<?php
include ("dbconnect.php");

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['register'])) {
    $nam = $_POST['nam'];
    $phone = $_POST['phone'];
    $username = $_POST['uname'];
    $password = password_hash($_POST['pass'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name,phone,username, password) VALUES ('$nam','$phone','$username', '$password')";
    if (mysqli_query($conn, $sql)) {
        echo "Registration successful.";
    }else{
        echo "Error: " . mysqli_error($conn);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body{
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form{
            border: 1px solid black;
            border-radius: 10px;
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
        nam{
            margin-left: 20px;
        }
    </style>
</head>
<body>
<form method="POST" action="">
    <h2 style="color: green;" >User Registration</h2>
    
    <label for="nam">Name:</label> 
    <input type="text" name="nam" id="nam" placeholder="Full Name" required><br><br>
    <label for="phone">Phone:</label>
    <input type="text" name="phone" id="phone" placeholder="Your phone number" required><br><br>
    <label for="uname">Username:</label>
    <input type="text" id="uname" name="uname" required placeholder="Username"><br><br>
    <label for="pass">Password:</label>
    <input type="password" id="pass" name="pass" required placeholder="Password"> <br><br>
    <button type="submit" name="register">Register</button>
    <button type="reset">Reset</button>
    <hr>
    <div class="footer">
        <p>Already have an account? <a href="login.php">Log In</a> </p>
    </div>
</form>   
</body>
</html>


