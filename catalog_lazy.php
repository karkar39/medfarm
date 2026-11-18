<?php
$pageTitle = 'Каталог — loading="lazy"';
$pageDescription = 'Каталог с нативной ленивой загрузкой.';
$pageKeywords = 'каталог, loading lazy, МедФарм';
$activePage = 'catalog';
require 'partials/header.php';
?>
<h1>Каталог (нативный loading="lazy")</h1>
<div class="products" id="products"></div>
<script src="js/lazy1.js" defer></script>
<?php require 'partials/footer.php'; ?>