<?php
// Shared sign-in helpers for the card maker. Included by app.php, login.php,
// logout.php, publish.php and team/index.php. Outputs nothing on its own.
// Accounts live in admin-config.php next to this file (see admin-config.example.php).

function auth_start()
{
    if (session_status() !== PHP_SESSION_NONE) return;
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('cardadmin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function auth_user()
{
    auth_start();
    return $_SESSION['user'] ?? null;
}

// Sends signed-out visitors to the sign-in page, then releases the session lock.
function auth_require_page()
{
    if (!auth_user()) {
        header('Location: /login.php');
        exit;
    }
    session_write_close();
}

function auth_users()
{
    $file = __DIR__ . '/admin-config.php';
    if (!is_file($file)) return [];
    $config = require $file;
    return is_array($config['users'] ?? null) ? $config['users'] : [];
}

// Passwords may be stored plain or as a password_hash() value ($2y$...).
function auth_check($user, $pass)
{
    $users = auth_users();
    $stored = (string) ($users[$user] ?? '');
    if ($stored === '' || $pass === '') return false;
    return strpos($stored, '$2y$') === 0 ? password_verify($pass, $stored) : hash_equals($stored, $pass);
}

function auth_login($user)
{
    auth_start();
    session_regenerate_id(true);
    $_SESSION['user'] = $user;
}

function auth_logout()
{
    auth_start();
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    session_destroy();
}

// Requests that change things must come from this site's own pages.
function auth_same_origin()
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin === '') return true; // older browsers omit it on same-origin POSTs; session + SameSite still apply
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return $origin === "https://$host" || $origin === "http://$host";
}
