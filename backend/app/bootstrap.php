<?php
declare(strict_types=1);

function wise_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }
    $path = getenv('WISE_CONFIG_PATH');
    if (!$path || !is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Private WISE configuration is not available.');
    }
    $loaded = require $path;
    if (!is_array($loaded) || empty($loaded['db']) || empty($loaded['app_secret'])) {
        throw new RuntimeException('Private WISE configuration is incomplete.');
    }
    $config = $loaded;
    return $config;
}

function wise_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $db = wise_config()['db'];
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['name'],
        $db['charset'] ?? 'utf8mb4'
    );
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function wise_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $settings = wise_config()['session'] ?? [];
    session_name($settings['name'] ?? 'WISESESSID');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    $now = time();
    $idle = (int)($settings['idle_timeout'] ?? 1800);
    $absolute = (int)($settings['absolute_timeout'] ?? 28800);
    if (isset($_SESSION['wise_last_activity']) && $now - (int)$_SESSION['wise_last_activity'] > $idle) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    if (isset($_SESSION['wise_created_at']) && $now - (int)$_SESSION['wise_created_at'] > $absolute) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['wise_created_at'] ??= $now;
    $_SESSION['wise_last_activity'] = $now;
}

function wise_json_response(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function wise_require_super_admin(): int
{
    wise_start_session();
    $userId = filter_var($_SESSION['wise_user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId || empty($_SESSION['wise_authenticated']) || ($_SESSION['wise_role'] ?? '') !== 'super_admin') {
        wise_json_response(403, ['ok' => false, 'error' => 'Authorised Super Administrator access is required.']);
    }
    return (int)$userId;
}

function wise_require_csrf(): void
{
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $known = $_SESSION['wise_csrf'] ?? '';
    if (!is_string($provided) || !is_string($known) || $known === '' || !hash_equals($known, $provided)) {
        wise_json_response(419, ['ok' => false, 'error' => 'Your session security check failed. Refresh and try again.']);
    }
}

function wise_audit(PDO $pdo, ?int $actorId, string $event, ?string $targetType = null, ?int $targetId = null, array $details = []): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (actor_user_id,event_type,target_type,target_id,details_json,ip_address)
         VALUES (:actor,:event,:type,:target,:details,:ip)'
    );
    $stmt->execute([
        ':actor' => $actorId,
        ':event' => $event,
        ':type' => $targetType,
        ':target' => $targetId,
        ':details' => $details ? json_encode($details, JSON_THROW_ON_ERROR) : null,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
