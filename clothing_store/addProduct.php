<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['seller','admin'])){
    header("Location: login.php"); exit();
}
include 'DBConn.php';
$uid  = $_SESSION['userID'];
$role = $_SESSION['role'];

$message = "";
$success = false;

if(isset($_POST['add'])){
    $name     = htmlspecialchars(trim($_POST['name']));
    $price    = (float)$_POST['price'];
    $desc     = htmlspecialchars(trim($_POST['description']));
    $brand    = htmlspecialchars(trim($_POST['brand']));
    $category = htmlspecialchars(trim($_POST['category']));
    $stock    = (int)$_POST['stock'];
    $approved = $role === 'admin' ? 1 : 0;
    $sellerID = $uid;

    $imageName = '';
    if(!empty($_FILES['image']['name'])){
        $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg','jpeg','png','gif','webp'];
        if(in_array($ext, $allowed)){
            $imageName = uniqid('img_').'.'.$ext;
            $target    = "uploads/".$imageName;
            if(!move_uploaded_file($_FILES['image']['tmp_name'], $target)){
                $imageName = ''; // upload failed silently
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO tblProduct (name,price,image,description,brand,category,stock,sellerID,isApproved) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sdssssiii", $name, $price, $imageName, $desc, $brand, $category, $stock, $sellerID, $approved);
    $stmt->execute();
    $success = true;
    $message = $role === 'admin' ? "Product added and published." : "Listing submitted for admin approval.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>List an Item — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page" style="max-width:680px;">
    <div class="page-header">
        <h2>List an Item</h2>
        <p class="text-muted">Fill in the details to request selling on Luks Clothing .org</p>
    </div>

    <?php if($message): ?>
        <div class="alert <?php echo $success?'alert-success':'alert-danger'; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Item Name *</label>
                    <input type="text" name="name" placeholder="e.g. Oversized Linen Blazer" required>
                </div>
                <div class="form-group">
                    <label>Brand *</label>
                    <input type="text" name="brand" placeholder="e.g. Zara, H&M, or your own" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="Tops">Tops</option>
                        <option value="Bottoms">Bottoms</option>
                        <option value="Dresses">Dresses</option>
                        <option value="Outerwear">Outerwear</option>
                        <option value="Shoes">Shoes</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (R) *</label>
                    <input type="number" name="price" placeholder="0.00" min="1" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock" placeholder="1" min="0" value="1">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Description *</label>
                    <textarea name="description" placeholder="Describe the item — condition, size, material, style..." required></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Photo</label>
                    <input type="file" name="image" accept="image/*">
                    <p class="text-muted" style="font-size:.78rem;margin-top:.3rem;">JPG, PNG or WEBP. Recommended: portrait orientation.</p>
                </div>
            </div>

            <?php if($role !== 'admin'): ?>
                <div class="alert alert-info" style="font-size:.83rem;margin-bottom:1rem;">
                    Your listing will be reviewed by an admin before going live in the store.
                </div>
            <?php endif; ?>

            <div style="display:flex;gap:1rem;justify-content:flex-end;">
                <a href="<?php echo $role==='admin'?'adminProducts.php':'sellerDashboard.php'; ?>" class="btn btn-ghost">Cancel</a>
                <button name="add" class="btn btn-gold">Submit Listing</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
