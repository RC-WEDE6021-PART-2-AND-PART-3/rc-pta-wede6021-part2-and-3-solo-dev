<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: adminLogin.php"); exit();
}
include 'DBConn.php';

// Approve / reject product
if(isset($_GET['approve'])){
    $pid = (int)$_GET['approve'];
    $conn->query("UPDATE tblProduct SET isApproved=1 WHERE productID=$pid");
    header("Location: adminProducts.php?msg=approved"); exit();
}
if(isset($_GET['reject'])){
    $pid = (int)$_GET['reject'];
    $conn->query("UPDATE tblProduct SET isApproved=0 WHERE productID=$pid");
    header("Location: adminProducts.php?msg=rejected"); exit();
}
if(isset($_GET['delete'])){
    $pid = (int)$_GET['delete'];
    $conn->query("DELETE FROM tblProduct WHERE productID=$pid");
    header("Location: adminProducts.php?msg=deleted"); exit();
}

$filter = $_GET['filter'] ?? 'all';
$where  = $filter === 'pending' ? "WHERE p.isApproved=0" : ($filter==='live' ? "WHERE p.isApproved=1" : "");
$products = $conn->query("SELECT p.*, u.username as sellerName FROM tblProduct p LEFT JOIN tblUser u ON p.sellerID=u.userID $where ORDER BY p.isApproved ASC, p.createdAt DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Products — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page">
    <div class="section-title">
        <div>
            <h2>Products</h2>
            <p class="text-muted"><?php echo $products->num_rows; ?> total</p>
        </div>
        <a href="addProduct.php" class="btn btn-gold">+ Add Product</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">Product <?php echo htmlspecialchars($_GET['msg']); ?> successfully.</div>
    <?php endif; ?>

    <div class="tabs">
        <a href="?filter=all"     class="tab <?php echo $filter==='all'?'active':''; ?>">All</a>
        <a href="?filter=pending" class="tab <?php echo $filter==='pending'?'active':''; ?>">Pending Approval</a>
        <a href="?filter=live"    class="tab <?php echo $filter==='live'?'active':''; ?>">Live</a>
    </div>

    <div class="card">
        <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Name</th><th>Brand</th><th>Category</th><th>Price</th><th>Seller</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if($products->num_rows === 0): ?>
                <tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">No products found.</td></tr>
            <?php else: ?>
            <?php while($p = $products->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                    <?php if($p['description']): ?>
                        <div class="text-muted" style="font-size:.78rem;"><?php echo htmlspecialchars(substr($p['description'],0,50)); ?>...</div>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($p['brand'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($p['category'] ?? '—'); ?></td>
                <td>R<?php echo number_format($p['price'],2); ?></td>
                <td><?php echo htmlspecialchars($p['sellerName'] ?? 'Admin'); ?></td>
                <td>
                    <?php if($p['isApproved']): ?>
                        <span class="badge badge-green">Live</span>
                    <?php else: ?>
                        <span class="badge badge-muted">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        <a href="editProduct.php?id=<?php echo $p['productID']; ?>" class="btn btn-outline btn-sm">Edit</a>
                        <?php if(!$p['isApproved']): ?>
                            <a href="?approve=<?php echo $p['productID']; ?>" class="btn btn-gold btn-sm">Approve</a>
                        <?php else: ?>
                            <a href="?reject=<?php echo $p['productID']; ?>" class="btn btn-ghost btn-sm">Unpublish</a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $p['productID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this product?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</body>
</html>
