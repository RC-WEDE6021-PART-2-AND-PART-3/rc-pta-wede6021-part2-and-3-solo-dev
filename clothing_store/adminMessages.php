<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: adminLogin.php"); exit();
}
include 'DBConn.php';
$uid = $_SESSION['userID'];

$success = "";

// Send message
if(isset($_POST['send'])){
    $receiverID = (int)$_POST['receiver_id'];
    $subject    = htmlspecialchars(trim($_POST['subject']));
    $body       = htmlspecialchars(trim($_POST['message']));
    if($body && $receiverID){
        $conn->query("INSERT INTO tblMessage (senderID,receiverID,subject,message) VALUES ($uid,$receiverID,'$subject','$body')");
        $success = "Message sent.";
    }
}

// Mark as read when opening thread
if(isset($_GET['thread'])){
    $otherID = (int)$_GET['thread'];
    $conn->query("UPDATE tblMessage SET isRead=1 WHERE receiverID=$uid AND senderID=$otherID");
}

$allUsers = $conn->query("SELECT userID, username, role FROM tblUser WHERE role!='admin' ORDER BY username");

// All unique conversations
$convQ = $conn->query("
    SELECT DISTINCT u.userID, u.username, u.role,
        (SELECT COUNT(*) FROM tblMessage WHERE receiverID=$uid AND senderID=u.userID AND isRead=0) as unread
    FROM tblMessage m
    JOIN tblUser u ON (u.userID = IF(m.senderID=$uid, m.receiverID, m.senderID))
    WHERE m.senderID=$uid OR m.receiverID=$uid
    ORDER BY unread DESC, u.username ASC
");

$composeFor = isset($_GET['compose']) ? (int)$_GET['compose'] : null;
$activeThread = isset($_GET['thread']) ? (int)$_GET['thread'] : $composeFor;
$threadUser = null;
$threadMsgs = null;
if($activeThread){
    $threadUser = $conn->query("SELECT username, role FROM tblUser WHERE userID=$activeThread")->fetch_assoc();
    if($threadUser){
        $threadMsgs = $conn->query("
            SELECT m.*, u.username as senderName FROM tblMessage m
            JOIN tblUser u ON m.senderID=u.userID
            WHERE (m.senderID=$uid AND m.receiverID=$activeThread)
               OR (m.senderID=$activeThread AND m.receiverID=$uid)
            ORDER BY m.sentAt ASC
        ");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Messages — Luks Clothing .org</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .msg-layout { display:grid; grid-template-columns:260px 1fr; gap:1.5rem; min-height:520px; }
        .msg-sidebar { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; display:flex; flex-direction:column; }
        .msg-sidebar-header { padding:.8rem 1.1rem; border-bottom:1px solid var(--border); font-size:.78rem; letter-spacing:.06em; color:var(--muted); text-transform:uppercase; }
        .conv-item { padding:.75rem 1.1rem; border-bottom:1px solid var(--border); text-decoration:none; color:inherit; display:flex; justify-content:space-between; align-items:center; transition:background .15s; }
        .conv-item:hover, .conv-item.active { background:rgba(201,168,76,.07); }
        .msg-main { display:flex; flex-direction:column; gap:1rem; }
        .msg-body { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; flex:1; overflow-y:auto; min-height:360px; }
        @media(max-width:640px){ .msg-layout { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="page">
    <div class="section-title">
        <div>
            <h2>Messages</h2>
            <p class="text-muted">Communicate with buyers and sellers</p>
        </div>
    </div>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- New Conversation -->
    <div class="card" style="margin-bottom:1.5rem;">
        <h3 style="margin-bottom:1rem;font-size:1rem;">Start a New Conversation</h3>
        <form method="POST" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;align-items:end;">
            <div class="form-group" style="margin:0;">
                <label>Send to</label>
                <select name="receiver_id" required>
                    <option value="">— Select user —</option>
                    <?php while($u = $allUsers->fetch_assoc()): ?>
                        <option value="<?php echo $u['userID']; ?>" <?php echo $composeFor==$u['userID']?'selected':''; ?>>
                            <?php echo htmlspecialchars($u['username']); ?> (<?php echo $u['role']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label>Subject</label>
                <input type="text" name="subject" placeholder="e.g. Regarding your order">
            </div>
            <div></div>
            <div class="form-group" style="margin:0;grid-column:1/-1;">
                <label>Message</label>
                <textarea name="message" placeholder="Type your message here..." style="min-height:80px;" required></textarea>
            </div>
            <div>
                <button name="send" class="btn btn-gold">Send Message</button>
            </div>
        </form>
    </div>

    <!-- Thread view -->
    <div class="msg-layout">
        <div class="msg-sidebar">
            <div class="msg-sidebar-header">Conversations</div>
            <?php if($convQ->num_rows === 0): ?>
                <p style="padding:1rem;color:var(--muted);font-size:.85rem;">No conversations yet.</p>
            <?php else: ?>
            <?php while($c = $convQ->fetch_assoc()): ?>
                <a href="adminMessages.php?thread=<?php echo $c['userID']; ?>" class="conv-item <?php echo $activeThread==$c['userID']?'active':''; ?>">
                    <div>
                        <div style="font-size:.9rem;"><?php echo htmlspecialchars($c['username']); ?></div>
                        <div style="font-size:.72rem;color:var(--muted);"><?php echo ucfirst($c['role']); ?></div>
                    </div>
                    <?php if($c['unread'] > 0): ?>
                        <span class="badge badge-gold"><?php echo $c['unread']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <div class="msg-main">
            <?php if($activeThread && $threadUser): ?>
                <div class="msg-body">
                    <h3 style="font-size:1rem;color:var(--muted);margin-bottom:1.2rem;">
                        Thread with <span style="color:var(--text);"><?php echo htmlspecialchars($threadUser['username']); ?></span>
                        <span class="badge badge-muted" style="margin-left:.5rem;"><?php echo ucfirst($threadUser['role']); ?></span>
                    </h3>
                    <div class="message-thread">
                        <?php if(!$threadMsgs || $threadMsgs->num_rows === 0): ?>
                            <p class="text-muted">No messages yet. Send the first one above.</p>
                        <?php else: ?>
                        <?php while($msg = $threadMsgs->fetch_assoc()): ?>
                            <div>
                                <div class="msg-bubble <?php echo $msg['senderID']==$uid?'msg-sent':'msg-recv'; ?>">
                                    <?php if($msg['subject']): ?>
                                        <strong style="display:block;font-size:.78rem;margin-bottom:.3rem;"><?php echo htmlspecialchars($msg['subject']); ?></strong>
                                    <?php endif; ?>
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                                <div class="msg-meta" style="text-align:<?php echo $msg['senderID']==$uid?'right':'left'; ?>">
                                    <?php echo $msg['senderID']==$uid?'You':htmlspecialchars($msg['senderName']); ?> · <?php echo date('d M, H:i', strtotime($msg['sentAt'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="POST" style="display:flex;gap:.8rem;align-items:flex-end;">
                    <input type="hidden" name="receiver_id" value="<?php echo $activeThread; ?>">
                    <input type="hidden" name="subject" value="Re: conversation">
                    <textarea name="message" placeholder="Reply..." style="flex:1;min-height:70px;resize:none;" required></textarea>
                    <button name="send" class="btn btn-gold">Send</button>
                </form>
            <?php else: ?>
                <div class="msg-body" style="display:flex;align-items:center;justify-content:center;">
                    <div class="text-center text-muted">
                        <p style="font-size:2.5rem;">✉️</p>
                        <p class="mt1">Select a conversation or start a new one above.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
