<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['seller','admin'])){
    header("Location: login.php"); exit();
}
include 'DBConn.php';
$uid  = $_SESSION['userID'];
$role = $_SESSION['role'];
$pid  = (int)($_GET['id'] ?? 0);

// Load product — sellers can only edit their own
if($role === 'admin'){
    $product = $conn->query("SELECT * FROM tblProduct WHERE productID=$pid")->fetch_assoc();
} else {
    $product = $conn->query("SELECT * FROM tblProduct WHERE productID=$pid AND sellerID=$uid")->fetch_assoc();
}

if(!$product){ header("Location: ".($role==='admin'?'adminProducts.php':'sellerDashboard.php')); exit(); }

$message = "";

if(isset($_POST['update'])){
    $name     = htmlspecialchars(trim($_POST['name']));
    $price    = (float)$_POST['price'];
    $desc     = htmlspecialchars(trim($_POST['description']));
    $brand    = htmlspecialchars(trim($_POST['brand']));
    $category = htmlspecialchars(trim($_POST['category']));
    $stock    = (int)$_POST['stock'];

    $imageName = $product['image'];
    if(!empty($_FILES['image']['name'])){
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if(in_array($ext, $allowed)){
            $newName = uniqid('img_').'.'.$ext;
            if(move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$newName)){
                $imageName = $newName;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE tblProduct SET name=?,price=?,image=?,description=?,brand=?,category=?,stock=? WHERE productID=?");
    $stmt->bind_param("sdsssiii", $name, $price, $imageName, $desc, $brand, $category, $stock, $pid);

    // Fix: bind_param type mismatch fix
    $stmt = $conn->prepare("UPDATE tblProduct SET name=?,price=?,image=?,description=?,brand=?,category=?,stock=? WHERE productID=?");
    $stmt->bind_param("sdssssii", $name, $price, $imageName, $desc, $brand, $category, $stock, $pid);
    $stmt->execute();

    $product = $conn->query("SELECT * FROM tblProduct WHERE productID=$pid")->fetch_assoc();
    $message = "Listing updated successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Listing — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page" style="max-width:680px;">
    <div class="page-header">
        <h2>Edit Listing</h2>
        <p class="text-muted"><?php echo htmlspecialchars($product['name']); ?></p>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Item Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <?php foreach(['Tops','Bottoms','Dresses','Outerwear','Shoes','Accessories','Other'] as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $product['category']===$cat?'selected':''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (R)</label>
                    <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" min="1" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="<?php echo $product['stock']; ?>" min="0">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Photo (leave blank to keep current)</label>
                    <?php if($product['image'] && file_exists("uploads/".$product['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" style="height:80px;border-radius:4px;margin-bottom:.5rem;display:block;">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>
            </div>
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:.5rem;">
                <a href="<?php echo $role==='admin'?'adminProducts.php':'sellerDashboard.php'; ?>" class="btn btn-ghost">Cancel</a>
                <button name="update" class="btn btn-gold">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
