<?php include 'DBConn.php'; ?>

<link rel="stylesheet" href="style.css">

<h2 style="text-align:center;">Store</h2>

<div style="display:flex;flex-wrap:wrap;justify-content:center;">

<?php
$result = $conn->query("SELECT * FROM tblProduct");

while($row = $result->fetch_assoc()){
?>
<div style="background:#111;padding:10px;margin:10px;width:200px;border-radius:10px;">
<img src="uploads/<?php echo $row['image']; ?>" width="100%"><br>
<b><?php echo $row['name']; ?></b><br>
R<?php echo $row['price']; ?><br>
<p><?php echo $row['description']; ?></p>
</div>
<?php } ?>

</div>

<a href="home.php">Back</a>