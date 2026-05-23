<?php
require_once __DIR__ . '/../includes/auth.php';
requireGuest();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fn = trim($_POST['first_name'] ?? '');
    $ln = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $emailc = trim($_POST['email_confirm'] ?? '');
    $pass = $_POST['password'] ?? '';
    $bmonth = $_POST['bmonth'] ?? '';
    $bday = $_POST['bday'] ?? '';
    $byear = $_POST['byear'] ?? '';
    $gender = $_POST['gender'] ?? '';
    if (!$fn || !$ln) $error = 'Please enter your name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email.';
    elseif ($email !== $emailc) $error = 'Your emails do not match.';
    elseif (strlen($pass) < 6) $error = 'Your password must be at least 6 characters.';
    elseif (!$gender) $error = 'Please select your gender.';
    else {
        $birthday = $byear && $bmonth && $bday ? "$byear-$bmonth-$bday" : '';
        $result = registerUser([
            'first_name' => $fn,
            'last_name' => $ln,
            'email' => $email,
            'password' => $pass,
            'birthday' => $birthday,
            'gender' => $gender
        ]);
        if ($result === true) {
            header('Location: /home.php');
            exit;
        }
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Sign Up for Facebook | Facebook</title>
<link rel="stylesheet" href="/css/style.css"/>
</head>
<body>

<div id="blueBar">
  <div class="inner">
    <a class="fb-logo-text" href="/">facebook</a>
    <form class="nav-login-form" method="post" action="/login.php">
      <div class="nav-field">
        <label>Email or Phone</label>
        <input type="text" name="email" placeholder="Email or Phone"/>
      </div>
      <div class="nav-field">
        <label>Password</label>
        <input type="password" name="password" placeholder="Password"/>
      </div>
      <button type="submit" class="nav-btn">Log In</button>
    </form>
    <div class="nav-right">
      <span>English (US)</span>
    </div>
  </div>
</div>

<div class="page-wrapper">
  <div class="register-page">
    <div class="register-box">
      <h2>Sign Up</h2>
      <p class="subtitle">It's free and always will be.</p>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
      <?php endif; ?>
      <form method="post" action="/register.php">
        <div class="form-row-half" style="margin-bottom:8px;">
          <input type="text" name="first_name" placeholder="First Name" value="<?= h($_POST['first_name'] ?? '') ?>" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:15px;"/>
          <input type="text" name="last_name" placeholder="Last Name" value="<?= h($_POST['last_name'] ?? '') ?>" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:15px;"/>
        </div>
        <div style="margin-bottom:8px;">
          <input type="email" name="email" placeholder="Your Email" value="<?= h($_POST['email'] ?? '') ?>" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:15px;"/>
        </div>
        <div style="margin-bottom:8px;">
          <input type="email" name="email_confirm" placeholder="Re-enter Email" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:15px;"/>
        </div>
        <div style="margin-bottom:8px;">
          <input type="password" name="password" placeholder="New Password" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:3px;font-size:15px;"/>
        </div>
        <div class="birthday-label">Birthday:</div>
        <div class="birthday-selects" style="margin-bottom:10px;">
          <select name="bmonth">
            <option value="">Month:</option>
            <?php $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            foreach ($months as $i => $m) {
                $sel = (isset($_POST['bmonth']) && $_POST['bmonth'] == ($i+1)) ? 'selected' : '';
                echo "<option value='".($i+1)."' $sel>$m</option>";
            } ?>
          </select>
          <select name="bday">
            <option value="">Day:</option>
            <?php for ($d=1;$d<=31;$d++) {
                $sel = (isset($_POST['bday']) && $_POST['bday'] == $d) ? 'selected' : '';
                echo "<option value='$d' $sel>$d</option>";
            } ?>
          </select>
          <select name="byear">
            <option value="">Year:</option>
            <?php for ($y=date('Y');$y>=1900;$y--) {
                $sel = (isset($_POST['byear']) && $_POST['byear'] == $y) ? 'selected' : '';
                echo "<option value='$y' $sel>$y</option>";
            } ?>
          </select>
        </div>
        <div class="gender-row" style="margin-bottom:12px;">
          <label>
            <input type="radio" name="gender" value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'checked' : '' ?>/> Female
          </label>
          <label>
            <input type="radio" name="gender" value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'checked' : '' ?>/> Male
          </label>
          <label>
            <input type="radio" name="gender" value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'checked' : '' ?>/> Other
          </label>
        </div>
        <button type="submit" class="btn-signup">Sign Up</button>
        <p class="signup-terms" style="margin-top:8px;">By clicking Sign Up, you agree to our <a href="#">Terms</a>, <a href="#">Data Policy</a> and <a href="#">Cookie Policy</a>. You may receive SMS notifications from us and can opt out at any time.</p>
      </form>
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #eee;text-align:center;font-size:13px;">
        Already have an account? <a href="/login.php">Log in</a>
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
