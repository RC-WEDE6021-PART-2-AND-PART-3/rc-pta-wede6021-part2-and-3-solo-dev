<?php
include 'DBConn.php';

$result = $conn->query("SELECT * FROM tblProduct");

echo "<h2>Clothing Store</h2>";

while($row = $result->fetch_assoc()){
    echo "<div style='border:1px solid black; padding:10px; margin:10px; width:200px;'>";

    echo "<img src='uploads/".$row['image']."' width='150'><br>";
    echo "<b>".$row['name']."</b><br>";
    echo "R".$row['price']."<br>";
    echo $row['description']."<br>";

    echo "</div>";
}
?>

<a href="addProduct.php">Add New Product</a>