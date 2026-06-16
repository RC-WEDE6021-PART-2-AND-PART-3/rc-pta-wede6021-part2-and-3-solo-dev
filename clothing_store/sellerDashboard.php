<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role']!=='seller'){
    header("Location: login.php"); exit();
}
include 'DBConn.php';
$uid = $_SESSION['userID'];

// Check if verified
$user = $conn->query("SELECT isVerified FROM tblUser WHERE userID=$uid")->fetch_assoc();
$isVerified = $user['isVerified'];

$myProducts = $conn->query("SELECT * FROM tblProduct WHERE sellerID=$uid ORDER BY createdAt DESC");
$unread = $conn->query("SELECT COUNT(*) as c FROM tblMessage WHERE receiverID=$uid AND isRead=0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Dashboard — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page">
    <div class="page-header">
        <h2>Seller Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['user']); ?></p>
    </div>

    <?php if(!$isVerified): ?>
    <div class="alert alert-info" style="margin-bottom:1.5rem;">
        ⏳ Your seller account is pending admin approval. You can still list items, but they won't appear in the store until approved.
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.2rem;margin-bottom:2rem;">
        <div class="card">
            <div style="font-size:1.8rem;"><?php echo $myProducts->num_rows; ?></div>
            <div class="text-muted" style="font-size:.85rem;">Listings</div>
        </div>
        <div class="card">
            <div style="font-size:1.8rem;"><?php echo $unread; ?></div>
            <div class="text-muted" style="font-size:.85rem;">Unread Messages</div>
        </div>
        <a href="addProduct.php" class="card" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:1rem;">
            <span style="font-size:1.8rem;">+</span>
            <div>
                <div style="font-weight:500;">List a New Item</div>
                <div class="text-muted" style="font-size:.82rem;">Add clothing to sell</div>
            </div>
        </a>
        <a href="messages.php" class="card" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:1rem;">
            <span style="font-size:1.8rem;">✉️</span>
            <div>
                <div style="font-weight:500;">Messages</div>
                <div class="text-muted" style="font-size:.82rem;">Talk to admin</div>
            </div>
        </a>
    </div>

    <div class="card">
        <div class="section-title">
            <h3>My Listings</h3>
            <a href="addProduct.php" class="btn btn-gold btn-sm">+ Add Item</a>
        </div>

        <?php if($myProducts->num_rows === 0): ?>
            <p class="text-muted">No listings yet. <a href="addProduct.php">Add your first item →</a></p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Item</th><th>Brand</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php
            $myProducts->data_seek(0);
            while($p = $myProducts->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($p['brand'] ?? '—'); ?></td>
                <td>R<?php echo number_format($p['price'],2); ?></td>
                <td>
                    <?php if($p['isApproved']): ?>
                        <span class="badge badge-green">Live</span>
                    <?php else: ?>
                        <span class="badge badge-muted">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="editProduct.php?id=<?php echo $p['productID']; ?>" class="btn btn-outline btn-sm">Edit</a>
                    <a href="deleteProduct.php?id=<?php echo $p['productID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this listing?')">Delete</a>
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
