<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Photos | Facebook</title>
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
  <div style="max-width:980px;margin:18px auto 0;padding:0 10px;display:flex;gap:16px;">

    <div style="width:200px;flex-shrink:0;">
      <div style="background:#fff;border:1px solid #ccc;border-radius:3px;overflow:hidden;">
        <div style="background:#6d84b4;color:#fff;font-size:12px;font-weight:bold;padding:6px 10px;">Photos</div>
        <div style="padding:8px 0;">
          <a href="#" style="display:block;padding:5px 12px;font-size:12px;color:#333;font-weight:bold;">Your Photos</a>
          <a href="#" style="display:block;padding:5px 12px;font-size:12px;color:#333;">Albums</a>
          <a href="#" style="display:block;padding:5px 12px;font-size:12px;color:#333;">Photos of You</a>
        </div>
      </div>
    </div>

    <div style="flex:1;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="font-size:18px;font-weight:bold;color:#333;">Photos</h2>
        <a href="#" style="background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:5px 12px;border-radius:3px;font-size:12px;font-weight:bold;">+ Add Photos</a>
      </div>

      <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:40px;text-align:center;">
        <div style="font-size:40px;margin-bottom:12px;">📷</div>
        <h3 style="font-size:16px;font-weight:bold;color:#333;margin-bottom:8px;">No photos yet</h3>
        <p style="font-size:13px;color:#777;margin-bottom:16px;">Add photos to share them with friends on your timeline.</p>
        <a href="#" style="background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:7px 16px;border-radius:3px;font-size:13px;font-weight:bold;text-decoration:none;">Upload Photos</a>
      </div>
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
