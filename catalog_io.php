<?php
$pageTitle = 'Каталог — Intersection Observer';
$pageDescription = 'Каталог с ленивой загрузкой через Intersection Observer.';
$pageKeywords = 'каталог, Intersection Observer, МедФарм';
$activePage = 'catalog';
require 'partials/header.php';
?>
<h1>Каталог (Intersection Observer)</h1>
<div class="products" id="products"></div>
<script src="js/lazy2.js" defer></script>
<?php require 'partials/footer.php'; ?>