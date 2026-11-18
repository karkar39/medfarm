<?php
require __DIR__ . '/../config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); exit('Товар не найден'); }

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
            UPDATE products
            SET name = ?, category = ?, price = ?, image = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $category, $price, $image ?: null, $desc, $id]);

        $_SESSION['admin_msg'] = 'Товар «' . $name . '» обновлён.';
        redirect('admin.php');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать товар — МедФарм</title>
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
    <h1>Редактировать: <?= esc($p['name']) ?></h1>

    <?php if ($error): ?>
        <p style="color:#c00"><?= esc($error) ?></p>
    <?php endif; ?>

    <form method="post" class="review-form" style="max-width:500px">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

        <p>
            <label>Название<br>
                <input type="text" name="name" required minlength="2"
                       value="<?= esc($p['name']) ?>"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Категория<br>
                <select name="category" required style="width:100%;padding:8px">
                    <?php foreach (['Лекарства', 'Витамины и БАД', 'Гигиена'] as $c): ?>
                        <option value="<?= $c ?>" <?= $p['category'] === $c ? 'selected' : '' ?>>
                            <?= $c ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>

        <p>
            <label>Цена (₽)<br>
                <input type="number" name="price" step="0.01" min="0.01" required
                       value="<?= esc($p['price']) ?>"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Путь к картинке<br>
                <input type="text" name="image"
                       value="<?= esc($p['image'] ?? '') ?>"
                       placeholder="img/products/1.webp"
                       style="width:100%;padding:8px">
            </label>
        </p>

        <p>
            <label>Описание<br>
                <textarea name="description" rows="4"
                          style="width:100%;padding:8px"><?= esc($p['description'] ?? '') ?></textarea>
            </label>
        </p>

        <button class="btn" type="submit">Сохранить</button>
        <a href="admin.php" class="btn" style="background:#888">Отмена</a>
    </form>
</main>

<footer>
    <p>МедФарм — 2026. Админ-панель.</p>
</footer>
</body>
</html>