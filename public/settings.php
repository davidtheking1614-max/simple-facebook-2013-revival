<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fn = trim($_POST['first_name'] ?? '');
    $ln = trim($_POST['last_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $newPass = $_POST['new_password'] ?? '';
    $curPass = $_POST['current_password'] ?? '';

    // Validate name
    if (!$fn || !$ln) {
        $error = 'First and last name are required.';
    } else {
        // Update profile info
        $stmt = $db->prepare('UPDATE users SET first_name = ?, last_name = ?, bio = ? WHERE id = ?');
        $stmt->execute([$fn, $ln, $bio, $me['id']]);
        $success = 'Profile updated successfully!';
        $me = currentUser();

        // Change password only if new password field is filled
        if ($newPass !== '') {
            if (strlen($newPass) < 6) {
                $error = 'New password must be at least 6 characters.';
                $success = '';
            } elseif (!password_verify($curPass, $me['password_hash'])) {
                $error = 'Current password is incorrect.';
                $success = '';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $me['id']]);
                $success = 'Password updated successfully!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Settings | Facebook</title>
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
      <a href="/logout.php" style="color:#ffcccc;">Log Out</a>
    </div>
  </div>
</div>
<div class="page-wrapper">
  <div style="max-width:600px;margin:30px auto 0;padding:0 10px;">
    <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:24px;">
      <h2 style="font-size:20px;font-weight:bold;color:#333;margin-bottom:16px;border-bottom:1px solid #eee;padding-bottom:8px;">Account Settings</h2>
      <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
      <form method="post" action="/settings.php">
        <div style="margin-bottom:14px;">
          <label style="display:block;font-size:13px;color:#555;margin-bottom:4px;font-weight:bold;">First Name</label>
          <input type="text" name="first_name" value="<?= h($me['first_name']) ?>" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:14px;"/>
        </div>
        <div style="margin-bottom:14px;">
          <label style="display:block;font-size:13px;color:#555;margin-bottom:4px;font-weight:bold;">Last Name</label>
          <input type="text" name="last_name" value="<?= h($me['last_name']) ?>" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:14px;"/>
        </div>
        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:13px;color:#555;margin-bottom:4px;font-weight:bold;">Bio</label>
          <textarea name="bio" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:14px;height:80px;font-family:inherit;resize:vertical;"><?= h($me['bio']) ?></textarea>
        </div>
        <div style="margin-bottom:14px;padding-top:14px;border-top:1px solid #eee;">
          <div style="font-size:14px;font-weight:bold;color:#333;margin-bottom:12px;">Change Password <span style="font-size:12px;font-weight:normal;color:#aaa;">(optional)</span></div>
          <div style="margin-bottom:8px;">
            <label style="display:block;font-size:13px;color:#555;margin-bottom:4px;">Current password</label>
            <input type="password" name="current_password" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:14px;"/>
          </div>
          <div>
            <label style="display:block;font-size:13px;color:#555;margin-bottom:4px;">New password</label>
            <input type="password" name="new_password" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:14px;"/>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
          <button type="submit" style="background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:8px 20px;border-radius:3px;font-size:13px;font-weight:bold;cursor:pointer;">Save Changes</button>
          <a href="/profile.php?id=<?= $me['id'] ?>" style="font-size:13px;color:#666;">Cancel</a>
        </div>
      </form>
    </div>
  </div>
  <footer class="page-footer">
    <div class="footer-copy">Facebook © 2013</div>
  </footer>
</div>
</body>
</html>
