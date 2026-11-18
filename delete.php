<?php
require __DIR__ . '/../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}

$type = $_POST['type'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

if ($id < 1) {
    $_SESSION['admin_msg'] = 'Неверный ID.';
    redirect('admin.php');
}

if ($type === 'review') {
    $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
    $_SESSION['admin_msg'] = 'Отзыв удалён.';

} elseif ($type === 'product') {
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $name = $stmt->fetchColumn();

    if ($name) {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $_SESSION['admin_msg'] = 'Товар «' . $name . '» и все его отзывы удалены.';
    } else {
        $_SESSION['admin_msg'] = 'Товар не найден.';
    }

} elseif ($type === 'promo') {
    $stmt = $pdo->prepare("SELECT title FROM promotions WHERE id = ?");
    $stmt->execute([$id]);
    $title = $stmt->fetchColumn();

    if ($title) {
        $pdo->prepare("DELETE FROM promotions WHERE id = ?")->execute([$id]);
        $_SESSION['admin_msg'] = 'Акция «' . $title . '» удалена.';
    } else {
        $_SESSION['admin_msg'] = 'Акция не найдена.';
    }

} else {
    $_SESSION['admin_msg'] = 'Неизвестный тип удаления.';
}

redirect('admin.php');