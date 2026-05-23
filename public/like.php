<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

$postId = (int)($_POST['post_id'] ?? 0);
$redirect = $_POST['redirect'] ?? '/home.php';

if ($postId) {
    $check = $db->prepare('SELECT id FROM likes WHERE post_id = ? AND user_id = ?');
    $check->execute([$postId, $me['id']]);
    if ($check->fetch()) {
        $db->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?')->execute([$postId, $me['id']]);
    } else {
        $db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)')->execute([$postId, $me['id']]);
    }
}

header('Location: ' . $redirect);
exit;
