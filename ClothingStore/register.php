<?php
include 'DBConn.php';

$message = "";

if(isset($_POST['register'])){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM tblUser WHERE username='$username'");

    if($check->num_rows > 0){
        $message = "Username exists!";
    } else {
        $conn->query("INSERT INTO tblUser (username,email,password)
                      VALUES ('$username','$email','$password')");
        $message = "Registered! Go login.";
    }
}
?>

<link rel="stylesheet" href="style.css">

<div class="container">
<h2>Register</h2>
<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="register">Register</button>
</form>

<p><?php echo $message; ?></p>
<a href="login.php">Login</a>
</div>