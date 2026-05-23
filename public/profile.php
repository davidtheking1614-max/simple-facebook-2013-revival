<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

$profileId = (int)($_GET['id'] ?? $me['id']);
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$profileId]);
$profile = $stmt->fetch();
if (!$profile) { header('Location: /home.php'); exit; }

$isMe = ($profileId === (int)$me['id']);

// Get posts for this user
$posts = $db->prepare("
    SELECT p.*, u.first_name, u.last_name, u.id as uid
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
    LIMIT 20
");
$posts->execute([$profileId]);
$posts = $posts->fetchAll();

// Like counts + user liked
$likedPosts = $likeCounts = $commentCount = [];
foreach ($posts as $post) {
    $s = $db->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
    $s->execute([$post['id']]);
    $likeCounts[$post['id']] = $s->fetch()['cnt'];
    $s2 = $db->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $s2->execute([$post['id'], $me['id']]);
    $likedPosts[$post['id']] = (bool)$s2->fetch();
    $s3 = $db->prepare("SELECT COUNT(*) as cnt FROM comments WHERE post_id = ?");
    $s3->execute([$post['id']]);
    $commentCount[$post['id']] = $s3->fetch()['cnt'];
}

// Comments
$postComments = [];
foreach ($posts as $post) {
    $s = $db->prepare("SELECT c.*, u.first_name, u.last_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC LIMIT 5");
    $s->execute([$post['id']]);
    $postComments[$post['id']] = $s->fetchAll();
}

// Friend count
$fc = $db->prepare("SELECT COUNT(*) as cnt FROM friends WHERE (requester_id = ? OR addressee_id = ?) AND status = 'accepted'");
$fc->execute([$profileId, $profileId]);
$friendCount = $fc->fetch()['cnt'];

// Friend request status
$friendStatus = null;
if (!$isMe) {
    $fs = $db->prepare("SELECT * FROM friends WHERE (requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?)");
    $fs->execute([$me['id'], $profileId, $profileId, $me['id']]);
    $friendStatus = $fs->fetch() ?: null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title><?= h($profile['first_name'] . ' ' . $profile['last_name']) ?> | Facebook</title>
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
  <div class="profile-cover"></div>
  <div class="profile-header">
    <div class="profile-header-inner">
      <div class="profile-avatar-big"><?= strtoupper($profile['first_name'][0]) ?></div>
      <div class="profile-name-area">
        <div class="profile-name"><?= h($profile['first_name'] . ' ' . $profile['last_name']) ?></div>
        <div class="profile-friends-count"><?= $friendCount ?> friend<?= $friendCount != 1 ? 's' : '' ?></div>
      </div>
      <div style="padding-bottom:12px;display:flex;gap:8px;align-items:center;">
        <?php if ($isMe): ?>
          <a href="/settings.php" style="background:linear-gradient(#f2f2f2,#e0e0e0);border:1px solid #ccc;padding:5px 10px;border-radius:3px;font-size:12px;color:#333;font-weight:bold;text-decoration:none;">Update Info</a>
        <?php elseif (!$friendStatus): ?>
          <form method="post" action="/friends.php">
            <input type="hidden" name="action" value="add"/>
            <input type="hidden" name="user_id" value="<?= $profileId ?>"/>
            <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>"/>
            <button type="submit" style="background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:5px 10px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">+ Add Friend</button>
          </form>
        <?php elseif ($friendStatus['status'] === 'accepted'): ?>
          <span style="font-size:12px;color:#3b5998;font-weight:bold;">✓ Friends</span>
          <form method="post" action="/friends.php" style="display:inline;">
            <input type="hidden" name="action" value="remove"/>
            <input type="hidden" name="user_id" value="<?= $profileId ?>"/>
            <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>"/>
            <button type="submit" style="background:linear-gradient(#f2f2f2,#e0e0e0);border:1px solid #ccc;color:#333;padding:5px 10px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;" onclick="return confirm('Remove friend?')">Unfriend</button>
          </form>
        <?php elseif ($friendStatus['status'] === 'pending' && $friendStatus['requester_id'] == $me['id']): ?>
          <span style="font-size:12px;color:#666;background:#f2f2f2;border:1px solid #ccc;padding:5px 10px;border-radius:3px;">Friend Request Sent</span>
          <form method="post" action="/friends.php" style="display:inline;">
            <input type="hidden" name="action" value="cancel"/>
            <input type="hidden" name="user_id" value="<?= $profileId ?>"/>
            <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>"/>
            <button type="submit" style="background:none;border:none;color:#999;font-size:11px;cursor:pointer;">Cancel</button>
          </form>
        <?php elseif ($friendStatus['status'] === 'pending' && $friendStatus['addressee_id'] == $me['id']): ?>
          <form method="post" action="/friends.php" style="display:inline;">
            <input type="hidden" name="action" value="accept"/>
            <input type="hidden" name="friend_id" value="<?= $friendStatus['id'] ?>"/>
            <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>"/>
            <button type="submit" style="background:linear-gradient(#5b77b0,#3b5998);border:1px solid #1a356e;color:#fff;padding:5px 10px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">Confirm Friend Request</button>
          </form>
          <form method="post" action="/friends.php" style="display:inline;">
            <input type="hidden" name="action" value="decline"/>
            <input type="hidden" name="friend_id" value="<?= $friendStatus['id'] ?>"/>
            <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>"/>
            <button type="submit" style="background:linear-gradient(#f2f2f2,#e0e0e0);border:1px solid #ccc;color:#333;padding:5px 10px;border-radius:3px;font-size:12px;font-weight:bold;cursor:pointer;">Delete Request</button>
          </form>
        <?php endif; ?>
        <a href="/messages.php" style="background:linear-gradient(#f2f2f2,#e0e0e0);border:1px solid #ccc;padding:5px 10px;border-radius:3px;font-size:12px;color:#333;font-weight:bold;text-decoration:none;">Message</a>
      </div>
    </div>
    <div class="profile-tabs">
      <a class="profile-tab active" href="/profile.php?id=<?= $profileId ?>">Timeline</a>
      <a class="profile-tab" href="#">About</a>
      <a class="profile-tab" href="#">Friends <span style="font-size:10px;color:#999;"><?= $friendCount ?></span></a>
      <a class="profile-tab" href="#">Photos</a>
    </div>
  </div>

  <div class="profile-layout">
    <!-- Left sidebar -->
    <div class="profile-sidebar">
      <div class="profile-info-box">
        <h3>About</h3>
        <?php if ($profile['bio']): ?>
          <div class="profile-info-item"><?= h($profile['bio']) ?></div>
        <?php endif; ?>
        <?php if ($profile['gender']): ?>
          <div class="profile-info-item">⚥ <?= h($profile['gender']) ?></div>
        <?php endif; ?>
        <?php if ($profile['birthday']): ?>
          <div class="profile-info-item">🎂 Born <?= h($profile['birthday']) ?></div>
        <?php endif; ?>
        <div class="profile-info-item">📅 Joined <?= date('F Y', strtotime($profile['created_at'])) ?></div>
        <?php if ($isMe): ?>
          <a href="/settings.php" style="font-size:11px;color:#3b5998;">Update your info</a>
        <?php endif; ?>
      </div>

      <div class="profile-info-box">
        <h3>Friends <span style="font-weight:normal;font-size:12px;color:#999;"><?= $friendCount ?></span></h3>
        <?php
        $fs2 = $db->prepare("SELECT u.* FROM users u JOIN friends f ON (f.requester_id = u.id AND f.addressee_id = ?) OR (f.addressee_id = u.id AND f.requester_id = ?) WHERE f.status = 'accepted' LIMIT 9");
        $fs2->execute([$profileId, $profileId]);
        $friends = $fs2->fetchAll();
        foreach ($friends as $fr): ?>
          <a href="/profile.php?id=<?= $fr['id'] ?>" style="display:inline-block;margin:2px;text-align:center;text-decoration:none;">
            <div class="avatar" style="display:inline-flex;width:40px;height:40px;"><?= strtoupper($fr['first_name'][0]) ?></div>
            <div style="font-size:10px;color:#333;width:40px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= h($fr['first_name']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Timeline -->
    <div class="profile-feed">
      <?php if ($isMe): ?>
      <div class="composer">
        <div class="composer-tabs">
          <div class="composer-tab active">Update Status</div>
        </div>
        <form method="post" action="/post.php">
          <input type="hidden" name="action" value="post"/>
          <div class="composer-body">
            <a class="avatar" href="/profile.php?id=<?= $me['id'] ?>"><?= strtoupper($me['first_name'][0]) ?></a>
            <textarea name="content" placeholder="What's on your mind?" required></textarea>
          </div>
          <div class="composer-footer">
            <button type="submit" class="btn-post">Post</button>
          </div>
        </form>
      </div>
      <?php endif; ?>

      <?php if (empty($posts)): ?>
        <div class="no-posts"><?= h($profile['first_name']) ?> hasn't posted anything yet.</div>
      <?php endif; ?>

      <?php foreach ($posts as $post): ?>
      <div class="post-card" id="post-<?= $post['id'] ?>">
        <div class="post-header">
          <a class="avatar" href="/profile.php?id=<?= $post['uid'] ?>"><?= strtoupper($post['first_name'][0]) ?></a>
          <div class="post-meta">
            <a class="post-author" href="/profile.php?id=<?= $post['uid'] ?>"><?= h($post['first_name'] . ' ' . $post['last_name']) ?></a>
            <span class="post-time"><?= timeAgo($post['created_at']) ?></span>
          </div>
          <?php if ($post['user_id'] == $me['id']): ?>
            <form method="post" action="/post.php" style="margin-left:auto;">
              <input type="hidden" name="action" value="delete"/>
              <input type="hidden" name="post_id" value="<?= $post['id'] ?>"/>
              <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>"/>
              <button type="submit" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:11px;" onclick="return confirm('Delete this post?')">✕</button>
            </form>
          <?php endif; ?>
        </div>
        <div class="post-content"><?= nl2br(h($post['content'])) ?></div>
        <?php if ($likeCounts[$post['id']] > 0 || $commentCount[$post['id']] > 0): ?>
        <div class="post-counts">
          <span><?= $likeCounts[$post['id']] > 0 ? '👍 ' . $likeCounts[$post['id']] : '' ?></span>
          <span><?= $commentCount[$post['id']] > 0 ? $commentCount[$post['id']] . ' comment' . ($commentCount[$post['id']] != 1 ? 's' : '') : '' ?></span>
        </div>
        <?php endif; ?>
        <div class="post-actions">
          <div class="post-action-btn <?= $likedPosts[$post['id']] ? 'liked' : '' ?>">
            <form method="post" action="/like.php">
              <input type="hidden" name="post_id" value="<?= $post['id'] ?>"/>
              <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>#post-<?= $post['id'] ?>"/>
              <button type="submit">👍 <?= $likedPosts[$post['id']] ? 'Unlike' : 'Like' ?></button>
            </form>
          </div>
          <div class="post-action-btn">💬 Comment</div>
          <div class="post-action-btn">↗ Share</div>
        </div>
        <div class="comments-section">
          <?php foreach ($postComments[$post['id']] as $comment): ?>
          <div class="comment-item">
            <a class="avatar avatar-sm" href="/profile.php?id=<?= $comment['user_id'] ?>"><?= strtoupper($comment['first_name'][0]) ?></a>
            <div class="comment-bubble">
              <span class="comment-author"><?= h($comment['first_name'] . ' ' . $comment['last_name']) ?></span>
              <?= h($comment['content']) ?>
            </div>
          </div>
          <?php endforeach; ?>
          <form class="comment-form" method="post" action="/comment.php">
            <a class="avatar avatar-sm" href="/profile.php?id=<?= $me['id'] ?>"><?= strtoupper($me['first_name'][0]) ?></a>
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>"/>
            <input type="hidden" name="redirect" value="/profile.php?id=<?= $profileId ?>#post-<?= $post['id'] ?>"/>
            <input type="text" name="content" placeholder="Write a comment..."/>
            <button type="submit">Post</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <footer class="page-footer">
    <div class="footer-links">
      <a href="#">Mobile</a><a href="#">Find Friends</a><a href="#">About</a>
      <a href="#">Privacy</a><a href="#">Terms</a><a href="#">Help</a>
    </div>
    <div class="footer-copy">Facebook © 2013</div>
  </footer>
</div>
</body>
</html>
