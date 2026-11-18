<?php
$pageTitle = 'Каталог — Skeleton';
$pageDescription = 'Каталог с ленивой загрузкой и skeleton-заглушками.';
$pageKeywords = 'каталог, skeleton, МедФарм';
$activePage = 'catalog';
require 'partials/header.php';
?>
<h1>Каталог (Skeleton + Intersection Observer)</h1>
<div class="products" id="products"></div>
<script src="js/lazy3.js" defer></script>
<?php require 'partials/footer.php'; ?>