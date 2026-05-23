<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $db->prepare("SELECT * FROM users WHERE id != ? AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?) ORDER BY first_name LIMIT 20");
    $stmt->execute([$me['id'], $like, $like, $like]);
    $results = $stmt->fetchAll();
}

// Friend statuses for results
$friendStatuses = [];
foreach ($results as $r) {
    $fs = $db->prepare("SELECT * FROM friends WHERE (requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?)");
    $fs->execute([$me['id'], $r['id'], $r['id'], $me['id']]);
    $friendStatuses[$r['id']] = $fs->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title><?= $q ? h($q) . ' - Search' : 'Search' ?> | Facebook</title>
<link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<div id="blueBar">
  <div class="inner">
    <a class="fb-logo-text" href="/home.php">facebook</a>
    <div style="flex:1;display:flex;justify-content:center;">
      <form style="display:flex;gap:6px;align-items:center;" method="get" action="/search.php">
        <input type="text" name="q" value="<?= h($q) ?>" placeholder="🔍  Search for people, places and things"
          style="width:260px;padding:3px 8px;border:1px solid #1a356e;border-radius:3px;background:#fff;font-size:11px;height:22px;" autofocus/>
        <button type="submit" class="nav-btn">Search</button>
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
      <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:10px;">
        <div style="font-size:13px;font-weight:bold;color:#333;margin-bottom:8px;border-bottom:1px solid #eee;padding-bottom:6px;">Search Results</div>
        <div style="font-size:12px;color:#6d84b4;font-weight:bold;padding:4px 0;">People</div>
      </div>
    </div>

    <div style="flex:1;">
      <?php if ($q === ''): ?>
        <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:30px;text-align:center;color:#aaa;font-size:14px;">
          Enter a name to search for people on Facebook.
        </div>
      <?php elseif (empty($results)): ?>
        <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:24px;">
          <h2 style="font-size:18px;font-weight:bold;color:#333;margin-bottom:8px;">No results for "<?= h($q) ?>"</h2>
          <p style="font-size:13px;color:#777;">Try searching for a person by their first or last name.</p>
        </div>
      <?php else: ?>
        <div style="margin-bottom:10px;font-size:13px;color:#555;">
          People named <strong><?= h($q) ?></strong> · <?= count($results) ?> result<?= count($results) != 1 ? 's' : '' ?>
        </div>
        <?php foreach ($results as $u): ?>
        <div style="background:#fff;border:1px solid #ccc;border-radius:3px;padding:12px;margin-bottom:8px;display:flex;align-items:center;gap:12px;">
          <a href="/profile.php?id=<?= $u['id'] ?>">
            <div class="avatar avatar-lg"><?= strtoupper($u['first_name'][0]) ?></div>
          </a>
          <div style="flex:1;">
            <a href="/profile.php?id=<?= $u['id'] ?>" style="font-size:14px;font-weight:bold;color:#333;display:block;"><?= h($u['first_name'] . ' ' . $u['last_name']) ?></a>
          </div>
          <div>
            <?php $fs = $friendStatuses[$u['id']]; ?>
            <?php if (!$fs): ?>
              <form method="post" action="/friends.php" style="display:inline;">
                <input type="hidden" name="action" value="add"/>
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>"/>
                <input type="hidden" name="redirect" value="/search.php?q=<?= urlencode($q) ?>"/>
                <button type="submit" style="background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:5px 12px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">+ Add Friend</button>
              </form>
            <?php elseif ($fs['status'] === 'pending' && $fs['requester_id'] == $me['id']): ?>
              <span style="font-size:12px;color:#666;background:#f2f2f2;border:1px solid #ccc;padding:5px 12px;border-radius:3px;">Request Sent</span>
            <?php elseif ($fs['status'] === 'pending' && $fs['addressee_id'] == $me['id']): ?>
              <form method="post" action="/friends.php" style="display:inline;">
                <input type="hidden" name="action" value="accept"/>
                <input type="hidden" name="friend_id" value="<?= $fs['id'] ?>"/>
                <button type="submit" style="background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:5px 12px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">Confirm</button>
              </form>
            <?php else: ?>
              <span style="font-size:12px;color:#3b5998;font-weight:bold;">✓ Friends</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
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
