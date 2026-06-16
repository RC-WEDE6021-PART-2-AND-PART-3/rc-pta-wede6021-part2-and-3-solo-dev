<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role']!=='customer'){
    header("Location: login.php"); exit();
}
include 'DBConn.php';
$uid = $_SESSION['userID'];

$orders = $conn->query("SELECT * FROM tblOrder WHERE userID=$uid ORDER BY createdAt DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Orders — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page">
    <div class="page-header">
        <h2>My Orders</h2>
        <p class="text-muted">Your order history</p>
    </div>

    <?php if($orders->num_rows === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <p style="font-size:3rem;">📦</p>
            <h3 class="mt1">No orders yet</h3>
            <a href="store.php" class="btn btn-gold mt2">Start Shopping</a>
        </div>
    <?php else: ?>
    <div class="card">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php while($o = $orders->fetch_assoc()):
                $items = $conn->query("SELECT oi.quantity, p.name FROM tblOrderItem oi JOIN tblProduct p ON oi.productID=p.productID WHERE oi.orderID={$o['orderID']}");
                $itemList = [];
                while($i = $items->fetch_assoc()) $itemList[] = $i['name'].' x'.$i['quantity'];
            ?>
            <tr>
                <td><strong>#<?php echo $o['orderID']; ?></strong></td>
                <td><?php echo date('d M Y', strtotime($o['createdAt'])); ?></td>
                <td style="font-size:.85rem;color:var(--muted);"><?php echo htmlspecialchars(implode(', ', $itemList)); ?></td>
                <td>R<?php echo number_format($o['totalAmount'],2); ?></td>
                <td>
                    <?php
                    $badgeClass = match($o['status']){
                        'delivered' => 'badge-green',
                        'cancelled' => 'badge-red',
                        default     => 'badge-gold'
                    };
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($o['status']); ?></span>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
