<?php
session_start();
include 'DBConn.php';

if(isset($_SESSION['user'])) {
    $dest = ['admin'=>'adminDashboard.php','seller'=>'sellerDashboard.php','customer'=>'home.php'];
    header("Location: " . ($dest[$_SESSION['role']] ?? 'home.php'));
    exit();
}

$message = "";

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM tblUser WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        if(password_verify($password, $row['password'])){
            $_SESSION['user']   = $row['username'];
            $_SESSION['userID'] = $row['userID'];
            $_SESSION['role']   = $row['role'];

            $dest = ['admin'=>'adminDashboard.php','seller'=>'sellerDashboard.php','customer'=>'home.php'];
            header("Location: " . ($dest[$row['role']] ?? 'home.php'));
            exit();
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "No account found with that username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">Luks Clothing .org</div>
        <h3 style="margin-bottom:1.5rem;font-weight:300;">Welcome back</h3>

        <?php if($message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Your username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button name="login" class="btn btn-gold btn-full mt2">Sign In</button>
        </form>

        <div class="auth-divider mt2"><span>New here?</span></div>
        <a href="register.php" class="btn btn-outline btn-full">Create an account</a>

        <p class="text-muted text-center mt2" style="font-size:.8rem;">
            Admin? <a href="adminLogin.php">Admin login →</a>
        </p>
    </div>
</div>
</body>
</html>
