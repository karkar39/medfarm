<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

function out($arr) { echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

// 1. Авторизация
if (empty($_SESSION['user_id'])) {
    out(['ok' => false, 'error' => 'Войдите, чтобы оставить отзыв.']);
}
$userId = (int)$_SESSION['user_id'];

// 2. Входные данные
$productId = (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$text      = trim($_POST['text'] ?? '');

if ($productId < 1)             out(['ok' => false, 'error' => 'Неверный товар.']);
if ($rating < 1 || $rating > 5) out(['ok' => false, 'error' => 'Оценка от 1 до 5.']);
if (mb_strlen($text) < 3)       out(['ok' => false, 'error' => 'Текст — минимум 3 символа.']);

// 3. Проверка факта покупки
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled'
");
$stmt->execute([$userId, $productId]);
if ((int)$stmt->fetchColumn() === 0) {
    out(['ok' => false, 'error' => 'Отзыв могут оставить только купившие товар.']);
}

// 4. Upsert (один пользователь — один отзыв)
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$existing = $stmt->fetchColumn();

if ($existing) {
    $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, text = ?, created_at = NOW() WHERE id = ?");
    $stmt->execute([$rating, $text, $existing]);
    $msg = 'Отзыв обновлён.';
} else {
    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$productId, $userId, $rating, $text]);
    $msg = 'Отзыв добавлен.';
}

// 5. Обновить средний рейтинг товара
$pdo->prepare("
    UPDATE products SET rating = (
        SELECT ROUND(AVG(rating),1) FROM reviews WHERE product_id = ?
    ) WHERE id = ?
")->execute([$productId, $productId]);

out(['ok' => true, 'message' => $msg]);