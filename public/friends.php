<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

$action = $_POST['action'] ?? '';
$redirect = $_POST['redirect'] ?? '/friends.php';

if ($action === 'add') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid && $uid !== $me['id']) {
        // Check if any relationship already exists either direction
        $check = $db->prepare("SELECT id FROM friends WHERE (requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?)");
        $check->execute([$me['id'], $uid, $uid, $me['id']]);
        if (!$check->fetch()) {
            $db->prepare('INSERT INTO friends (requester_id, addressee_id, status) VALUES (?, ?, ?)')->execute([$me['id'], $uid, 'pending']);
        }
    }
    header('Location: ' . $redirect); exit;
}

if ($action === 'accept') {
    $fid = (int)($_POST['friend_id'] ?? 0);
    $db->prepare("UPDATE friends SET status = 'accepted' WHERE id = ? AND addressee_id = ?")->execute([$fid, $me['id']]);
    header('Location: ' . $redirect); exit;
}

if ($action === 'decline') {
    $fid = (int)($_POST['friend_id'] ?? 0);
    $db->prepare("DELETE FROM friends WHERE id = ? AND addressee_id = ?")->execute([$fid, $me['id']]);
    header('Location: ' . $redirect); exit;
}

if ($action === 'remove') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid) {
        $db->prepare("DELETE FROM friends WHERE (requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?)")->execute([$me['id'], $uid, $uid, $me['id']]);
    }
    header('Location: ' . $redirect); exit;
}

if ($action === 'cancel') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid) {
        $db->prepare("DELETE FROM friends WHERE requester_id = ? AND addressee_id = ? AND status = 'pending'")->execute([$me['id'], $uid]);
    }
    header('Location: ' . $redirect); exit;
}

// Fetch accepted friends (no duplicates)
$friends = $db->prepare("SELECT u.* FROM users u JOIN friends f ON (f.requester_id = u.id AND f.addressee_id = ?) OR (f.addressee_id = u.id AND f.requester_id = ?) WHERE f.status = 'accepted'");
$friends->execute([$me['id'], $me['id']]);
$friends = $friends->fetchAll();

// Incoming friend requests (others sent to me)
$requests = $db->prepare("SELECT f.id as friend_req_id, u.* FROM friends f JOIN users u ON f.requester_id = u.id WHERE f.addressee_id = ? AND f.status = 'pending'");
$requests->execute([$me['id']]);
$requests = $requests->fetchAll();

// People you may know (not yet any relationship)
$existingIds = array_column($friends, 'id');
$pendingIds = array_column($requests, 'id');
// Also exclude users I sent pending requests to
$sentPending = $db->prepare("SELECT addressee_id FROM friends WHERE requester_id = ? AND status = 'pending'");
$sentPending->execute([$me['id']]);
$sentIds = array_column($sentPending->fetchAll(), 'addressee_id');

$excludeIds = array_unique(array_merge($existingIds, $pendingIds, $sentIds, [$me['id']]));
$placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
$suggestions = $db->prepare("SELECT * FROM users WHERE id NOT IN ($placeholders) ORDER BY created_at DESC LIMIT 12");
$suggestions->execute($excludeIds);
$suggestions = $suggestions->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Find Friends | Facebook</title>
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
  <div style="max-width:980px;margin:18px auto 0;padding:0 10px;">
    <h2 style="font-size:20px;font-weight:bold;color:#333;margin-bottom:14px;">Find Friends</h2>

    <?php if (!empty($requests)): ?>
    <div style="margin-bottom:20px;">
      <h3 style="font-size:14px;font-weight:bold;color:#3b5998;margin-bottom:10px;">
        Friend Requests <span style="background:#3b5998;color:#fff;border-radius:9px;padding:1px 6px;font-size:11px;"><?= count($requests) ?></span>
      </h3>
      <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php foreach ($requests as $req): ?>
        <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:12px;width:210px;">
          <a href="/profile.php?id=<?= $req['id'] ?>" style="display:flex;align-items:center;gap:8px;margin-bottom:10px;text-decoration:none;">
            <div class="avatar"><?= strtoupper($req['first_name'][0]) ?></div>
            <strong style="color:#333;font-size:13px;"><?= h($req['first_name'] . ' ' . $req['last_name']) ?></strong>
          </a>
          <div style="display:flex;gap:6px;">
            <form method="post" action="/friends.php" style="flex:1;">
              <input type="hidden" name="action" value="accept"/>
              <input type="hidden" name="friend_id" value="<?= $req['friend_req_id'] ?>"/>
              <button type="submit" style="width:100%;background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:5px 4px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">Confirm</button>
            </form>
            <form method="post" action="/friends.php" style="flex:1;">
              <input type="hidden" name="action" value="decline"/>
              <input type="hidden" name="friend_id" value="<?= $req['friend_req_id'] ?>"/>
              <button type="submit" style="width:100%;background:linear-gradient(#f2f2f2,#e0e0e0);border:1px solid #ccc;color:#333;padding:5px 4px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">Delete</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($friends)): ?>
    <div style="margin-bottom:20px;">
      <h3 style="font-size:14px;font-weight:bold;color:#333;margin-bottom:10px;">Your Friends (<?= count($friends) ?>)</h3>
      <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php foreach ($friends as $f): ?>
        <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:12px;width:210px;">
          <a href="/profile.php?id=<?= $f['id'] ?>" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;text-decoration:none;">
            <div class="avatar"><?= strtoupper($f['first_name'][0]) ?></div>
            <span style="color:#333;font-size:13px;font-weight:bold;"><?= h($f['first_name'] . ' ' . $f['last_name']) ?></span>
          </a>
          <form method="post" action="/friends.php">
            <input type="hidden" name="action" value="remove"/>
            <input type="hidden" name="user_id" value="<?= $f['id'] ?>"/>
            <button type="submit" style="width:100%;background:linear-gradient(#f2f2f2,#e0e0e0);border:1px solid #ccc;color:#333;padding:4px;border-radius:3px;font-size:11px;cursor:pointer;" onclick="return confirm('Remove <?= h($f['first_name']) ?> as a friend?')">Unfriend</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div>
      <h3 style="font-size:14px;font-weight:bold;color:#333;margin-bottom:10px;">People You May Know</h3>
      <?php if (empty($suggestions)): ?>
        <p style="color:#aaa;font-size:13px;">No suggestions right now. <a href="/search.php">Search</a> for people you know!</p>
      <?php else: ?>
      <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php foreach ($suggestions as $s): ?>
        <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:12px;width:210px;text-align:center;">
          <a href="/profile.php?id=<?= $s['id'] ?>">
            <div class="avatar avatar-lg" style="margin:0 auto 8px;display:flex;"><?= strtoupper($s['first_name'][0]) ?></div>
          </a>
          <a href="/profile.php?id=<?= $s['id'] ?>" style="font-size:13px;font-weight:bold;color:#333;display:block;margin-bottom:10px;"><?= h($s['first_name'] . ' ' . $s['last_name']) ?></a>
          <form method="post" action="/friends.php">
            <input type="hidden" name="action" value="add"/>
            <input type="hidden" name="user_id" value="<?= $s['id'] ?>"/>
            <input type="hidden" name="redirect" value="/friends.php"/>
            <button type="submit" style="width:100%;background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:6px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">+ Add Friend</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
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
