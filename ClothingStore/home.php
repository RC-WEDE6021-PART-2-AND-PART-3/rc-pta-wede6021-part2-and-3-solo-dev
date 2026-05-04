<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
?>

<h1>Welcome <?php echo $_SESSION['user']; ?></h1>

<a href="addProduct.php">Add Product</a><br>
<a href="viewProducts.php">View Store</a><br>
<a href="logout.php">Logout</a>