<?php
// config.php — подключение к БД + хелперы
session_start();

$DB_HOST = 'localhost';
$DB_NAME = 'z97765l3_medfarm';
$DB_USER = 'z97765l3_medfarm';
$DB_PASS = '123456Kar';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('Ошибка подключения к БД: ' . htmlspecialchars($e->getMessage()));
}

function esc($s) {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function currentUser()     { return $_SESSION['user_id']   ?? null; }
function currentUserName() { return $_SESSION['user_name'] ?? null; }

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function isAdmin() {
    if (empty($_SESSION['user_id'])) return false;
    global $pdo;
    static $cache = null;
    if ($cache === null) {
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cache = (int)$stmt->fetchColumn() === 1;
    }
    return $cache;
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('../login.php?back=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Найти картинку товара.
 * Возвращает путь относительно корня проекта (без ../).
 * $p — строка из products, должен содержать id и image.
 */
function productImage($p) {
    if (!empty($p['image']) && file_exists(__DIR__ . '/' . $p['image'])) {
        return $p['image'];
    }
    foreach (['.webp', '.jpg', '.png'] as $ext) {
        if (file_exists(__DIR__ . '/img/' . (int)$p['id'] . $ext)) {
            return 'img/' . (int)$p['id'] . $ext;
        }
    }
    return 'img/no-image.png';
}

/**
 * Найти картинку акции.
 * $pr — строка из promotions.
 */
function promoImage($pr) {
    if (!empty($pr['image']) && file_exists(__DIR__ . '/' . $pr['image'])) {
        return $pr['image'];
    }
    foreach (['.webp', '.jpg', '.png'] as $ext) {
        if (file_exists(__DIR__ . '/img/promo_' . (int)$pr['id'] . $ext)) {
            return 'img/promo_' . (int)$pr['id'] . $ext;
        }
    }
    return 'img/no-image.png';
}

/**
 * Активные акции по типу: 'day' | 'week' | 'month' | null (все).
 */
function getActivePromotions($type = null) {
    global $pdo;
    $sql = "
        SELECT pr.*,
               p.name  AS product_name,
               p.price AS product_price,
               p.category AS product_category
        FROM promotions pr
        JOIN products p ON p.id = pr.product_id
        WHERE pr.date_start <= CURDATE() AND pr.date_end >= CURDATE()
    ";
    $params = [];
    if ($type) {
        $sql .= " AND pr.type = ?";
        $params[] = $type;
    }
    $sql .= " ORDER BY pr.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ЛАБОРАТОРНАЯ РАБОТА №7 — SEO-поля, настройки, API

/**
 * Получить одну настройку сайта (из таблицы settings).
 */
function getSetting(string $key, string $default = ''): string {
    global $pdo;
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];

    try {
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $cache[$key] = $row ? (string)$row['value'] : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

/**
 * Получить все настройки разом (ассоциативный массив).
 */
function getSettings(): array {
    global $pdo;
    try {
        $rows = $pdo->query("SELECT `key`, `value` FROM settings")->fetchAll();
        $out = [];
        foreach ($rows as $r) $out[$r['key']] = $r['value'];
        return $out;
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Товары для API каталога — с рейтингом и активной акцией.
 * Использует существующие таблицы products, reviews, promotions.
 */
function getCatalogProducts(int $limit = 20, int $offset = 0): array {
    global $pdo;
    $sql = "
        SELECT p.id, p.name, p.description, p.price,
               p.image, p.image_alt, p.image_webp,
               p.image_width, p.image_height, p.image_title,
               ROUND(AVG(r.rating), 1) AS avg_rating,
               COUNT(r.id) AS reviews_count,
               pr.type AS promo_type,
               pr.discount AS promo_discount
        FROM products p
        LEFT JOIN reviews r ON r.product_id = p.id
        LEFT JOIN promotions pr ON pr.product_id = p.id
            AND pr.date_start <= CURDATE() AND pr.date_end >= CURDATE()
        GROUP BY p.id
        ORDER BY p.id DESC
        LIMIT :lim OFFSET :off
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}