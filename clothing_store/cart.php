<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role']!=='customer'){
    header("Location: login.php"); exit();
}
include 'DBConn.php';
$uid = $_SESSION['userID'];

// Update quantity
if(isset($_POST['update_qty'])){
    $cartID = (int)$_POST['cart_id'];
    $qty    = (int)$_POST['quantity'];
    if($qty < 1) $qty = 1;
    $conn->query("UPDATE tblCart SET quantity=$qty WHERE cartID=$cartID AND userID=$uid");
    header("Location: cart.php"); exit();
}

// Remove item
if(isset($_GET['remove'])){
    $cartID = (int)$_GET['remove'];
    $conn->query("DELETE FROM tblCart WHERE cartID=$cartID AND userID=$uid");
    header("Location: cart.php"); exit();
}

// Checkout
if(isset($_POST['checkout'])){
    $cartItems = $conn->query("SELECT c.*, p.price FROM tblCart c JOIN tblProduct p ON c.productID=p.productID WHERE c.userID=$uid");
    $total = 0;
    $items = [];
    while($item = $cartItems->fetch_assoc()){
        $total += $item['price'] * $item['quantity'];
        $items[] = $item;
    }
    if(count($items) > 0){
        $conn->query("INSERT INTO tblOrder (userID,totalAmount) VALUES ($uid, $total)");
        $orderID = $conn->insert_id;
        foreach($items as $item){
            $conn->query("INSERT INTO tblOrderItem (orderID,productID,quantity,price) VALUES ($orderID,{$item['productID']},{$item['quantity']},{$item['price']})");
        }
        $conn->query("DELETE FROM tblCart WHERE userID=$uid");
        header("Location: cart.php?ordered=1"); exit();
    }
}

// Fetch cart
$cartItems = $conn->query("SELECT c.cartID, c.quantity, p.productID, p.name, p.price, p.image, p.brand FROM tblCart c JOIN tblProduct p ON c.productID=p.productID WHERE c.userID=$uid");
$total = 0;
$cartRows = [];
while($row = $cartItems->fetch_assoc()){
    $total += $row['price'] * $row['quantity'];
    $cartRows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Bag — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page" style="max-width:760px;">
    <div class="page-header">
        <h2>My Bag</h2>
        <p class="text-muted"><?php echo count($cartRows); ?> item(s)</p>
    </div>

    <?php if(isset($_GET['ordered'])): ?>
        <div class="alert alert-success">
            ✅ Order placed successfully! We'll be in touch about delivery.
            <a href="store.php">Continue shopping →</a>
        </div>
    <?php endif; ?>

    <?php if(empty($cartRows)): ?>
        <div class="card text-center" style="padding:3rem;">
            <p style="font-size:3rem;">🛍️</p>
            <h3 class="mt1">Your bag is empty</h3>
            <p class="text-muted mt1">Add some pieces to get started.</p>
            <a href="store.php" class="btn btn-gold mt2">Browse the Store</a>
        </div>
    <?php else: ?>
        <div class="card">
            <?php foreach($cartRows as $item): ?>
            <div class="cart-item">
                <?php if($item['image'] && file_exists("uploads/".$item['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="">
                <?php else: ?>
                    <div class="ci-img">👗</div>
                <?php endif; ?>
                <div class="ci-info">
                    <div class="ci-brand"><?php echo htmlspecialchars($item['brand'] ?? ''); ?></div>
                    <div class="ci-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="ci-price">R<?php echo number_format($item['price'],2); ?> each</div>
                </div>
                <div class="ci-qty">
                    <form method="POST" style="display:flex;align-items:center;gap:.4rem;">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cartID']; ?>">
                        <button type="button" class="qty-btn" onclick="changeQty(this,-1)">−</button>
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99" style="width:50px;text-align:center;padding:.3rem;" id="qty_<?php echo $item['cartID']; ?>">
                        <button type="button" class="qty-btn" onclick="changeQty(this,1)">+</button>
                        <button name="update_qty" class="btn btn-ghost btn-sm">Update</button>
                    </form>
                </div>
                <div style="text-align:right;min-width:90px;">
                    <div style="font-weight:500;margin-bottom:.4rem;">R<?php echo number_format($item['price']*$item['quantity'],2); ?></div>
                    <a href="cart.php?remove=<?php echo $item['cartID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')">Remove</a>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="cart-total">
                <p class="text-muted" style="margin-bottom:.3rem;">Total</p>
                <div class="total-amount text-gold">R<?php echo number_format($total,2); ?></div>
            </div>

            <form method="POST" style="display:flex;gap:1rem;justify-content:flex-end;flex-wrap:wrap;">
                <a href="store.php" class="btn btn-ghost">Continue Shopping</a>
                <button name="checkout" class="btn btn-gold" onclick="return confirm('Confirm your order of R<?php echo number_format($total,2); ?>?')">Place Order</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<script>
function changeQty(btn, delta){
    const form = btn.closest('form');
    const input = form.querySelector('input[name="quantity"]');
    input.value = Math.max(1, parseInt(input.value) + delta);
}
</script>
</body>
</html>
