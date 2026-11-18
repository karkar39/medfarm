<?php
// Ожидает: $pr — строка из promotions + product_name, product_price
$imgSrc = promoImage($pr);
$newPrice = $pr['product_price'] * (100 - (int)$pr['discount']) / 100;
?>
<article class="promo-card">
    <img src="<?= esc($imgSrc) ?>" alt="<?= esc($pr['title']) ?>">
    <div class="promo-badge">−<?= (int)$pr['discount'] ?>%</div>
    <h4><?= esc($pr['title']) ?></h4>
    <p class="promo-desc"><?= esc($pr['description']) ?></p>
    <p class="promo-product">
        Товар: <a href="product.php?id=<?= (int)$pr['product_id'] ?>">
            <?= esc($pr['product_name']) ?>
        </a>
    </p>
    <p class="promo-price">
        <span class="old"><?= esc($pr['product_price']) ?> ₽</span>
        <span class="new"><?= number_format($newPrice, 0) ?> ₽</span>
    </p>
    <p class="promo-dates">
        <?= esc(date('d.m.Y', strtotime($pr['date_start']))) ?>
        — <?= esc(date('d.m.Y', strtotime($pr['date_end']))) ?>
    </p>
</article>