<?php
require __DIR__ . '/auth.php';

if (auth_user()) {
    header('Location: /');
    exit;
}

$error = '';
$user = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim((string) ($_POST['user'] ?? ''));
    $pass = (string) ($_POST['pass'] ?? '');
    if (!auth_same_origin()) {
        $error = 'Please sign in from this page.';
    } elseif (!auth_users()) {
        $error = 'Sign-in is not set up yet: admin-config.php is missing on the server.';
    } elseif (auth_check($user, $pass)) {
        auth_login($user);
        header('Location: /');
        exit;
    } else {
        sleep(1); // slows down password guessing
        $error = 'That username and password don’t match. Try again.';
    }
}

$e = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in · Business card maker</title>
<link rel="icon" href="/favicon.ico">
<style>
  :root { --bg: #0d1830; --panel: #14223d; --field: #07101f; --line: #5a6f95; --ink: #eef2f8; --muted: #a3b2cc; --accent: #2c5cc5; --focus: #8fb0f5; }
  * { box-sizing: border-box; }
  body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1rem;
    background: var(--bg); color: var(--ink);
    background-image: linear-gradient(rgba(143,176,245,.06) 1px, transparent 1px), linear-gradient(90deg, rgba(143,176,245,.06) 1px, transparent 1px);
    background-size: 32px 32px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
  main { width: 100%; max-width: 24rem; background: var(--panel); border-radius: 1rem; padding: 2rem 1.5rem; }
  .logo { display: inline-block; background: #fff; border-radius: .5rem; padding: .5rem .75rem; }
  .logo img { display: block; height: 2.75rem; width: auto; }
  h1 { margin: 1.25rem 0 .25rem; font-size: 1.5rem; }
  p.sub { margin: 0 0 1.5rem; color: var(--muted); }
  label { display: block; margin: 1rem 0 .375rem; font-weight: 600; }
  input { width: 100%; height: 3rem; padding: 0 1rem; font: inherit; color: var(--ink);
    background: var(--field); border: 1px solid var(--line); border-radius: .5rem; }
  input:hover { border-color: var(--muted); }
  input:focus-visible, button:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; }
  button { width: 100%; height: 3.25rem; margin-top: 1.5rem; font: inherit; font-weight: 700; font-size: 1.0625rem;
    color: #fff; background: var(--accent); border: 0; border-radius: .5rem; cursor: pointer; }
  button:hover { background: #3d6fdb; }
  .error { margin: 1rem 0 0; padding: .75rem 1rem; border: 1px solid #dc2626; border-radius: .5rem; color: #fca5a5; }
</style>
</head>
<body>
<main>
  <span class="logo"><img src="/brand-logo.png" alt="Company logo"></span>
  <h1>Business card maker</h1>
  <p class="sub">Sign in to create and publish cards.</p>
  <form method="post" action="/login.php">
    <label for="user">Username</label>
    <input id="user" name="user" autocomplete="username" autocapitalize="none" spellcheck="false" required value="<?= $e($user) ?>" <?= $user === '' ? 'autofocus' : '' ?>>
    <label for="pass">Password</label>
    <input id="pass" name="pass" type="password" autocomplete="current-password" required <?= $user !== '' ? 'autofocus' : '' ?>>
    <?php if ($error): ?><p class="error" role="alert"><?= $e($error) ?></p><?php endif; ?>
    <button type="submit">Sign in</button>
  </form>
</main>
</body>
</html>
