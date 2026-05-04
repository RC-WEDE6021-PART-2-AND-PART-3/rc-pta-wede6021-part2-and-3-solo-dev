<?php
$conn = new mysqli("localhost", "root", "", "ClothingStore");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>