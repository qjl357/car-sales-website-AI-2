<?php
/**
 * login.php — Login (Form POST, DB Check + password_verify + Session)
 *
 * Frontend: login.html → method="post" action="login.php"
 * POST: username, password
 *
 * Teammate in charge of database: Copy db_config.example.php to db_config.php,
 * fill in PDO and table fields.
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Allow only letters, numbers, underscores to prevent SQL injection via config tampering */
function sqlIdentifier(string $name, string $label): string
{
    if ($name === '' || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        throw new InvalidArgumentException('Invalid ' . $label);
    }
    return $name;
}

function renderError(string $title, string $message, int $code = 500): void
{
    http_response_code($code);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    // 引入你的全局样式
    echo '<link rel="stylesheet" href="style.css">';
    echo '<link rel="stylesheet" href="pages.css">';
    echo '<title>' . h($title) . '</title>';
    echo '<style>body{font-family:system-ui,sans-serif;margin:24px;line-height:1.6}a{color:#0645ad}</style></head><body>';
    echo '<h1>' . h($title) . '</h1><p>' . $message . '</p><p><a href="login.html">Back to Login</a></p></body></html>';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: login.html', true, 302);
    exit;
}

$username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
$password = isset($_POST['password']) ? (string) $_POST['password'] : '';

if ($username === '' || $password === '') {
    renderError('Login Failed', 'Please enter username and password.', 400);
    exit;
}

$configPath = __DIR__ . DIRECTORY_SEPARATOR . 'db_config.php';
if (!is_readable($configPath)) {
    header('Content-Type: text/html; charset=UTF-8');
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    // 引入你的全局样式
    echo '<link rel="stylesheet" href="style.css">';
    echo '<link rel="stylesheet" href="pages.css">';
    echo '<title>Database Not Configured</title>';
    echo '<style>body{font-family:system-ui,sans-serif;margin:24px;line-height:1.6}a{color:#0645ad}code{background:#f4f4f4;padding:2px 6px}</style></head><body>';
    echo '<h1>Database Not Configured</h1><p>Please copy <code>db_config.example.php</code> to <code>db_config.php</code> in this directory, and fill in PDO connection, table and column names.</p>';
    echo '<p><a href="login.html">Back to Login</a></p></body></html>';
    exit;
}

/** @var array<string,mixed> $db */
$db = require $configPath;
if (!is_array($db) || !isset($db['pdo']) || !is_array($db['pdo'])) {
    renderError('Configuration Error', 'db_config.php must return an array containing the <code>pdo</code> key.');
    exit;
}

$pdoCfg = $db['pdo'];
$usersTable = sqlIdentifier((string) ($db['users_table'] ?? 'users'), 'users_table');
$userCol = sqlIdentifier((string) ($db['username_column'] ?? 'username'), 'username_column');
$passCol = sqlIdentifier((string) ($db['password_column'] ?? 'password'), 'password_column');
try {
    $pdo = new PDO(
        (string) $pdoCfg['dsn'],
        (string) $pdoCfg['user'],
        (string) $pdoCfg['pass'],
        is_array($pdoCfg['options'] ?? null) ? $pdoCfg['options'] : []
    );
} catch (PDOException $e) {
    renderError('Database Connection Failed', 'Please check host, database name, username and password in db_config.php. (Details are logged on the server; sensitive information is not shown here.)');
    exit;
}

$selectCols = ['`' . $userCol . '`', '`' . $passCol . '`'];
$sessionCols = $db['users_session_columns'] ?? [];
if (!is_array($sessionCols)) {
    $sessionCols = [];
}
foreach ($sessionCols as $extra) {
    $ex = (string) $extra;
    if ($ex === '') {
        continue;
    }
    $selectCols[] = '`' . sqlIdentifier($ex, 'users_session_columns') . '`';
}
$sql = sprintf(
    'SELECT %s FROM `%s` WHERE `%s` = ? LIMIT 1',
    implode(', ', $selectCols),
    $usersTable,
    $userCol
);

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $row = $stmt->fetch();
} catch (PDOException $e) {
    renderError('Query Failed', 'Please verify table and column names match db_config.php and actual schema.');
    exit;
}

if ($row === false) {
    renderError('Login Failed', 'Invalid username or password.', 401);
    exit;
}

$hash = (string) ($row[$passCol] ?? '');
if ($hash === '' || !password_verify($password, $hash)) {
    renderError('Login Failed', 'Invalid username or password.', 401);
    exit;
}

session_regenerate_id(true);
$_SESSION['logged_in'] = true;
$_SESSION['username'] = (string) ($row[$userCol] ?? $username);
foreach ($sessionCols as $extra) {
    $ex = (string) $extra;
    if ($ex === '' || !preg_match('/^[A-Za-z0-9_]+$/', $ex)) {
        continue;
    }
    $_SESSION[$ex] = (string) ($row[$ex] ?? '');
}

header('Location: home.html?welcome=1', true, 302);
exit;