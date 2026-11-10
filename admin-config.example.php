<?php
// Copy to admin-config.php on the server, next to login.php (not part of the build,
// so uploading a new build never overwrites it). PHP files are executed, not
// downloaded, so visitors can't read these passwords.
//
// One line per account: 'username' => 'password'. Add as many as you need.
// Use long passwords. To avoid storing them in plain text, you can put a
// password_hash() value ("$2y$...") instead of the password.
return [
    'users' => [
        'admin' => 'change-this-to-a-long-password',
    ],
];
