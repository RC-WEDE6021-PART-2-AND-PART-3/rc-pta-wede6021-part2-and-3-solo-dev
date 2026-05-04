<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
?>

<link rel="stylesheet" href="style.css">

<div class="container">
<h1>Welcome <?php echo $_SESSION['user']; ?></h1>

<a href="addProduct.php">Add Product</a><br><br>
<a href="viewProducts.php">View Store</a><br><br>
<a href="logout.php">Logout</a>
</div>