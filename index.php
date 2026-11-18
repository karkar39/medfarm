<?php
require 'config.php';

$dayPromos   = getActivePromotions('day');
$weekPromos  = getActivePromotions('week');
$monthPromos = getActivePromotions('month');

$pageTitle       = 'Онлайн-аптека МедФарм — Бронирование лекарств с самовывозом';
$pageDescription = 'Онлайн-аптека МедФарм: бронируйте лекарства, витамины, БАД и товары гигиены онлайн. Акции дня, недели и месяца.';
$pageKeywords    = 'онлайн-аптека, бронирование лекарств, акции, скидки, витамины, БАД, МедФарм';
$activePage      = 'index';
require 'partials/header.php';
?>

<h1>Бронирование лекарств онлайн с самовывозом из аптеки</h1>
<p>«МедФарм» — это онлайн-аптека, где вы можете проверить наличие лекарств в аптеках вашего города, забронировать нужные препараты и забрать их без очереди в удобное время.</p>

<?php if ($dayPromos || $weekPromos || $monthPromos): ?>
    <section class="promo-section">
        <h2>Акции</h2>

        <?php if ($dayPromos): ?>
            <h3 class="promo-title promo-day">Акция дня</h3>
            <div class="promo-grid">
                <?php foreach ($dayPromos as $pr): ?>
                    <?php include 'partials/promo_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($weekPromos): ?>
            <h3 class="promo-title promo-week">Акция недели</h3>
            <div class="promo-grid">
                <?php foreach ($weekPromos as $pr): ?>
                    <?php include 'partials/promo_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($monthPromos): ?>
            <h3 class="promo-title promo-month">Акция месяца</h3>
            <div class="promo-grid">
                <?php foreach ($monthPromos as $pr): ?>
                    <?php include 'partials/promo_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<a href="catalog.php" class="btn">Перейти в каталог</a>
<a href="services.php" class="btn">Как забронировать</a>

<?php require 'partials/footer.php'; ?>