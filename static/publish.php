<?php
// Receives a card zip from the editor and unpacks it to ./<slug>/ next to this file.
header('Content-Type: text/plain; charset=utf-8');

function fail($code, $msg, $tmp = null)
{
    if ($tmp) rrmdir($tmp);
    http_response_code($code);
    exit($msg);
}

function rrmdir($dir)
{
    if (!is_dir($dir)) return;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $f) $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    rmdir($dir);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'POST only.');

// Only signed-in admins may publish (see login.php / admin-config.php).
require __DIR__ . '/auth.php';
if (!auth_user()) fail(401, 'You are signed out. Sign in again, then publish.');
session_write_close(); // don't hold the session lock during the upload
if (!auth_same_origin()) fail(403, 'Publishing is only allowed from the card maker.');

$slug = (string) ($_POST['slug'] ?? '');
if (!preg_match('/^[a-z0-9_]{1,64}$/', $slug)) fail(400, 'Invalid card name. Use letters and numbers in your name.');

$target = __DIR__ . '/' . $slug;
// Only overwrite folders this script created, never the app's own files.
if (file_exists($target) && !is_file("$target/.card")) fail(409, "The name \"$slug\" is reserved. Ask an admin.");

$upload = $_FILES['card'] ?? null;
if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) fail(400, 'Upload failed. The card may be larger than the server allows.');

if (!class_exists('ZipArchive')) fail(500, 'The PHP zip extension is not enabled on the server.');
$zip = new ZipArchive();
if ($zip->open($upload['tmp_name']) !== true) fail(400, 'The uploaded card is not a valid zip.');

// Only the file types the card generator produces, at the top level or in media/.
$allowed = '~^(media/)?[^/\\\\:.][^/\\\\:]*\.(html|css|js|json|vcf|asc|png|jpe?g|gif|webp|svg|mp3|mp4|webm|pdf)$~i';
$maxBytes = 100 * 1024 * 1024;
$total = 0;
$hasIndex = false;

$tmp = __DIR__ . "/.tmp-$slug-" . bin2hex(random_bytes(4));
mkdir("$tmp/media", 0755, true);

for ($i = 0; $i < $zip->numFiles; $i++) {
    $stat = $zip->statIndex($i);
    $name = $stat['name'];
    if (substr($name, -1) === '/') continue; // folder entry
    if (!preg_match($allowed, $name) || strpos($name, '..') !== false) {
        fail(400, "File not allowed in a card: $name", $tmp);
    }
    $total += $stat['size'];
    if ($total > $maxBytes) fail(413, 'Card is larger than 100 MB.', $tmp);
    if (file_put_contents("$tmp/$name", $zip->getFromIndex($i)) === false) {
        fail(500, "Could not write $name on the server.", $tmp);
    }
    if ($name === 'index.html') $hasIndex = true;
}
$zip->close();

if (!$hasIndex) fail(400, 'Card has no index.html.', $tmp);
touch("$tmp/.card");

// Swap the new card in, then delete the old one.
$old = null;
if (is_dir($target)) {
    $old = __DIR__ . "/.old-$slug-" . bin2hex(random_bytes(4));
    rename($target, $old);
}
if (!rename($tmp, $target)) {
    if ($old) rename($old, $target);
    fail(500, 'Could not publish the card folder.', $tmp);
}
if ($old) rrmdir($old);

echo "Published /$slug/";
