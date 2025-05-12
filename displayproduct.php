<?php
include("dbconnect.php");

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 90%;
            margin: 40px auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .card {
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: white;
            padding: 15px;
            text-align: center;
            box-shadow: 0px 0px 8px rgba(0,0,0,0.1);
        }
        .card img {
            max-width: 100%;
            height: 150px;
            object-fit: contain;
            border-radius: 5px;
        }
        .price {
            color: green;
            font-weight: bold;
        }
        
    </style>
</head>
<body>

<h2 style="text-align:center;">Product List</h2>

<div class="container">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="card">';
            echo '<img src="productimage/' . htmlspecialchars($row['image']) . '" 
                    onerror="this.onerror=null;this.src=\'productimage/default.jpg\';" 
                    alt="Product Image">';

            echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
            echo '<p class="category">Category: ' . htmlspecialchars($row['category']) . '</p>';
            echo '<p class="category">Description: ' . htmlspecialchars($row['description']) . '</p>';

            echo '<p class="category">Discount offer: ';
                    if($row['discount'] == 0 ){
                        echo 'Not available';
                    }
                    else{                    
                           echo htmlspecialchars($row['discount']);
                        }
                    echo '</p>';
            echo '<p class="price">Rs. ' . htmlspecialchars($row['price']) . '</p>';
            echo '<p>In Stock: ' . htmlspecialchars($row['quantity']) . ' pieces</p>';

            echo '<form method="POST" action="cart.php">';
            echo '<input type="hidden" name="pid" value="' . htmlspecialchars($row['id']) . '">';
            echo '<button type="submit" name="add_to_cart">Add to Cart</button>';
            echo '</form>';


            echo '</div>';
        }
    } else {
        echo "<p style='text-align:center;'>No products found.</p>";
    }

    $conn->close();
    ?>
</div>

</body>
</html>
