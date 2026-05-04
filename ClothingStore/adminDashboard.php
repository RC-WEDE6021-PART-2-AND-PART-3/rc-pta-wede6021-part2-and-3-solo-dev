<?php
session_start();
include 'DBConn.php';

if(!isset($_SESSION['admin'])){
    die("Access denied");
}

$result = $conn->query("SELECT * FROM tblUser");

while($row = $result->fetch_assoc()){
    echo $row['username'] . " | Verified: " . $row['isVerified'];
    echo " <a href='?verify=".$row['userID']."'>Verify</a><br>";
}

if(isset($_GET['verify'])){
    $id = $_GET['verify'];
    $conn->query("UPDATE tblUser SET isVerified=1 WHERE userID=$id");
    header("Location: adminDashboard.php");
}
?>