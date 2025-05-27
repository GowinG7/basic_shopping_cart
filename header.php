<style>
.navbar {
  background-color: #333;
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: Arial, sans-serif;
  color: white;
}

.navbar a {
  color: white;
  margin-left: 15px;
  text-decoration: none;
}

.navbar a:hover {
  text-decoration: underline;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-info span {
  margin-right: 10px;
  font-weight: bold;
  color: #f0c040; /* subtle highlight */
}
</style>

<div class="navbar">
  <div>
    <a href="index.php">Home</a>
    <a href="displaycart.php">My Shopping Cart</a>
  </div>

  <div class="user-info">
    <?php if (isset($_SESSION['user_id'])){ 
      echo '<span>Welcome, ' . htmlspecialchars($_SESSION['name']) . '</span>';
      echo '<a href="logout.php">Logout</a>';
    } else{
      echo '<a href="login.php">Login</a>';
      echo '<a href="register.php">Register</a>';
    }
    ?>
  </div>
</div>
