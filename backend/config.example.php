<?php
declare(strict_types=1);

/*
 * Copy this file to config.php OUTSIDE public_html.
 * Never commit real credentials or secrets to Git.
 */
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'YOUR_CPANEL_DATABASE_NAME',
        'username' => 'YOUR_DATABASE_USER',
        'password' => 'REPLACE_WITH_DATABASE_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    // Generate with: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
    'app_secret' => 'REPLACE_WITH_A_RANDOM_SECRET_AT_LEAST_64_HEX_CHARACTERS',
    'session' => [
        'name' => 'WISESESSID',
        'idle_timeout' => 1800,
        'absolute_timeout' => 28800,
    ],
];
