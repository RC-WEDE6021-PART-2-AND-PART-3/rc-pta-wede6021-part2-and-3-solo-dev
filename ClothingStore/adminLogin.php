<?php
session_start();

$message = "";

if(isset($_POST['login'])){
    if($_POST['username'] == "admin" && $_POST['password'] == "admin"){
        $_SESSION['admin'] = true;
        header("Location: adminDashboard.php");
        exit();
    } else {
        $message = "Wrong admin login!";
    }
}
?>

<h2>Admin Login</h2>

<form method="POST">
<input type="text" name="username" placeholder="Admin Username"><br><br>
<input type="password" name="password" placeholder="Password"><br><br>
<button name="login">Login</button>
</form>

<p><?php echo $message; ?></p>