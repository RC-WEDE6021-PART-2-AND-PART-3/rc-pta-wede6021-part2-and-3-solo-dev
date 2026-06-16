<?php
session_start();
include 'DBConn.php';

$message = "";
$success = false;

if(isset($_POST['register'])){
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = in_array($_POST['role'], ['customer','seller']) ? $_POST['role'] : 'customer';

    $check = $conn->prepare("SELECT userID FROM tblUser WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        $message = "That username is already taken.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tblUser (username,email,password,role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        $stmt->execute();

        // If seller, create a pending seller request
        if($role === 'seller'){
            $uid = $conn->insert_id;
            $reason = trim($_POST['reason'] ?? '');
            $conn->query("INSERT INTO tblSellerRequest (userID,reason) VALUES ($uid,'".htmlspecialchars($reason)."')");
        }

        $success = true;
        $message = "Account created! You can now log in.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">Luks Clothing .org</div>
        <h3 style="margin-bottom:1.5rem;font-weight:300;">Create account</h3>

        <?php if($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if(!$success): ?>
        <form method="POST" id="regForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min 8 characters" minlength="8" required>
            </div>
            <div class="form-group">
                <label>I want to join as</label>
                <select name="role" id="roleSelect" onchange="toggleSeller()">
                    <option value="customer">Customer — browse &amp; buy</option>
                    <option value="seller">Seller — list clothes for sale</option>
                </select>
            </div>
            <div id="sellerReason" style="display:none;">
                <div class="form-group">
                    <label>Why do you want to sell? (optional)</label>
                    <textarea name="reason" placeholder="Tell us about what you want to sell..."></textarea>
                </div>
                <div class="alert alert-info" style="font-size:.82rem;">Seller accounts need admin approval before you can list items.</div>
            </div>
            <button name="register" class="btn btn-gold btn-full mt2">Create Account</button>
        </form>
        <?php else: ?>
            <a href="login.php" class="btn btn-gold btn-full">Sign In Now →</a>
        <?php endif; ?>

        <p class="text-muted text-center mt2" style="font-size:.82rem;">
            Already have an account? <a href="login.php">Sign in</a>
        </p>
    </div>
</div>
<script>
function toggleSeller(){
    const role = document.getElementById('roleSelect').value;
    document.getElementById('sellerReason').style.display = role === 'seller' ? 'block' : 'none';
}
</script>
</body>
</html>
