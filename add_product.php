<?php
require __DIR__ . '/../config.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? '';
    $price    = (float)($_POST['price'] ?? 0);
    $image    = trim($_POST['image'] ?? '');
    $desc     = trim($_POST['description'] ?? '');

    if (mb_strlen($name) < 2) {
        $error = 'Название — минимум 2 символа.';
    } elseif (!in_array($category, ['Лекарства', 'Витамины и БАД', 'Гигиена'], true)) {
        $error = 'Выберите категорию.';
    } elseif ($price <= 0) {
        $error = 'Цена должна быть больше нуля.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, category, price, image, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category, $price, $image ?: null, $desc]);

        $_SESSION['admin_msg'] = 'Товар «' . $name . '» добавлен.';
        redirect('admin.php');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар — МедФарм</title>
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
    <h1>Добавить товар</h1>

    <?php if ($error): ?>
        <p style="color:#c00"><?= esc($error) ?></p>
    <?php endif; ?>

    <form method="post" class="review-form" style="max-width:500px">
        <p>
            <label>Название<br>
                <input type="text" name="name" required minlength="2"
                       value="<?= esc($_POST['name'] ?? '') ?>"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Категория<br>
                <select name="category" required style="width:100%;padding:8px">
                    <option value="">— выберите —</option>
                    <option value="Лекарства"      <?= ($_POST['category'] ?? '') === 'Лекарства'      ? 'selected' : '' ?>>Лекарства</option>
                    <option value="Витамины и БАД" <?= ($_POST['category'] ?? '') === 'Витамины и БАД' ? 'selected' : '' ?>>Витамины и БАД</option>
                    <option value="Гигиена"        <?= ($_POST['category'] ?? '') === 'Гигиена'        ? 'selected' : '' ?>>Гигиена</option>
                </select>
            </label>
        </p>

        <p>
            <label>Цена (₽)<br>
                <input type="number" name="price" step="0.01" min="0.01" required
                       value="<?= esc($_POST['price'] ?? '') ?>"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Путь к картинке (например, img/products/61.webp)<br>
                <input type="text" name="image"
                       value="<?= esc($_POST['image'] ?? '') ?>"
                       placeholder="img/products/61.webp"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Описание<br>
                <textarea name="description" rows="4"
                          style="width:100%;padding:8px"><?= esc($_POST['description'] ?? '') ?></textarea>
            </label>
        </p>

        <button class="btn" type="submit">Добавить товар</button>
        <a href="admin.php" class="btn" style="background:#888">Отмена</a>
    </form>
</main>

<footer>
    <p>МедФарм — 2026. Админ-панель.</p>
</footer>
</body>
</html>