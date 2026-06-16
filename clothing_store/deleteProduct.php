<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['seller','admin'])){
    header("Location: login.php"); exit();
}
include 'DBConn.php';
$uid  = $_SESSION['userID'];
$role = $_SESSION['role'];
$pid  = (int)($_GET['id'] ?? 0);

if($role === 'admin'){
    $conn->query("DELETE FROM tblProduct WHERE productID=$pid");
} else {
    $conn->query("DELETE FROM tblProduct WHERE productID=$pid AND sellerID=$uid");
}

header("Location: ".($role==='admin'?'adminProducts.php':'sellerDashboard.php'));
exit();
