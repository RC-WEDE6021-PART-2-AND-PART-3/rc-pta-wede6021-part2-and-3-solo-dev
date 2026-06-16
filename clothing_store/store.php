<?php
session_start();
include 'DBConn.php';

// Add to cart
if(isset($_POST['add_to_cart']) && isset($_SESSION['userID'])){
    $uid = $_SESSION['userID'];
    $pid = (int)$_POST['product_id'];
    // Check if already in cart
    $exists = $conn->query("SELECT cartID, quantity FROM tblCart WHERE userID=$uid AND productID=$pid")->fetch_assoc();
    if($exists){
        $newQty = $exists['quantity'] + 1;
        $conn->query("UPDATE tblCart SET quantity=$newQty WHERE cartID={$exists['cartID']}");
    } else {
        $conn->query("INSERT INTO tblCart (userID,productID,quantity) VALUES ($uid,$pid,1)");
    }
    header("Location: store.php?added=1");
    exit();
}

// Filters
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort     = $_GET['sort'] ?? 'newest';

$where = "WHERE p.isApproved=1";
if($search)   $where .= " AND (p.name LIKE '%".htmlspecialchars($search)."%' OR p.brand LIKE '%".htmlspecialchars($search)."%')";
if($category) $where .= " AND p.category='".htmlspecialchars($category)."'";

$orderBy = match($sort){
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    default      => 'p.productID DESC'
};

$products = $conn->query("SELECT p.*, u.username as sellerName FROM tblProduct p LEFT JOIN tblUser u ON p.sellerID=u.userID $where ORDER BY $orderBy");
$categories = $conn->query("SELECT DISTINCT category FROM tblProduct WHERE isApproved=1 AND category IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<div class="page">
    <?php if(isset($_GET['added'])): ?>
        <div class="alert alert-success">Item added to your bag! <a href="cart.php">View bag →</a></div>
    <?php endif; ?>

    <div class="section-title">
        <div>
            <h2>The Collection</h2>
            <p class="text-muted"><?php echo $products->num_rows; ?> pieces available</p>
        </div>
    </div>

    <div class="filter-bar">
        <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;width:100%;">
            <input type="text" name="search" placeholder="Search by name or brand..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category" style="min-width:150px;">
                <option value="">All Categories</option>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category===$cat['category']?'selected':''; ?>>
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="sort" style="min-width:150px;">
                <option value="newest" <?php echo $sort==='newest'?'selected':''; ?>>Newest First</option>
                <option value="price_asc" <?php echo $sort==='price_asc'?'selected':''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo $sort==='price_desc'?'selected':''; ?>>Price: High to Low</option>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
        </form>
    </div>

    <?php if($products->num_rows === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <p style="font-size:2rem;margin-bottom:.5rem;">🔍</p>
            <h3>Nothing found</h3>
            <p class="text-muted mt1">Try a different search or category.</p>
            <a href="store.php" class="btn btn-outline mt2">Clear Filters</a>
        </div>
    <?php else: ?>
    <div class="product-grid">
        <?php while($p = $products->fetch_assoc()): ?>
        <div class="product-card">
            <?php if($p['image'] && file_exists("uploads/".$p['image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
            <?php else: ?>
                <div class="img-placeholder">👗</div>
            <?php endif; ?>
            <div class="p-info">
                <div class="p-brand"><?php echo htmlspecialchars($p['brand'] ?? 'Luks Clothing .org'); ?></div>
                <div class="p-name"><?php echo htmlspecialchars($p['name']); ?></div>
                <div class="p-price">R<?php echo number_format($p['price'],2); ?></div>
                <?php if($p['description']): ?>
                    <p class="text-muted" style="font-size:.8rem;margin-bottom:.8rem;"><?php echo htmlspecialchars(substr($p['description'],0,60)); ?>...</p>
                <?php endif; ?>
                <div class="p-actions">
                    <?php if(isset($_SESSION['user']) && $_SESSION['role']==='customer'): ?>
                        <form method="POST" style="flex:1;">
                            <input type="hidden" name="product_id" value="<?php echo $p['productID']; ?>">
                            <button name="add_to_cart" class="btn btn-gold btn-sm w100">Add to Bag</button>
                        </form>
                    <?php elseif(!isset($_SESSION['user'])): ?>
                        <a href="login.php" class="btn btn-outline btn-sm w100">Login to Buy</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
