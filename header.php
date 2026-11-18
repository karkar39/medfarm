<?php
if (!isset($pageTitle))       $pageTitle = 'Онлайн-аптека МедФарм';
if (!isset($pageDescription)) $pageDescription = 'Онлайн-аптека МедФарм — бронирование лекарств, витаминов и БАД.';
if (!isset($pageKeywords))    $pageKeywords = 'онлайн-аптека, лекарства, витамины, БАД, гигиена, МедФарм';
if (!isset($activePage))      $activePage = '';

// Настройки сайта для логотипа (ЛР №7)
$__settings = getSettings();
$__siteName = $__settings['site_name']        ?? 'МедФарм';
$__siteDesc = $__settings['site_description'] ?? '';
$__logoPath = $__settings['site_logo']        ?? 'img/logo.svg';
$__logoAlt  = $__settings['site_logo_alt']    ?? 'Логотип';

$menu = [
    'index'    => ['href' => 'index.php',    'label' => 'Главная'],
    'about'    => ['href' => 'about.php',    'label' => 'О нас'],
    'services' => ['href' => 'services.php', 'label' => 'Услуги'],
    // Переименовываем основной каталог, чтобы было понятно, какой это пример
    'catalog'  => ['href' => 'catalog.php',  'label' => 'Каталог (Lazy 1)'],
    // Добавляем новые пункты меню
    'lazy2'    => ['href' => 'catalog_io.php', 'label' => 'Каталог (IO)'],
    'lazy3'    => ['href' => 'catalog_skeleton.php', 'label' => 'Каталог (Skeleton)'],
    'contacts' => ['href' => 'contacts.php', 'label' => 'Контакты'],
    'blog'     => ['href' => 'blog.php',     'label' => 'Блог'],
];

$isAdminHere = false;
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $isAdminHere = (int)$stmt->fetchColumn() === 1;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords"    content="<?= htmlspecialchars($pageKeywords, ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- Favicon и логотип (ЛР №7) -->
    <link rel="icon" type="image/svg+xml" href="<?= esc($__logoPath) ?>">
    
    <!-- Schema.org: Organization с логотипом (ЛР №7) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": <?= json_encode($__siteName, JSON_UNESCAPED_UNICODE) ?>,
      "url": "https://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '') ?>/",
      "logo": "https://<?= htmlspecialchars(($_SERVER['HTTP_HOST'] ?? '') . '/' . $__logoPath) ?>"
    }
    </script>

    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header>
    <a href="index.php" class="logo-link" aria-label="<?= esc($__siteName) ?>">
        <img src="<?= esc($__logoPath) ?>"
             alt="<?= esc($__logoAlt) ?>"
             width="160" height="48"
             decoding="async"
             style="display:block;margin:0 auto">
    </a>
    <nav>
        <ul>
            <?php foreach ($menu as $key => $item): ?>
                <li>
                    <a href="<?= $item['href'] ?>"
                       class="<?= $activePage === $key ? 'active' : '' ?>">
                        <?= $item['label'] ?>
                    </a>
                </li>
            <?php endforeach; ?>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <li><span class="user-greeting">Привет, <?= esc($_SESSION['user_name']) ?></span></li>
                <?php if ($isAdminHere): ?>
                    <li><a href="admin/admin.php">Админка</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Выйти</a></li>
            <?php else: ?>
                <li><a href="login.php">Войти</a></li>
                <li><a href="register.php">Регистрация</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>