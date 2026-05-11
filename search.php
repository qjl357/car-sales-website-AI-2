<?php
/**
 * search.php — 搜索（表单 POST + PDO 查库 + HTML）
 *
 * POST：q, brand, year_from, year_to, province（英文省名，匹配 location 列 LIKE 前缀）, color, price_min, price_max, search_action
 * 表结构由 db_config.php → listings 配置（与 car_sales 车源表列一致：model/year/color/location/price 等）。
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sqlIdentifier(string $name, string $label): string
{
    if ($name === '' || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        throw new InvalidArgumentException('Illegal ' . $label . ': ' . $name);
    }
    return $name;
}

function likeWrap(string $q): string
{
    $q = mb_substr(trim($q), 0, 200, 'UTF-8');
    $q = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
    return '%' . $q . '%';
}

function postString(string $key): string
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
}

function postIntOrNull(string $key): ?int
{
    $v = postString($key);
    if ($v === '') {
        return null;
    }
    if (!preg_match('/^-?\d+$/', $v)) {
        return null;
    }
    return (int) $v;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: search.html', true, 302);
    exit;
}

$configPath = __DIR__ . DIRECTORY_SEPARATOR . 'db_config.php';
if (!is_readable($configPath)) {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="zh-Hans"><head><meta charset="UTF-8"><title>Not configured</title></head><body>';
    echo '<p>Missing <code>db_config.php</code>。</p><p><a href="search.html">Back to Search</a></p></body></html>';
    exit;
}

/** @var array<string,mixed> $cfg */
$cfg = require $configPath;
if (!is_array($cfg) || !isset($cfg['pdo'], $cfg['listings']) || !is_array($cfg['listings'])) {
    http_response_code(500);
    echo '<p>db_config.php 须包含 pdo 与 listings。</p><p><a href="search.html">返回</a></p>';
    exit;
}

$listCfg = $cfg['listings'];
$table = sqlIdentifier((string) ($listCfg['table'] ?? ''), 'listings.table');
$colsIn = $listCfg['columns'] ?? null;
if (!is_array($colsIn)) {
    http_response_code(500);
    echo '<p>listings.columns  Must be an array</p><p><a href="search.html">返回</a></p>';
    exit;
}

/** @return array<string,string> logical => physical SQL identifier */
function resolveListingColumns(array $colsIn): array
{
    $required = ['id', 'model', 'year', 'color', 'price'];
    $out = [];
    foreach ($required as $k) {
        if (!isset($colsIn[$k]) || (string) $colsIn[$k] === '') {
            throw new InvalidArgumentException('listings.columns Missing or empty:' . $k);
        }
        $out[$k] = sqlIdentifier((string) $colsIn[$k], 'column.' . $k);
    }
    $optional = ['location', 'image_path', 'created_at', 'mileage_km'];
    foreach ($optional as $k) {
        if (!isset($colsIn[$k])) {
            continue;
        }
        $v = (string) $colsIn[$k];
        if ($v !== '') {
            $out[$k] = sqlIdentifier($v, 'column.' . $k);
        }
    }
    return $out;
}

try {
    $cols = resolveListingColumns($colsIn);
} catch (InvalidArgumentException $e) {
    http_response_code(500);
    echo '<p>' . h($e->getMessage()) . '</p><p><a href="search.html">返回</a></p>';
    exit;
}

$q = postString('q');
$brand = postString('brand');
$yearFrom = postIntOrNull('year_from');
$yearTo = postIntOrNull('year_to');
$province = postString('province');
$color = postString('color');
$priceMin = postIntOrNull('price_min');
$priceMax = postIntOrNull('price_max');

if ($yearFrom !== null && $yearTo !== null && $yearFrom > $yearTo) {
    $t = $yearFrom;
    $yearFrom = $yearTo;
    $yearTo = $t;
}
if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
    $t = $priceMin;
    $priceMin = $priceMax;
    $priceMax = $t;
}

$pdoCfg = $cfg['pdo'];
try {
    $pdo = new PDO(
        (string) $pdoCfg['dsn'],
        (string) $pdoCfg['user'],
        (string) $pdoCfg['pass'],
        is_array($pdoCfg['options'] ?? null) ? $pdoCfg['options'] : []
    );
} catch (PDOException $e) {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="zh-Hans"><head><meta charset="UTF-8"><title>连接失败</title></head><body>';
    echo '<p>数据库连接失败。</p><p><a href="search.html">返回搜索</a></p></body></html>';
    exit;
}

$selectParts = [];
foreach ($cols as $logical => $sqlCol) {
    $selectParts[] = '`' . $sqlCol . '` AS `' . $logical . '`';
}
$sql = 'SELECT ' . implode(', ', $selectParts) . ' FROM `' . $table . '` WHERE 1=1';
$params = [];

$modelCol = $cols['model'];
$colorCol = $cols['color'];

if ($q !== '') {
    $like = likeWrap($q);
    $sql .= " AND (`{$modelCol}` LIKE ? OR `{$colorCol}` LIKE ?";
    $params[] = $like;
    $params[] = $like;
    if (isset($cols['location'])) {
        $loc = $cols['location'];
        $sql .= " OR `{$loc}` LIKE ?";
        $params[] = $like;
    }
    $sql .= ')';
}

if ($brand !== '') {
    $sql .= " AND LOWER(`{$modelCol}`) = LOWER(?)";
    $params[] = $brand;
}
if ($yearFrom !== null) {
    $sql .= ' AND `' . $cols['year'] . '` >= ?';
    $params[] = $yearFrom;
}
if ($yearTo !== null) {
    $sql .= ' AND `' . $cols['year'] . '` <= ?';
    $params[] = $yearTo;
}
if ($province !== '' && isset($cols['location'])) {
    $locCol = $cols['location'];
    $pEsc = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($province, 0, 32, 'UTF-8'));
    $sql .= ' AND `' . $locCol . '` LIKE ?';
    $params[] = $pEsc . '%';
}
if ($color !== '') {
    $sql .= ' AND `' . $colorCol . '` = ?';
    $params[] = $color;
}
if ($priceMin !== null) {
    $sql .= ' AND CAST(`' . $cols['price'] . '` AS DECIMAL(14,2)) >= ?';
    $params[] = $priceMin;
}
if ($priceMax !== null) {
    $sql .= ' AND CAST(`' . $cols['price'] . '` AS DECIMAL(14,2)) <= ?';
    $params[] = $priceMax;
}

$sql .= ' ORDER BY `' . $cols['id'] . '` ASC';

$rows = [];
$errorMsg = '';
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMsg = 'Query failed: Please ensure that listings.table and listings.columns match the database (the vehicle source table name should be vehicles, or modify db_config.php).';
}

$n = count($rows);
$hasImage = isset($cols['image_path']);
$hasLocation = isset($cols['location']);
?>
<!DOCTYPE html>
<html lang="zh-Hans">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Search results · <?php echo h((string) $n); ?> </title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="pages.css">
  <style>
    .search-php-results h1 { font-size: 1.35rem; margin: 0 0 8px 0; }
    .search-php-results .meta { color: var(--text-secondary); font-size: 14px; margin-bottom: 12px; }
    .search-php-results .search-results-wrap { overflow-x: auto; margin-top: 8px; }
    .search-php-results table { border-collapse: collapse; width: 100%; max-width: 1100px; }
    .search-php-results th,
    .search-php-results td {
      border: 1px solid var(--border);
      padding: 8px 10px;
      text-align: left;
      vertical-align: middle;
    }
    .search-php-results th { background: var(--tag-bg); color: var(--text-secondary); font-weight: 600; }
    .search-php-results tr:nth-child(even) td { background: rgba(255,255,255,0.02); }
    .search-php-results .err { color: var(--danger); margin: 12px 0; }
    .search-php-results .num { text-align: right; }
    .search-php-results .thumb { max-width: 72px; max-height: 54px; object-fit: cover; border-radius: 4px; }
    .search-php-results code { background: var(--tag-bg); padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
  </style>
</head>
<body class="search-page">
  <main class="page-shell lt-shell search-php-results">
    <header class="lt-topbar">
      <a class="lt-back" href="search.html" aria-label="返回搜索">‹</a>
      <div class="lt-brand-block">
        <p class="lt-brand">Search Operations</p>
        <h1>Search results</h1>
      </div>
      <nav class="lt-nav">
        <a class="lt-nav-link" href="home.html">Home</a>
        <a class="lt-nav-link" href="publish.php">Publish</a>
        <a class="lt-nav-link" href="login.html">Login</a>
      </nav>
    </header>

    <div class="auth-wrap" style="margin-top: 8px; max-width: 1100px;">
      <div class="card">
        <p class="meta"> <strong><?php echo h((string) $n); ?></strong> in total ·  <code><?php echo h($table); ?></code></p>
        <p class="meta"><a href="search.html">Return to search page</a></p>

        <?php if ($errorMsg !== '') : ?>
          <p class="err"><?php echo h($errorMsg); ?></p>
        <?php elseif ($n === 0) : ?>
          <p>No qualified vehicle sources found.</p>
        <?php else : ?>
          <div class="search-results-wrap">
            <table>
              <thead>
                <tr>
                  <?php if ($hasImage) : ?><th>Image</th><?php endif; ?>
                  <th>Model</th>
                  <th>Year</th>
                  <th>Color</th>
                  <?php if ($hasLocation) : ?><th>Location</th><?php endif; ?>
                  <th>Price</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r) : ?>
                <tr>
                  <?php if ($hasImage) :
                      $img = (string) ($r['image_path'] ?? '');
                      ?>
                  <td><?php if ($img !== '') : ?><img class="thumb" src="<?php echo h($img); ?>" alt=""><?php endif; ?></td>
                  <?php endif; ?>
                  <td><?php echo h((string) ($r['model'] ?? '')); ?></td>
                  <td class="num"><?php echo h((string) ($r['year'] ?? '')); ?></td>
                  <td><?php echo h((string) ($r['color'] ?? '')); ?></td>
                  <?php if ($hasLocation) : ?>
                  <td><?php echo h((string) ($r['location'] ?? '')); ?></td>
                  <?php endif; ?>
                  <td class="num"><?php echo h((string) ($r['price'] ?? '')); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
