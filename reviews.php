<?php
require 'config.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action !== 'quickview' || $id < 1) { http_response_code(400); exit('Bad request'); }

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); exit('Товар не найден'); }

$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, ROUND(AVG(rating),1) AS avg_rating FROM reviews WHERE product_id = ?");
$stmt->execute([$id]);
$stat = $stmt->fetch();
$cnt = (int)$stat['cnt'];
$avg = $stat['avg_rating'] ?: 0;

$stmt = $pdo->prepare("
    SELECT r.rating, r.text, r.created_at, u.name
    FROM reviews r JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ? ORDER BY r.id DESC
");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$imgSrc = productImage($p);
?>
<h2><?= esc($p['name']) ?></h2>
<img src="<?= esc($imgSrc) ?>"
     alt="Фото <?= esc($p['name']) ?> — отзывы"
     style="max-width:100%;border-radius:6px;margin:8px 0">

<p>
    <span class="avg-rating">★ <?= esc($avg) ?></span>
    (<?= $cnt ?> отзывов) · <?= esc($p['price']) ?> ₽
</p>

<div class="tabs">
    <button class="tab active" data-target="tab-chars">Характеристики</button>
    <button class="tab" data-target="tab-reviews">Отзывы (<?= $cnt ?>)</button>
</div>

<div class="tab-panel active" id="tab-chars">
    <h3>Характеристики</h3>
    <p><strong>Категория:</strong> <?= esc($p['category']) ?></p>
    <p><strong>Цена:</strong> <?= esc($p['price']) ?> ₽</p>
    <p><?= esc($p['description'] ?? '—') ?></p>
</div>

<div class="tab-panel" id="tab-reviews">
    <h3>Отзывы (<?= $cnt ?>)</h3>

    <?php if (!$reviews): ?>
        <p>Пока нет отзывов. Будьте первым!</p>
    <?php else: foreach ($reviews as $r): ?>
        <article class="review" itemprop="review" itemscope itemtype="https://schema.org/Review">
            <span class="review-author" itemprop="author"><?= esc($r['name']) ?></span>
            <span class="review-date"><?= esc(date('d.m.Y', strtotime($r['created_at']))) ?></span>
            <div class="stars" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                <meta itemprop="ratingValue" content="<?= (int)$r['rating'] ?>">
                <meta itemprop="bestRating" content="5">
                <?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?>
            </div>
            <div class="review-text" itemprop="reviewBody"><?= esc($r['text']) ?></div>
        </article>
    <?php endforeach; endif; ?>
</div>