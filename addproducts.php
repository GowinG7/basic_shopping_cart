<?php
include("dbconnect.php");

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])){

    $pid = $_POST['pid'];
    $name = $_POST['pname'];
    $price = $_POST['price'];
    $quan = $_POST['quantity'];
    $pcategory = $_POST['pcategory'];
    $discount = $_POST['discount'];
    $description = $_POST['desc'];
    $shipping = $_POST['shipping'];

    //image upload handling
    $imagename = $_FILES['image']['name'];
    $imagetmp = $_FILES['image']['tmp_name'];

    //rename image to prevent duplication
    $imageNewname = time() . '-' . basename($imagename);
    $uploadpath = "productimage/" . $imageNewname;

    //Move the uploaded file to the folder
    if (move_uploaded_file($imagetmp, $uploadpath)) {

        $sql = "insert into products(id, name, category,  price, discount,shipping, quantity, image, description) values('$pid','$name','$pcategory','$price','$discount','$shipping','$quan','$imageNewname','$description') ";

        $result = $conn->query($sql);

        if ($result) {
            echo "Product added successfully";
        } else {
            echo "Error inserting data" . $conn_error;
        }
    }else{
        echo "Failed to upload image";
    }
    mysqli_close($conn);


}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Products</title>
    <style>
        body{
            background-color: whitesmoke;
            height: 100vh;      
            display: flex;     
            justify-content: center;
            align-items: center;
        }
        form{
            border: 2px solid black;
            border-radius: 10px;
            background-color: white;
            margin: 20px;
            padding: 10px;
        }
        button{
            cursor: pointer;
            background-color: rgb(214, 210, 210);
        }
        button:hover{
            background-color: skyblue;
        }
    </style>
</head>
<body>
    <form method="POST" action="" enctype="multipart/form-data">
        <h3><u>Add Product</u></h3>
        <label for="pid">ID:</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="number" id="pid" name="pid" required><br><br>
        <label for="pname">Name:</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="text" id="pname" name="pname" required><br><br>
        <label for="pcategory">Category:</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="text" id="pcategory" name="pcategory" required><br><br>
        <label for="price">Price:</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="text" id="price" name="price" required><br><br>
        <label for="discount">Discount:</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="number" id="discount" name="discount"><br><br>
        <label for="shipping">Shipping Cost:</label>&nbsp;&nbsp;
        <input type="number" id="shipping" name="shipping" ><br><br>
        <label for="quantity">Quantity:</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="number" id="quantity" name="quantity" required><br><br>
        <label for="image">Image:</label>&nbsp;&nbsp;
        <input type="file" id="image" name="image" accept="image/*" required><br><br>
        <label for="desc">Description of Products:</label><br>
        <textarea id="desc" name="desc" rows="5" cols="34" ></textarea><br><br>
        <button type="submit" name="submit" >Add product</button>
        <button type="reset">Reset</button>
    </form>
</body>
</html>