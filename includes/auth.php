<?php
require_once __DIR__ . '/db.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function requireGuest(): void {
    if (isLoggedIn()) {
        header('Location: /home.php');
        exit;
    }
}

function loginUser(string $email, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        startSession();
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function registerUser(array $data): bool|string {
    $db = getDB();
    $email = strtolower(trim($data['email']));
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) return 'Email already registered.';
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (first_name, last_name, email, password_hash, birthday, gender) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        htmlspecialchars($data['first_name']),
        htmlspecialchars($data['last_name']),
        $email,
        $hash,
        $data['birthday'] ?? '',
        $data['gender'] ?? ''
    ]);
    startSession();
    $_SESSION['user_id'] = $db->lastInsertId();
    return true;
}

function logoutUser(): void {
    startSession();
    session_destroy();
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function timeAgo(string $datetime): string {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->getTimestamp() - $past->getTimestamp();
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return $past->format('F j, Y');
}
