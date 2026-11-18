<?php
require __DIR__ . '/../config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect('admin.php'); }

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); exit('Товар не найден'); }

$saved = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alt    = trim($_POST['image_alt']    ?? '');
    $title  = trim($_POST['image_title']  ?? '');
    $webp   = trim($_POST['image_webp']   ?? '');
    $width  = (int)($_POST['image_width']  ?? 0) ?: null;
    $height = (int)($_POST['image_height'] ?? 0) ?: null;

    if (mb_strlen($alt) > 255) {
        $error = 'Alt-текст длиннее 255 символов.';
    } elseif (mb_strlen($title) > 255) {
        $error = 'Title длиннее 255 символов.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE products
            SET image_alt = ?, image_title = ?, image_webp = ?,
                image_width = ?, image_height = ?
            WHERE id = ?
        ");
        $stmt->execute([$alt, $title, $webp, $width, $height, $id]);
        $saved = true;

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
    }
}

$currentImage = 'img/no-image.png';
if (!empty($p['image']) && file_exists(__DIR__ . '/../' . $p['image'])) {
    $currentImage = $p['image'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO изображения — <?= esc($p['name']) ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .seo-form label { display: block; font-weight: bold; margin: 12px 0 4px; }
        .seo-form input[type="text"], .seo-form input[type="number"] {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;
        }
        .seo-preview { max-width: 200px; border-radius: 8px; margin: 10px 0; background: #f7f7f7; }
        .seo-msg-ok  { color: #0a7d4b; background: #e0f5ea; padding: 10px 14px; border-radius: 6px; }
        .seo-msg-err { color: #c00;    background: #fdeaea; padding: 10px 14px; border-radius: 6px; }
    </style>
</head>
<body>
<header>
    <div class="logo">МедФарм · Админка</div>
    <nav><ul>
        <li><a href="admin.php">← Назад</a></li>
        <li><a href="../logout.php">Выйти</a></li>
    </ul></nav>
</header>

<main>
    <h1>SEO-настройки изображения: <?= esc($p['name']) ?></h1>

    <?php if ($saved): ?>
        <p class="seo-msg-ok">Сохранено.</p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="seo-msg-err"><?= esc($error) ?></p>
    <?php endif; ?>

    <p><strong>Текущее изображение:</strong></p>
    <img src="../<?= esc($currentImage) ?>" alt="<?= esc($p['image_alt'] ?? $p['name']) ?>" class="seo-preview">
    <p><small>Путь в БД: <code><?= esc($p['image'] ?? '—') ?></code></small></p>

    <form method="post" class="seo-form" style="max-width:600px">
        <label>Alt-текст</label>
        <input type="text" name="image_alt" maxlength="255"
               value="<?= esc($p['image_alt'] ?? '') ?>"
               placeholder="Парацетамол 500мг №20 — упаковка таблеток">

        <label>Title (подсказка при наведении)</label>
        <input type="text" name="image_title" maxlength="255"
               value="<?= esc($p['image_title'] ?? '') ?>"
               placeholder="Парацетамол 500мг №20">

        <label>Путь к WebP</label>
        <input type="text" name="image_webp" maxlength="500"
               value="<?= esc($p['image_webp'] ?? '') ?>"
               placeholder="img/paracetamol.webp">

        <label>Ширина (px)</label>
        <input type="number" name="image_width" min="1"
               value="<?= (int)($p['image_width'] ?? 0) ?>">

        <label>Высота (px)</label>
        <input type="number" name="image_height" min="1"
               value="<?= (int)($p['image_height'] ?? 0) ?>">

        <p style="margin-top:20px">
            <button class="btn" type="submit">Сохранить</button>
            <a href="admin.php" class="btn" style="background:#888">Отмена</a>
        </p>
    </form>
</main>

<footer><p>МедФарм — 2026. Админ-панель.</p></footer>
</body>
</html>