<?php
require __DIR__ . '/../config.php';
requireAdmin();

$error = '';
$products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId  = (int)($_POST['product_id'] ?? 0);
    $type       = $_POST['type'] ?? '';
    $title      = trim($_POST['title'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $discount   = (int)($_POST['discount'] ?? 0);
    $dateStart  = $_POST['date_start'] ?? '';
    $dateEnd    = $_POST['date_end'] ?? '';
    $image      = trim($_POST['image'] ?? '');

    if ($productId < 1) {
        $error = 'Выберите товар.';
    } elseif (!in_array($type, ['day', 'week', 'month'], true)) {
        $error = 'Выберите тип акции.';
    } elseif (mb_strlen($title) < 2) {
        $error = 'Название — минимум 2 символа.';
    } elseif ($discount < 1 || $discount > 99) {
        $error = 'Скидка — от 1 до 99%.';
    } elseif (!$dateStart || !$dateEnd || $dateStart > $dateEnd) {
        $error = 'Проверьте даты.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO promotions
                (product_id, type, title, description, image, discount, date_start, date_end)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $productId, $type, $title, $desc, $image ?: null,
            $discount, $dateStart, $dateEnd
        ]);

        $_SESSION['admin_msg'] = 'Акция «' . $title . '» добавлена.';
        redirect('admin.php');
    }
}

// Значения по умолчанию — сегодня и через N дней
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить акцию — МедФарм</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<header>
    <div class="logo">МедФарм · Админка</div>
    <nav>
        <ul>
            <li><a href="admin.php">← Назад</a></li>
            <li><a href="../logout.php">Выйти</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>Добавить акцию</h1>

    <?php if ($error): ?>
        <p style="color:#c00"><?= esc($error) ?></p>
    <?php endif; ?>

    <form method="post" class="review-form" style="max-width:520px">
        <p>
            <label>Товар<br>
                <select name="product_id" required style="width:100%;padding:8px">
                    <option value="">— выберите товар —</option>
                    <?php foreach ($products as $prod): ?>
                        <option value="<?= (int)$prod['id'] ?>"
                            <?= ($_POST['product_id'] ?? '') == $prod['id'] ? 'selected' : '' ?>>
                            <?= esc($prod['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>

        <p>
            <label>Тип акции<br>
                <select name="type" required style="width:100%;padding:8px">
                    <option value="day"   <?= ($_POST['type'] ?? '') === 'day'   ? 'selected' : '' ?>>Акция дня</option>
                    <option value="week"  <?= ($_POST['type'] ?? '') === 'week'  ? 'selected' : '' ?>>Акция недели</option>
                    <option value="month" <?= ($_POST['type'] ?? '') === 'month' ? 'selected' : '' ?>>Акция месяца</option>
                </select>
            </label>
        </p>

        <p>
            <label>Название акции<br>
                <input type="text" name="title" required minlength="2"
                       value="<?= esc($_POST['title'] ?? '') ?>"
                       placeholder="Например: Скидка на витамины"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Описание<br>
                <textarea name="description" rows="3"
                          style="width:100%;padding:8px"><?= esc($_POST['description'] ?? '') ?></textarea>
            </label>
        </p>

        <p>
            <label>Скидка, %<br>
                <input type="number" name="discount" min="1" max="99" required
                       value="<?= esc($_POST['discount'] ?? '10') ?>"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Дата начала<br>
                <input type="date" name="date_start" required
                       value="<?= esc($_POST['date_start'] ?? $today) ?>"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Дата окончания<br>
                <input type="date" name="date_end" required
                       value="<?= esc($_POST['date_end'] ?? date('Y-m-d', strtotime('+7 days'))) ?>"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Путь к картинке (необязательно)<br>
                <input type="text" name="image"
                       value="<?= esc($_POST['image'] ?? '') ?>"
                       placeholder="img/promotions/1.webp"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <button class="btn" type="submit">Добавить</button>
        <a href="admin.php" class="btn" style="background:#888">Отмена</a>
    </form>
</main>

<footer>
    <p>МедФарм — 2026. Админ-панель.</p>
</footer>
</body>
</html>