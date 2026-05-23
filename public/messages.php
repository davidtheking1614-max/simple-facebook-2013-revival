<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

// Get accepted friends for the sidebar
$friends = $db->prepare("SELECT u.* FROM users u JOIN friends f ON (f.requester_id = u.id AND f.addressee_id = ?) OR (f.addressee_id = u.id AND f.requester_id = ?) WHERE f.status = 'accepted' ORDER BY u.first_name");
$friends->execute([$me['id'], $me['id']]);
$friends = $friends->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Messages | Facebook</title>
<link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<div id="blueBar">
  <div class="inner">
    <a class="fb-logo-text" href="/home.php">facebook</a>
    <div style="flex:1;display:flex;justify-content:center;">
      <form style="display:flex;gap:6px;align-items:center;" method="get" action="/search.php">
        <input type="text" name="q" placeholder="🔍  Search for people, places and things"
          style="width:240px;padding:3px 8px;border:1px solid #1a356e;border-radius:3px;background:#fff;font-size:11px;height:22px;"/>
      </form>
    </div>
    <div class="nav-right">
      <a href="/profile.php?id=<?= $me['id'] ?>" class="nav-username"><?= h($me['first_name'] . ' ' . $me['last_name']) ?></a>
      <a href="/home.php">Home</a>
      <a href="/profile.php?id=<?= $me['id'] ?>">Profile</a>
      <a href="/friends.php">Friends</a>
      <a href="/logout.php" style="color:#ffcccc;">Log Out</a>
    </div>
  </div>
</div>

<div class="page-wrapper">
  <div style="max-width:980px;margin:18px auto 0;padding:0 10px;display:flex;gap:0;background:#fff;border:1px solid #ccc;border-radius:3px;min-height:500px;">

    <!-- Inbox list -->
    <div style="width:260px;border-right:1px solid #ddd;flex-shrink:0;">
      <div style="padding:10px 12px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:15px;font-weight:bold;color:#333;">Messages</span>
        <a href="#" style="font-size:12px;color:#3b5998;">New Message</a>
      </div>
      <div style="padding:6px 0;">
        <?php if (empty($friends)): ?>
          <div style="padding:20px;text-align:center;color:#aaa;font-size:13px;">No conversations yet.<br/><a href="/friends.php">Add friends</a> to start messaging.</div>
        <?php else: foreach ($friends as $f): ?>
        <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;border-bottom:1px solid #f5f5f5;">
          <div class="avatar"><?= strtoupper($f['first_name'][0]) ?></div>
          <div>
            <div style="font-size:13px;font-weight:bold;color:#333;"><?= h($f['first_name'] . ' ' . $f['last_name']) ?></div>
            <div style="font-size:11px;color:#aaa;">Say hello!</div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Message area -->
    <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;">
      <div style="width:60px;height:60px;background:#e9eaed;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:16px;">✉</div>
      <h2 style="font-size:18px;font-weight:bold;color:#333;margin-bottom:8px;">Select a conversation</h2>
      <p style="font-size:13px;color:#aaa;max-width:300px;">Choose from your existing conversations or start a new one by clicking "New Message" above.</p>
    </div>
  </div>

  <footer class="page-footer">
    <div class="footer-links">
      <a href="#">Mobile</a><a href="#">About</a><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Help</a>
    </div>
    <div class="footer-copy">Facebook © 2013</div>
  </footer>
</div>
</body>
</html>
