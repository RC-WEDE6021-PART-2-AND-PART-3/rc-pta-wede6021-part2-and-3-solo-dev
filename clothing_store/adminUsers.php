<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: adminLogin.php"); exit();
}
include 'DBConn.php';

// Verify user
if(isset($_GET['verify'])){
    $id = (int)$_GET['verify'];
    $conn->query("UPDATE tblUser SET isVerified=1 WHERE userID=$id");
    // Also approve their seller request
    $conn->query("UPDATE tblSellerRequest SET status='approved' WHERE userID=$id");
    header("Location: adminUsers.php?msg=verified"); exit();
}
// Reject seller
if(isset($_GET['reject'])){
    $id = (int)$_GET['reject'];
    $conn->query("UPDATE tblSellerRequest SET status='rejected' WHERE userID=$id");
    $conn->query("UPDATE tblUser SET role='customer' WHERE userID=$id");
    header("Location: adminUsers.php?msg=rejected"); exit();
}
// Delete user
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tblUser WHERE userID=$id AND role!='admin'");
    header("Location: adminUsers.php?msg=deleted"); exit();
}
// Change role
if(isset($_POST['change_role'])){
    $id   = (int)$_POST['user_id'];
    $role = in_array($_POST['role'],['customer','seller']) ? $_POST['role'] : 'customer';
    $conn->query("UPDATE tblUser SET role='$role' WHERE userID=$id AND role!='admin'");
    header("Location: adminUsers.php?msg=updated"); exit();
}

$tab = $_GET['tab'] ?? 'users';
$users = $conn->query("SELECT * FROM tblUser WHERE role!='admin' ORDER BY createdAt DESC");
$sellerRequests = $conn->query("SELECT sr.*, u.username, u.email FROM tblSellerRequest sr JOIN tblUser u ON sr.userID=u.userID ORDER BY sr.submittedAt DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page">
    <div class="page-header">
        <h2>Users</h2>
        <p class="text-muted">Manage customers, sellers and seller applications</p>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">User <?php echo htmlspecialchars($_GET['msg']); ?> successfully.</div>
    <?php endif; ?>

    <div class="tabs">
        <a href="?tab=users"   class="tab <?php echo $tab==='users'?'active':''; ?>">All Users</a>
        <a href="?tab=sellers" class="tab <?php echo $tab==='sellers'?'active':''; ?>">Seller Requests</a>
    </div>

    <?php if($tab === 'users'): ?>
    <div class="card">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Verified</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if($users->num_rows === 0): ?>
                <tr><td colspan="6" class="text-muted text-center" style="padding:2rem;">No users yet.</td></tr>
            <?php else: ?>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <form method="POST" style="display:flex;gap:.4rem;align-items:center;">
                        <input type="hidden" name="user_id" value="<?php echo $u['userID']; ?>">
                        <select name="role" style="padding:.3rem;font-size:.8rem;width:auto;">
                            <option value="customer" <?php echo $u['role']==='customer'?'selected':''; ?>>Customer</option>
                            <option value="seller"   <?php echo $u['role']==='seller'?'selected':''; ?>>Seller</option>
                        </select>
                        <button name="change_role" class="btn btn-ghost btn-sm">Set</button>
                    </form>
                </td>
                <td>
                    <?php if($u['isVerified']): ?>
                        <span class="badge badge-green">Verified</span>
                    <?php else: ?>
                        <span class="badge badge-muted">Unverified</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('d M Y', strtotime($u['createdAt'])); ?></td>
                <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        <?php if(!$u['isVerified']): ?>
                            <a href="?verify=<?php echo $u['userID']; ?>" class="btn btn-gold btn-sm">Verify</a>
                        <?php endif; ?>
                        <a href="adminMessages.php?compose=<?php echo $u['userID']; ?>" class="btn btn-outline btn-sm">Message</a>
                        <a href="?delete=<?php echo $u['userID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user permanently?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php else: ?>
    <div class="card">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Applicant</th><th>Email</th><th>Reason</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if($sellerRequests->num_rows === 0): ?>
                <tr><td colspan="6" class="text-muted text-center" style="padding:2rem;">No seller requests.</td></tr>
            <?php else: ?>
            <?php while($sr = $sellerRequests->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($sr['username']); ?></strong></td>
                <td><?php echo htmlspecialchars($sr['email']); ?></td>
                <td style="font-size:.85rem;max-width:240px;"><?php echo htmlspecialchars($sr['reason'] ?: '—'); ?></td>
                <td><?php echo date('d M Y', strtotime($sr['submittedAt'])); ?></td>
                <td>
                    <?php
                    $bc = match($sr['status']){'approved'=>'badge-green','rejected'=>'badge-red',default=>'badge-gold'};
                    ?>
                    <span class="badge <?php echo $bc; ?>"><?php echo ucfirst($sr['status']); ?></span>
                </td>
                <td>
                    <?php if($sr['status']==='pending'): ?>
                    <div style="display:flex;gap:.4rem;">
                        <a href="?verify=<?php echo $sr['userID']; ?>" class="btn btn-gold btn-sm">Approve</a>
                        <a href="?reject=<?php echo $sr['userID']; ?>" class="btn btn-danger btn-sm">Reject</a>
                    </div>
                    <?php else: ?>
                        <span class="text-muted" style="font-size:.82rem;">Processed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
