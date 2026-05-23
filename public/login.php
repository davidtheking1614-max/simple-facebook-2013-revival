<?php
require_once __DIR__ . '/../includes/auth.php';
requireGuest();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (loginUser($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /home.php');
        exit;
    }
    $error = 'Incorrect email or password. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Facebook - Log In or Sign Up</title>
<link rel="stylesheet" href="/css/style.css"/>
</head>
<body>

<div id="blueBar">
  <div class="inner">
    <a class="fb-logo-text" href="/">facebook</a>
    <div class="nav-right">
      <a href="/register.php" style="background:linear-gradient(#5b77b0,#3b5998);color:#fff;border:1px solid #1a356e;padding:3px 10px;border-radius:3px;font-weight:bold;font-size:11px;text-decoration:none;">Sign Up</a>
    </div>
  </div>
</div>

<div class="page-wrapper">
  <div class="login-page-bg">
    <div>
      <div class="login-box">
        <div class="login-box-header">
          <h2>Log In to Facebook</h2>
        </div>
        <div class="login-box-body">
          <?php if ($error): ?>
            <div class="login-error"><?= h($error) ?></div>
          <?php endif; ?>
          <form method="post" action="/login.php">
            <div class="login-field">
              <label for="email">Email or Phone Number:</label>
              <input type="text" name="email" id="email" autocomplete="email" autofocus/>
            </div>
            <div class="login-field">
              <label for="pass">Password:</label>
              <input type="password" name="password" id="pass" autocomplete="current-password"/>
            </div>
            <div class="login-persist">
              <input type="checkbox" name="remember" id="persist_box"/>
              <label for="persist_box">Keep me logged in</label>
            </div>
            <button type="submit" class="btn-login">Log In</button>
            <div class="login-links">
              <a href="#">Forgot your password?</a>
              <a href="/register.php" style="color:#3b5998;font-weight:bold;">Register for Facebook</a>
            </div>
          </form>
        </div>
      </div>

      <div class="login-locale-list">
        <a href="#">English (US)</a><a href="#">Español</a><a href="#">Français (France)</a>
        <a href="#">中文(简体)</a><a href="#">العربية</a><a href="#">Português (Brasil)</a>
        <a href="#">Italiano</a><a href="#">한국어</a><a href="#">Deutsch</a><a href="#">हिन्दी</a>
        <a href="#">日本語</a>
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
