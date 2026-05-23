<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

// Fetch posts (all users, most recent first)
$posts = $db->query("
    SELECT p.*, u.first_name, u.last_name, u.id as uid
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT 40
")->fetchAll();

// Get like counts + whether current user liked
$likedPosts = [];
$likeCounts = [];
foreach ($posts as $post) {
    $s = $db->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
    $s->execute([$post['id']]);
    $likeCounts[$post['id']] = $s->fetch()['cnt'];
    $s2 = $db->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $s2->execute([$post['id'], $me['id']]);
    $likedPosts[$post['id']] = (bool)$s2->fetch();
}

// Fetch comments for each post
$postComments = [];
foreach ($posts as $post) {
    $s = $db->prepare("
        SELECT c.*, u.first_name, u.last_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
        LIMIT 5
    ");
    $s->execute([$post['id']]);
    $postComments[$post['id']] = $s->fetchAll();
}

// Fetch friends / people you may know
$users = $db->query("SELECT * FROM users WHERE id != {$me['id']} ORDER BY created_at DESC LIMIT 8")->fetchAll();
$commentCount = [];
foreach ($posts as $post) {
    $s = $db->prepare("SELECT COUNT(*) as cnt FROM comments WHERE post_id = ?");
    $s->execute([$post['id']]);
    $commentCount[$post['id']] = $s->fetch()['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Facebook</title>
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
  <div class="home-layout">

    <!-- Left Sidebar -->
    <div class="home-sidebar-left">
      <div class="sidebar-user-card">
        <a class="avatar" href="/profile.php?id=<?= $me['id'] ?>"><?= strtoupper($me['first_name'][0]) ?></a>
        <div>
          <a class="sidebar-user-name" href="/profile.php?id=<?= $me['id'] ?>"><?= h($me['first_name'] . ' ' . $me['last_name']) ?></a>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="/home.php" class="active">⊞ News Feed</a>
        <a href="/messages.php">✉ Messages</a>
        <a href="/events.php">📅 Events</a>
        <a href="/friends.php">👥 Find Friends</a>
        <a href="/photos.php">📷 Photos</a>
      </nav>
      <div class="sidebar-section-title">Pages</div>
      <nav class="sidebar-nav">
        <a href="#">📄 Like Pages</a>
        <a href="#">+ Create a Page</a>
      </nav>
      <div class="sidebar-section-title">Apps</div>
      <nav class="sidebar-nav">
        <a href="#">🎮 Games</a>
        <a href="#">🎵 Music</a>
      </nav>
    </div>

    <!-- Center Feed -->
    <div class="home-feed">

      <!-- Composer -->
      <div class="composer">
        <div class="composer-tabs">
          <div class="composer-tab active">Update Status</div>
          <div class="composer-tab">Add Photo/Video</div>
          <div class="composer-tab">Ask Question</div>
        </div>
        <form method="post" action="/post.php">
          <input type="hidden" name="action" value="post"/>
          <div class="composer-body">
            <a class="avatar" href="/profile.php?id=<?= $me['id'] ?>"><?= strtoupper($me['first_name'][0]) ?></a>
            <textarea name="content" placeholder="What's on your mind, <?= h($me['first_name']) ?>?" required></textarea>
          </div>
          <div class="composer-footer">
            <button type="submit" class="btn-post">Post</button>
          </div>
        </form>
      </div>

      <?php if (empty($posts)): ?>
        <div class="no-posts">
          <p>No posts yet. Be the first to share something!</p>
        </div>
      <?php endif; ?>

      <!-- Posts -->
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
              <button type="submit" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:11px;" onclick="return confirm('Delete this post?')">✕</button>
            </form>
          <?php endif; ?>
        </div>
        <div class="post-content"><?= nl2br(h($post['content'])) ?></div>
        <?php if ($likeCounts[$post['id']] > 0 || $commentCount[$post['id']] > 0): ?>
        <div class="post-counts">
          <span><?= $likeCounts[$post['id']] > 0 ? '👍 ' . $likeCounts[$post['id']] . ' ' . ($likeCounts[$post['id']] == 1 ? 'person' : 'people') . ' like this' : '' ?></span>
          <span><?= $commentCount[$post['id']] > 0 ? $commentCount[$post['id']] . ' comment' . ($commentCount[$post['id']] != 1 ? 's' : '') : '' ?></span>
        </div>
        <?php endif; ?>
        <div class="post-actions">
          <div class="post-action-btn <?= $likedPosts[$post['id']] ? 'liked' : '' ?>">
            <form method="post" action="/like.php">
              <input type="hidden" name="post_id" value="<?= $post['id'] ?>"/>
              <input type="hidden" name="redirect" value="/home.php#post-<?= $post['id'] ?>"/>
              <button type="submit">👍 <?= $likedPosts[$post['id']] ? 'Unlike' : 'Like' ?></button>
            </form>
          </div>
          <div class="post-action-btn">
            💬 Comment
          </div>
          <div class="post-action-btn">
            ↗ Share
          </div>
        </div>

        <!-- Comments -->
        <?php if (!empty($postComments[$post['id']])): ?>
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
            <input type="hidden" name="redirect" value="/home.php#post-<?= $post['id'] ?>"/>
            <input type="text" name="content" placeholder="Write a comment..."/>
            <button type="submit">Post</button>
          </form>
        </div>
        <?php else: ?>
        <div class="comments-section">
          <form class="comment-form" method="post" action="/comment.php">
            <a class="avatar avatar-sm" href="/profile.php?id=<?= $me['id'] ?>"><?= strtoupper($me['first_name'][0]) ?></a>
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>"/>
            <input type="hidden" name="redirect" value="/home.php#post-<?= $post['id'] ?>"/>
            <input type="text" name="content" placeholder="Write a comment..."/>
            <button type="submit">Post</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Right Sidebar -->
    <div class="home-sidebar-right">
      <div class="ticker-box">
        <div class="ticker-title">Ticker</div>
        <?php if (empty($posts)): ?>
          <div class="ticker-item">Nothing to show yet.</div>
        <?php else: foreach (array_slice($posts, 0, 5) as $p): ?>
          <div class="ticker-item"><strong><?= h($p['first_name']) ?></strong> posted something.</div>
        <?php endforeach; endif; ?>
      </div>

      <?php if (!empty($users)): ?>
      <div class="sidebar-box">
        <div class="sidebar-box-title">People You May Know</div>
        <div class="sidebar-box-body">
          <?php foreach (array_slice($users, 0, 5) as $u): ?>
          <div class="friend-item">
            <a class="avatar avatar-sm" href="/profile.php?id=<?= $u['id'] ?>"><?= strtoupper($u['first_name'][0]) ?></a>
            <a href="/profile.php?id=<?= $u['id'] ?>" style="font-size:12px;color:#333;"><?= h($u['first_name'] . ' ' . $u['last_name']) ?></a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="sidebar-box">
        <div class="sidebar-box-title">Chat (0)</div>
        <div class="sidebar-box-body" style="color:#999;font-size:11px;text-align:center;padding:16px;">
          Chat coming soon
        </div>
      </div>
    </div>

  </div>

  <footer class="page-footer">
    <div class="footer-links">
      <a href="#">Mobile</a><a href="#">Find Friends</a><a href="#">About</a>
      <a href="#">Advertise</a><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Help</a>
    </div>
    <div class="footer-copy">Facebook © 2013</div>
  </footer>
</div>

</body>
</html>
