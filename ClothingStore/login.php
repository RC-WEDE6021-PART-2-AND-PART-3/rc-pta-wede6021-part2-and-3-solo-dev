<?php
session_start();
include 'DBConn.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM tblUser WHERE username='$username'");
    $user = $result->fetch_assoc();

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user'] = $user['username'];
        header("Location: home.php");
    } else {
        echo "Invalid login";
    }
}
?>

<form method="POST">
<input type="text" name="username"><br>
<input type="password" name="password"><br>
<button name="login">Login</button>
</form>