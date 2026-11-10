<?php
// Serves the card maker (the built index.html) to signed-in users only.
// .htaccess routes "/", /index.html and /404.html here.
require __DIR__ . '/auth.php';
auth_require_page();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
readfile(__DIR__ . '/index.html');
