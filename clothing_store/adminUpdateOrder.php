<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: adminLogin.php"); exit();
}
include 'DBConn.php';

if(isset($_POST['order_id']) && isset($_POST['status'])){
    $oid    = (int)$_POST['order_id'];
    $status = in_array($_POST['status'], ['pending','processing','shipped','delivered','cancelled'])
              ? $_POST['status'] : 'pending';
    $conn->query("UPDATE tblOrder SET status='$status' WHERE orderID=$oid");
}

header("Location: adminDashboard.php");
exit();
