<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: login.php"); exit(); }
include 'DBConn.php';

$uid = $_SESSION['userID'];

// Fetch order count
$orders = $conn->query("SELECT COUNT(*) as c FROM tblOrder WHERE userID=$uid")->fetch_assoc()['c'];
$cartCnt = $conn->query("SELECT SUM(quantity) as c FROM tblCart WHERE userID=$uid")->fetch_assoc()['c'] ?? 0;
$msgs = $conn->query("SELECT COUNT(*) as c FROM tblMessage WHERE receiverID=$uid AND isRead=0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Account — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page">
    <div class="page-header">
        <h2>Hello, <?php echo htmlspecialchars($_SESSION['user']); ?></h2>
        <p class="text-muted">Your personal shopping space</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.2rem;margin-bottom:2.5rem;">
        <a href="store.php" class="card" style="text-decoration:none;color:inherit;">
            <div style="font-size:2rem;margin-bottom:.5rem;">👗</div>
            <h3>Browse Store</h3>
            <p class="text-muted" style="font-size:.85rem;margin-top:.3rem;">Discover new arrivals</p>
        </a>
        <a href="cart.php" class="card" style="text-decoration:none;color:inherit;">
            <div style="font-size:2rem;margin-bottom:.5rem;">🛍️</div>
            <h3>My Bag</h3>
            <p class="text-muted" style="font-size:.85rem;margin-top:.3rem;"><?php echo $cartCnt; ?> item(s)</p>
        </a>
        <a href="messages.php" class="card" style="text-decoration:none;color:inherit;">
            <div style="font-size:2rem;margin-bottom:.5rem;">✉️</div>
            <h3>Messages</h3>
            <p class="text-muted" style="font-size:.85rem;margin-top:.3rem;"><?php echo $msgs; ?> unread</p>
        </a>
        <a href="orders.php" class="card" style="text-decoration:none;color:inherit;">
            <div style="font-size:2rem;margin-bottom:.5rem;">📦</div>
            <h3>My Orders</h3>
            <p class="text-muted" style="font-size:.85rem;margin-top:.3rem;"><?php echo $orders; ?> order(s)</p>
        </a>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3>Recent Activity</h3>
        </div>
        <?php
        $recent = $conn->query("SELECT o.orderID, o.totalAmount, o.status, o.createdAt FROM tblOrder o WHERE o.userID=$uid ORDER BY o.createdAt DESC LIMIT 5");
        if($recent->num_rows > 0):
        ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php while($o = $recent->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $o['orderID']; ?></td>
                <td>R<?php echo number_format($o['totalAmount'],2); ?></td>
                <td><span class="badge badge-gold"><?php echo ucfirst($o['status']); ?></span></td>
                <td><?php echo date('d M Y', strtotime($o['createdAt'])); ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <p class="text-muted">No orders yet. <a href="store.php">Start shopping →</a></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
