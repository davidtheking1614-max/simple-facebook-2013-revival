<?php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) {
    header('Location: /home.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (loginUser($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /home.php');
        exit;
    }
    $error = 'Incorrect email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Welcome to Facebook - Log In, Sign Up or Learn More</title>
<link rel="stylesheet" href="/css/style.css"/>
</head>
<body>

<div id="blueBar">
  <div class="inner">
    <a class="fb-logo-text" href="/">facebook</a>
    <form class="nav-login-form" method="post" action="/">
      <input type="hidden" name="action" value="login"/>
      <?php if ($error): ?>
        <span style="color:#ffcccc;font-size:11px;"><?= h($error) ?></span>
      <?php endif; ?>
      <div class="nav-field">
        <label for="n_email">Email or Phone</label>
        <input type="text" name="email" id="n_email" placeholder="Email or Phone" autocomplete="email"/>
      </div>
      <div class="nav-field">
        <label for="n_pass">Password</label>
        <input type="password" name="password" id="n_pass" placeholder="Password"/>
      </div>
      <button type="submit" class="nav-btn">Log In</button>
      <a class="nav-forgot" href="/login.php">Forgot your password?</a>
    </form>
    <div class="nav-right">
      <span>English (US)</span>
    </div>
  </div>
</div>

<div class="page-wrapper">
  <div class="landing-hero">
    <div class="inner">
      <div class="hero-left">
        <h1>Connect with friends and the<br/>world around you on Facebook.</h1>
        <div class="hero-feature">
          <div class="hero-feature-icon">📰</div>
          <div class="hero-feature-text">
            <span class="title">See photos and updates</span>
            <span class="desc">from friends in News Feed.</span>
          </div>
        </div>
        <div class="hero-feature">
          <div class="hero-feature-icon">🕐</div>
          <div class="hero-feature-text">
            <span class="title">Share what's new</span>
            <span class="desc">in your life on your Timeline.</span>
          </div>
        </div>
        <div class="hero-feature">
          <div class="hero-feature-icon">🔍</div>
          <div class="hero-feature-text">
            <span class="title">Find more</span>
            <span class="desc">of what you're looking for with Graph Search.</span>
          </div>
        </div>
      </div>

      <div class="hero-right">
        <div class="signup-box">
          <h2>Sign Up</h2>
          <p class="subtitle">It's free and always will be.</p>
          <?php if (!empty($_GET['reg_error'])): ?>
            <div class="alert alert-error"><?= h($_GET['reg_error']) ?></div>
          <?php endif; ?>
          <form method="post" action="/register.php">
            <div class="form-row-half">
              <input type="text" name="first_name" placeholder="First Name" required/>
              <input type="text" name="last_name" placeholder="Last Name" required/>
            </div>
            <input type="email" name="email" placeholder="Your Email" required/>
            <input type="email" name="email_confirm" placeholder="Re-enter Email" required/>
            <input type="password" name="password" placeholder="New Password" required/>
            <div class="birthday-label">Birthday:</div>
            <div class="birthday-selects">
              <select name="bmonth">
                <option value="">Month:</option>
                <?php $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                foreach ($months as $i => $m) echo "<option value='".($i+1)."'>$m</option>"; ?>
              </select>
              <select name="bday">
                <option value="">Day:</option>
                <?php for ($d=1;$d<=31;$d++) echo "<option value='$d'>$d</option>"; ?>
              </select>
              <select name="byear">
                <option value="">Year:</option>
                <?php for ($y=date('Y');$y>=1900;$y--) echo "<option value='$y'>$y</option>"; ?>
              </select>
            </div>
            <div class="gender-row">
              <label><input type="radio" name="gender" value="Female"/> Female</label>
              <label><input type="radio" name="gender" value="Male"/> Male</label>
              <label><input type="radio" name="gender" value="Other"/> Other</label>
            </div>
            <button type="submit" class="btn-signup">Sign Up</button>
            <p class="signup-terms">By clicking Sign Up, you agree to our <a href="#">Terms</a>, <a href="#">Data Policy</a> and <a href="#">Cookie Policy</a>.</p>
          </form>
        </div>
      </div>
    </div>
  </div>

  <footer class="page-footer">
    <div class="footer-links">
      <a href="#">Mobile</a><a href="#">Find Friends</a><a href="#">Badges</a><a href="#">People</a>
      <a href="#">Pages</a><a href="#">Places</a><a href="#">Apps</a><a href="#">Games</a>
      <a href="#">About</a><a href="#">Advertise</a><a href="#">Create a Page</a>
      <a href="#">Privacy</a><a href="#">Terms</a><a href="#">Help</a>
    </div>
    <div class="footer-copy">Facebook © 2013 · <a href="#">English (US)</a></div>
  </footer>
</div>

</body>
</html>
