<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: adminLogin.php"); exit();
}
include 'DBConn.php';

$totalUsers    = $conn->query("SELECT COUNT(*) as c FROM tblUser WHERE role!='admin'")->fetch_assoc()['c'];
$totalProducts = $conn->query("SELECT COUNT(*) as c FROM tblProduct")->fetch_assoc()['c'];
$pendingProducts = $conn->query("SELECT COUNT(*) as c FROM tblProduct WHERE isApproved=0")->fetch_assoc()['c'];
$totalOrders   = $conn->query("SELECT COUNT(*) as c FROM tblOrder")->fetch_assoc()['c'];
$totalRevenue  = $conn->query("SELECT SUM(totalAmount) as s FROM tblOrder")->fetch_assoc()['s'] ?? 0;
$pendingSellers = $conn->query("SELECT COUNT(*) as c FROM tblSellerRequest WHERE status='pending'")->fetch_assoc()['c'];
$unreadMsgs    = $conn->query("SELECT COUNT(*) as c FROM tblMessage WHERE receiverID={$_SESSION['userID']} AND isRead=0")->fetch_assoc()['c'];

$recentOrders = $conn->query("SELECT o.*, u.username FROM tblOrder o JOIN tblUser u ON o.userID=u.userID ORDER BY o.createdAt DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page">
    <div class="page-header">
        <h2>Admin Dashboard</h2>
        <p class="text-muted">Overview of Luks Clothing .org</p>
    </div>

    <!-- Stats Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;">
        <div class="card">
            <div class="text-muted" style="font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;">Users</div>
            <div style="font-size:2.2rem;font-family:'Cormorant Garamond',serif;"><?php echo $totalUsers; ?></div>
        </div>
        <div class="card">
            <div class="text-muted" style="font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;">Products</div>
            <div style="font-size:2.2rem;font-family:'Cormorant Garamond',serif;"><?php echo $totalProducts; ?></div>
            <?php if($pendingProducts): ?>
                <div class="text-gold" style="font-size:.8rem;"><?php echo $pendingProducts; ?> pending</div>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="text-muted" style="font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;">Orders</div>
            <div style="font-size:2.2rem;font-family:'Cormorant Garamond',serif;"><?php echo $totalOrders; ?></div>
        </div>
        <div class="card">
            <div class="text-muted" style="font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;">Revenue</div>
            <div style="font-size:2.2rem;font-family:'Cormorant Garamond',serif;">R<?php echo number_format($totalRevenue,0); ?></div>
        </div>
        <a href="adminUsers.php" class="card" style="text-decoration:none;color:inherit;">
            <div class="text-muted" style="font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;">Seller Requests</div>
            <div style="font-size:2.2rem;font-family:'Cormorant Garamond',serif;"><?php echo $pendingSellers; ?></div>
            <div class="text-gold" style="font-size:.8rem;">Review →</div>
        </a>
        <a href="adminMessages.php" class="card" style="text-decoration:none;color:inherit;">
            <div class="text-muted" style="font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;">Unread Messages</div>
            <div style="font-size:2.2rem;font-family:'Cormorant Garamond',serif;"><?php echo $unreadMsgs; ?></div>
            <div class="text-gold" style="font-size:.8rem;">View inbox →</div>
        </a>
    </div>

    <!-- Quick actions -->
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem;">
        <a href="adminProducts.php" class="btn btn-outline">Manage Products</a>
        <a href="adminUsers.php" class="btn btn-outline">Manage Users</a>
        <a href="addProduct.php" class="btn btn-gold">+ Add Product</a>
        <a href="adminMessages.php" class="btn btn-outline">Messages</a>
    </div>

    <!-- Recent orders -->
    <div class="card">
        <h3 style="margin-bottom:1.2rem;">Recent Orders</h3>
        <?php if($recentOrders->num_rows === 0): ?>
            <p class="text-muted">No orders yet.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php while($o = $recentOrders->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $o['orderID']; ?></td>
                <td><?php echo htmlspecialchars($o['username']); ?></td>
                <td>R<?php echo number_format($o['totalAmount'],2); ?></td>
                <td>
                    <?php $bc = match($o['status']){'delivered'=>'badge-green','cancelled'=>'badge-red',default=>'badge-gold'}; ?>
                    <span class="badge <?php echo $bc; ?>"><?php echo ucfirst($o['status']); ?></span>
                </td>
                <td><?php echo date('d M Y', strtotime($o['createdAt'])); ?></td>
                <td>
                    <form method="POST" action="adminUpdateOrder.php" style="display:flex;gap:.4rem;align-items:center;">
                        <input type="hidden" name="order_id" value="<?php echo $o['orderID']; ?>">
                        <select name="status" style="padding:.3rem;font-size:.8rem;width:auto;">
                            <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $o['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-ghost btn-sm">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
