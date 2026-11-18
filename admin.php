<?php
require __DIR__ . '/../config.php';
requireAdmin();

$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalReviews  = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$totalUsers    = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPromos   = (int)$pdo->query("SELECT COUNT(*) FROM promotions")->fetchColumn();

$products = $pdo->query("SELECT * FROM products ORDER BY id")->fetchAll();

$reviews = $pdo->query("
    SELECT r.id, r.rating, r.text, r.created_at,
           u.name AS user_name,
           p.name AS product_name, p.id AS product_id
    FROM reviews r
    JOIN users u    ON u.id = r.user_id
    JOIN products p ON p.id = r.product_id
    ORDER BY r.id DESC
")->fetchAll();

$promos = $pdo->query("
    SELECT pr.*, p.name AS product_name
    FROM promotions pr
    JOIN products p ON p.id = pr.product_id
    ORDER BY pr.id DESC
")->fetchAll();

$msg = $_SESSION['admin_msg'] ?? '';
unset($_SESSION['admin_msg']);

function productImageSrc($p) {
    if (!empty($p['image']) && file_exists(__DIR__ . '/../' . $p['image'])) {
        return '../' . $p['image'];
    }
    foreach (['.webp', '.jpg', '.png'] as $ext) {
        if (file_exists(__DIR__ . '/../img/' . (int)$p['id'] . $ext)) {
            return '../img/' . (int)$p['id'] . $ext;
        }
    }
    return '../img/no-image.png';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админка — МедФарм</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .admin-stats { display: flex; gap: 15px; flex-wrap: wrap; margin: 20px 0; }
        .admin-stats .card-stat {
            background: #f5f5f5; border-radius: 8px; padding: 15px 20px;
            min-width: 140px; text-align: center;
        }
        .admin-stats .num { font-size: 24px; font-weight: bold; color: #0a7d4b; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .admin-table th, .admin-table td {
            border: 1px solid #ddd; padding: 8px 10px;
            text-align: left; font-size: 14px; vertical-align: middle;
        }
        .admin-table th { background: #f5f5f5; }
        .admin-table tr:nth-child(even) { background: #fafafa; }
        .btn-sm {
            padding: 5px 10px; font-size: 13px; border-radius: 4px;
            text-decoration: none; display: inline-block;
            border: none; cursor: pointer;
        }
        .btn-edit    { background: #0a5c7d; color: #fff; }
        .btn-danger  { background: #c00;    color: #fff; }
        .btn-success { background: #0a7d4b; color: #fff; }
        .admin-msg { padding: 10px 15px; border-radius: 6px; margin: 10px 0; background: #e0f5ea; color: #0a7d4b; }
        .admin-section { margin-top: 40px; }
        .admin-thumb {
            width: 50px; height: 50px;
            object-fit: cover; border-radius: 6px;
            background: #f7f7f7; display: block;
        }
        @media (max-width: 700px) {
            .admin-table { font-size: 13px; }
            .admin-table th, .admin-table td { padding: 6px; }
            .admin-thumb { width: 40px; height: 40px; }
        }
    </style>
</head>
<body>
<header>
    <div class="logo">МедФарм · Админка</div>
    <nav>
        <ul>
            <li><a href="../index.php">На сайт</a></li>
            <li><a href="../logout.php">Выйти</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>Панель администратора</h1>

    <?php if ($msg): ?>
        <div class="admin-msg"><?= esc($msg) ?></div>
    <?php endif; ?>

    <div class="admin-stats">
        <div class="card-stat"><div class="num"><?= $totalProducts ?></div><div>товаров</div></div>
        <div class="card-stat"><div class="num"><?= $totalReviews ?></div><div>отзывов</div></div>
        <div class="card-stat"><div class="num"><?= $totalPromos ?></div><div>акций</div></div>
        <div class="card-stat"><div class="num"><?= $totalUsers ?></div><div>пользователей</div></div>
    </div>

    <!-- ТОВАРЫ -->
    <section class="admin-section">
        <h2>Товары</h2>
        <a href="add_product.php" class="btn btn-success" style="margin:10px 0">+ Добавить товар</a>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th><th>Фото</th><th>Название</th>
                    <th>Категория</th><th>Цена</th><th style="width:170px">Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><img src="<?= esc(productImageSrc($p)) ?>" alt="" class="admin-thumb"></td>
                    <td><?= esc($p['name']) ?></td>
                    <td><?= esc($p['category']) ?></td>
                    <td><?= esc($p['price']) ?> ₽</td>
                    <td>
                        
                        <a href="product_seo.php?id=<?= (int)$p['id'] ?>" class="btn-sm btn-edit" style="background:#8e24aa">SEO</a>
                        <a href="edit_product.php?id=<?= (int)$p['id'] ?>" class="btn-sm btn-edit">Изменить</a>
                        <form method="post" action="delete.php" style="display:inline"
                              onsubmit="return confirm('Удалить товар?')">
                            <input type="hidden" name="type" value="product">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="btn-sm btn-danger" type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- АКЦИИ -->
    <section class="admin-section">
        <h2>Акции (<?= $totalPromos ?>)</h2>
        <a href="add_promo.php" class="btn btn-success" style="margin:10px 0">+ Добавить акцию</a>

        <?php if (!$promos): ?>
            <p>Пока нет акций.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Тип</th><th>Название</th><th>Товар</th>
                        <th>Скидка</th><th>Период</th><th>Статус</th><th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($promos as $pr):
                    $today = date('Y-m-d');
                    if ($pr['date_end'] < $today)         $status = '<span style="color:#999">Завершена</span>';
                    elseif ($pr['date_start'] > $today)   $status = '<span style="color:#0a5c7d">Запланирована</span>';
                    else                                   $status = '<span style="color:#0a7d4b;font-weight:bold">Активна</span>';
                    $types = ['day' => 'День', 'week' => 'Неделя', 'month' => 'Месяц'];
                ?>
                    <tr>
                        <td><?= (int)$pr['id'] ?></td>
                        <td><?= $types[$pr['type']] ?? $pr['type'] ?></td>
                        <td><?= esc($pr['title']) ?></td>
                        <td><?= esc($pr['product_name']) ?></td>
                        <td>−<?= (int)$pr['discount'] ?>%</td>
                        <td>
                            <?= esc(date('d.m.Y', strtotime($pr['date_start']))) ?>
                            — <?= esc(date('d.m.Y', strtotime($pr['date_end']))) ?>
                        </td>
                        <td><?= $status ?></td>
                        <td>
                            <form method="post" action="delete.php" style="display:inline"
                                  onsubmit="return confirm('Удалить акцию?')">
                                <input type="hidden" name="type" value="promo">
                                <input type="hidden" name="id" value="<?= (int)$pr['id'] ?>">
                                <button class="btn-sm btn-danger" type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- ОТЗЫВЫ -->
    <section class="admin-section">
        <h2>Отзывы (<?= $totalReviews ?>)</h2>

        <?php if (!$reviews): ?>
            <p>Пока нет отзывов.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Товар</th><th>Автор</th><th>Оценка</th>
                        <th>Текст</th><th>Дата</th><th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td><a href="../product.php?id=<?= (int)$r['product_id'] ?>"><?= esc($r['product_name']) ?></a></td>
                        <td><?= esc($r['user_name']) ?></td>
                        <td><?= str_repeat('★', (int)$r['rating']) ?></td>
                        <td><?= esc(mb_substr($r['text'], 0, 80)) ?><?= mb_strlen($r['text']) > 80 ? '…' : '' ?></td>
                        <td><?= esc(date('d.m.Y', strtotime($r['created_at']))) ?></td>
                        <td>
                            <form method="post" action="delete.php" style="display:inline"
                                  onsubmit="return confirm('Удалить отзыв?')">
                                <input type="hidden" name="type" value="review">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button class="btn-sm btn-danger" type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>МедФарм — 2026. Админ-панель.</p>
</footer>
</body>
</html>