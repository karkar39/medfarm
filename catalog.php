<?php
require 'config.php';

$allowed = ['Лекарства', 'Витамины и БАД', 'Гигиена'];
$cat = $_GET['cat'] ?? '';
if ($cat !== '' && !in_array($cat, $allowed, true)) $cat = '';

if ($cat !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY id");
    $stmt->execute([$cat]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id");
}
$products = $stmt->fetchAll();

$catLabel = $cat !== '' ? $cat : 'Все товары';
$pageTitle       = "Каталог {$catLabel} — МедФарм";
$pageDescription = "Каталог онлайн-аптеки МедФарм: {$catLabel}. Наличие, цены, отзывы покупателей.";
$pageKeywords    = "каталог, {$catLabel}, лекарства, витамины, БАД, гигиена, отзывы, МедФарм";
$activePage      = 'catalog';
require 'partials/header.php';
?>

<h1>Каталог товаров<?= $cat !== '' ? ': ' . esc($cat) : '' ?></h1>

<div class="filters">
    <a href="catalog.php" class="<?= $cat === '' ? 'active' : '' ?>">Все</a>
    <?php foreach ($allowed as $c): ?>
        <a href="catalog.php?cat=<?= urlencode($c) ?>"
           class="<?= $cat === $c ? 'active' : '' ?>"><?= esc($c) ?></a>
    <?php endforeach; ?>
</div>

<?php if (!$products): ?>
    <p>В этой категории пока нет товаров.</p>
<?php else: ?>
    <div class="products">
        <?php foreach ($products as $p): ?>
            <?php $imgSrc = productImage($p); ?>
            <article class="product-card" itemscope itemtype="https://schema.org/Product">
                <img src="<?= esc($imgSrc) ?>"
                     alt="Фото <?= esc($p['name']) ?> — отзывы"
                     itemprop="image" loading="lazy">
                <div class="name" itemprop="name"><?= esc($p['name']) ?></div>
                <div class="price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                    <span itemprop="price"><?= esc($p['price']) ?></span> ₽
                    <meta itemprop="priceCurrency" content="RUB">
                </div>
                <div class="actions">
                    <button class="btn"
                        onclick="ym(113591918, 'reachGoal', 'quick_view'); openQuickView(<?= (int)$p['id'] ?>)">
                        Быстрый просмотр
                    </button>
                    <a class="btn" href="product.php?id=<?= (int)$p['id'] ?>">Подробнее</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal" id="quickViewModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeQuickView()">×</button>
        <div id="quickViewBody"></div>
    </div>
</div>

<?php require 'partials/footer.php'; ?>