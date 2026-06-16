<?php
session_start();
if(isset($_SESSION['role']) && $_SESSION['role']==='admin'){
    header("Location: adminDashboard.php"); exit();
}
include 'DBConn.php';

$message = "";

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM tblUser WHERE username=? AND role='admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if($row && password_verify($password, $row['password'])){
        $_SESSION['user']   = $row['username'];
        $_SESSION['userID'] = $row['userID'];
        $_SESSION['role']   = 'admin';
        header("Location: adminDashboard.php");
        exit();
    } else {
        $message = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">Luks Clothing .org</div>
        <h3 style="margin-bottom:.3rem;font-weight:300;">Admin Portal</h3>
        <p class="text-muted text-center" style="font-size:.83rem;margin-bottom:1.5rem;">Restricted access</p>

        <?php if($message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button name="login" class="btn btn-gold btn-full mt2">Sign In as Admin</button>
        </form>
        <p class="text-muted text-center mt2" style="font-size:.8rem;"><a href="login.php">← Back to customer login</a></p>
    </div>
</div>
</body>
</html>
