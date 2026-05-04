<?php
session_start();
include 'DBConn.php';

$message = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM tblUser WHERE username='$username'");

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();

        if(password_verify($password, $row['password'])){
            $_SESSION['user'] = $username;
            header("Location: home.php");
            exit();
        } else {
            $message = "Wrong password!";
        }
    } else {
        $message = "User not found!";
    }
}
?>

<link rel="stylesheet" href="style.css">

<div class="container">
<h2>Login</h2>
<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Login</button>
</form>

<p><?php echo $message; ?></p>
</div>