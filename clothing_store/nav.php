<?php
// nav.php — included at top of every page
// Requires session_start() to have been called already

$cartCount = 0;
if(isset($_SESSION['userID']) && $_SESSION['role'] === 'customer'){
    include_once 'DBConn.php';
    $uid = $_SESSION['userID'];
    $r = $conn->query("SELECT SUM(quantity) as cnt FROM tblCart WHERE userID=$uid");
    if($r) $cartCount = (int)$r->fetch_assoc()['cnt'];
}

$role = $_SESSION['role'] ?? '';
$username = $_SESSION['user'] ?? '';
?>
<nav class="navbar">
    <a href="<?php echo $role === 'admin' ? 'adminDashboard.php' : ($role === 'seller' ? 'sellerDashboard.php' : 'home.php'); ?>" class="brand">Luks Clothing .org</a>
    <nav>
        <?php if($role === 'customer'): ?>
            <a href="store.php">Store</a>
            <a href="cart.php">Bag <span class="cart-badge"><?php echo $cartCount ?: '0'; ?></span></a>
            <a href="messages.php">Messages</a>
            <a href="home.php">Account</a>
        <?php elseif($role === 'seller'): ?>
            <a href="sellerDashboard.php">Dashboard</a>
            <a href="addProduct.php">List Item</a>
            <a href="messages.php">Messages</a>
        <?php elseif($role === 'admin'): ?>
            <a href="adminDashboard.php">Dashboard</a>
            <a href="adminProducts.php">Products</a>
            <a href="adminUsers.php">Users</a>
            <a href="adminMessages.php">Messages</a>
        <?php endif; ?>
        <?php if($username): ?>
            <a href="logout.php" style="color:var(--muted);">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</nav>
