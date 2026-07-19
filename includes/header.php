<?php
/** @var array $settings */
$settings   = $settings ?? get_settings();
$pageTitle  = $pageTitle ?? $settings['site_title'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e($settings['site_title']) ?></title>
<link rel="stylesheet" href="assets/style.css">
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo">
            <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle>
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"></path>
            </svg>
            <span>
                <strong><?= e($settings['site_title']) ?></strong>
                <?php if ($settings['slogan'] !== ''): ?><small><?= e($settings['slogan']) ?></small><?php endif; ?>
            </span>
        </a>
        <nav class="main-nav">
            <a href="index.php">Anasayfa</a>
            <a href="index.php#urunler">Ürünler</a>
            <a href="iletisim.php">İletişim</a>
            <?php if ($settings['phone'] !== ''): ?>
                <a class="phone-btn" href="tel:<?= e(preg_replace('/\s+/', '', $settings['phone'])) ?>">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.79.66 2.64a2 2 0 0 1-.45 2.11L8 9.74a16 16 0 0 0 6.26 6.26l1.27-1.27a2 2 0 0 1 2.11-.45c.85.32 1.74.54 2.64.66A2 2 0 0 1 22 16.92z"></path></svg>
                    <?= e($settings['phone']) ?>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
