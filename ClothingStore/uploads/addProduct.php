<?php
session_start();
include 'DBConn.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['add'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];

    // Image upload
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $conn->query("INSERT INTO tblProduct (name, price, image, description)
                  VALUES ('$name','$price','$image','$desc')");

    echo "Product added!";
}
?>

<h2>Add Product</h2>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="name" placeholder="Product Name" required><br><br>
<input type="number" name="price" placeholder="Price" required><br><br>
<textarea name="description" placeholder="Description"></textarea><br><br>
<input type="file" name="image" required><br><br>
<button name="add">Add Product</button>
</form>

<a href="viewProducts.php">View Products</a>