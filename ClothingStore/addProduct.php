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

    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $conn->query("INSERT INTO tblProduct (name,price,image,description)
                  VALUES ('$name','$price','$image','$desc')");
}
?>

<link rel="stylesheet" href="style.css">

<div class="container">
<h2>Add Product</h2>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="name" placeholder="Product Name" required>
<input type="number" name="price" placeholder="Price" required>
<textarea name="description" placeholder="Description"></textarea>
<input type="file" name="image" required>
<button name="add">Add</button>
</form>

<a href="viewProducts.php">View Store</a>
</div>