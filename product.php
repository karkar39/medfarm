<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); exit('Товар не найден'); }

// ----- Покупка -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'buy') {
    if (empty($_SESSION['user_id'])) {
        redirect('login.php?back=' . urlencode('product.php?id=' . $id));
    }
    $uid = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled'
    ");
    $stmt->execute([$uid, $id]);

    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'paid')")->execute([$uid]);
        $oid = (int)$pdo->lastInsertId();
        $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty) VALUES (?, ?, 1)")
            ->execute([$oid, $id]);
    }
    redirect('product.php?id=' . $id);
}

// ----- Статистика отзывов -----
$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, ROUND(AVG(rating),1) AS avg_rating FROM reviews WHERE product_id = ?");
$stmt->execute([$id]);
$stat = $stmt->fetch();
$cnt = (int)$stat['cnt'];
$avg = $stat['avg_rating'] ?: 0;

// ----- Отзывы -----
$stmt = $pdo->prepare("
    SELECT r.rating, r.text, r.created_at, u.name
    FROM reviews r JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ? ORDER BY r.id DESC
");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

// ----- Может ли оставить отзыв? -----
$canReview  = false;
$userReview = null;
if (!empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled'
    ");
    $stmt->execute([$uid, $id]);
    $canReview = (int)$stmt->fetchColumn() > 0;

    $stmt = $pdo->prepare("SELECT rating, text FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$uid, $id]);
    $userReview = $stmt->fetch();
}

$imgSrc = productImage($p);

$pageTitle       = $p['name'] . ' — отзывы, характеристики, цена | МедФарм';
$pageDescription = $p['name'] . ' в МедФарм: ' . mb_substr($p['description'] ?? $p['name'], 0, 120) . ' Цена ' . $p['price'] . ' ₽. Отзывы покупателей.';
$pageKeywords    = $p['name'] . ', отзывы, отзывы покупателей, купить, цена, МедФарм';
$activePage      = 'catalog';
require 'partials/header.php';
?>

<article itemscope itemtype="https://schema.org/Product">
    <h1 itemprop="name"><?= esc($p['name']) ?></h1>

    <img src="<?= esc($imgSrc) ?>"
         alt="Фото <?= esc($p['name']) ?> — отзывы"
         class="product-image"
         itemprop="image">

    <p>
        <span class="avg-rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
            ★ <span itemprop="ratingValue"><?= esc($avg) ?></span>
            <meta itemprop="bestRating" content="5">
            <meta itemprop="reviewCount" content="<?= $cnt ?>">
        </span>
        на основе <?= $cnt ?> отзывов
    </p>
    <p>Категория: <?= esc($p['category']) ?> · Цена:
        <strong itemprop="offers" itemscope itemtype="https://schema.org/Offer">
            <span itemprop="price"><?= esc($p['price']) ?></span> ₽
            <meta itemprop="priceCurrency" content="RUB">
        </strong>
    </p>

    <h2>Характеристики</h2>
    <p><?= esc($p['description'] ?? 'Описание отсутствует.') ?></p>

    <?php if (empty($_SESSION['user_id'])): ?>
        <p><a href="login.php?back=<?= urlencode('product.php?id=' . $id) ?>">Войдите</a>, чтобы купить.</p>
    <?php elseif (!$canReview): ?>
        <form method="post" style="margin:15px 0">
    <input type="hidden" name="action" value="buy">
    <button class="btn" type="submit"
            onclick="ym(113591918, 'reachGoal', 'buy_click'); return true;">
        Купить (тестово)
    </button>
</form>
    <?php else: ?>
        <p style="color:#0a7d4b">✔ Вы купили этот товар</p>
    <?php endif; ?>

    <h2>Отзывы (<?= $cnt ?>)</h2>

    <?php if (!$reviews): ?>
        <p>Пока нет отзывов.</p>
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

    <h2>Оставить отзыв</h2>

    <?php if (empty($_SESSION['user_id'])): ?>
        <p><a href="login.php?back=<?= urlencode('product.php?id=' . $id) ?>">Войдите</a>, чтобы оставить отзыв.</p>
    <?php elseif (!$canReview): ?>
        <p>Отзыв могут оставить только купившие товар.</p>
    <?php else: ?>
        <form class="review-form" onsubmit="return submitReview(this)">
            <input type="hidden" name="product_id" value="<?= $id ?>">
            <input type="hidden" name="rating" value="<?= $userReview ? (int)$userReview['rating'] : 0 ?>">

            <div class="star-input">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $userReview && $i <= $userReview['rating'] ? 'on' : '' ?>">★</span>
                <?php endfor; ?>
            </div>

            <p><textarea name="text" placeholder="Ваш отзыв (минимум 3 символа)"><?= esc($userReview['text'] ?? '') ?></textarea></p>
            <button type="submit" class="btn"><?= $userReview ? 'Обновить отзыв' : 'Отправить отзыв' ?></button>
            <div class="msg"></div>
        </form>
    <?php endif; ?>
</article>

<?php require 'partials/footer.php'; ?>