<?php
declare(strict_types=1);

$wiseAppPath = getenv('WISE_APP_PATH') ?: dirname(__DIR__, 3);
require_once rtrim($wiseAppPath, DIRECTORY_SEPARATOR) . '/app/bootstrap.php';

try {
    wise_require_super_admin();
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        wise_json_response(405, ['ok' => false, 'error' => 'Method not allowed.']);
    }
    if (empty($_SESSION['wise_csrf'])) {
        $_SESSION['wise_csrf'] = bin2hex(random_bytes(32));
    }
    wise_json_response(200, ['ok' => true, 'csrf_token' => $_SESSION['wise_csrf']]);
} catch (Throwable $error) {
    error_log('WISE CSRF endpoint failure: ' . $error->getMessage());
    wise_json_response(500, ['ok' => false, 'error' => 'The request could not be completed.']);
}
