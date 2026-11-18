<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

try {
    $limit  = isset($_GET['limit'])  ? max(1, min(100, (int)$_GET['limit']))  : 20;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset'])           : 0;

    $items = getCatalogProducts($limit, $offset);

    foreach ($items as &$it) {
        $it['image_url']   = $it['image_webp'] ?: $it['image'] ?: 'img/no-image.png';
        $it['alt_text']    = $it['image_alt']  ?: ('Изображение товара: ' . $it['name']);
        $it['image_title'] = $it['image_title'] ?: $it['name'];
        $it['width']       = (int)($it['image_width'])  ?: 400;
        $it['height']      = (int)($it['image_height']) ?: 300;
    }
    unset($it);

    echo json_encode(['ok' => true, 'count' => count($items), 'items' => $items], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}