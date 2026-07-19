<?php
/** @var array $settings */
$settings   = $settings ?? get_settings();
$pageTitle  = $pageTitle ?? $settings['site_title'];
$logoUrl    = site_logo_url($settings);
$navCurrent = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
$cartCount  = cart_count();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e($settings['site_title']) ?></title>
<link rel="stylesheet" href="<?= e(asset('assets/style.css')) ?>">
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo" aria-label="<?= e($settings['site_title']) ?> anasayfa">
            <?php if ($logoUrl): ?>
                <img class="logo-img" src="<?= e($logoUrl) ?>" alt="" width="38" height="38">
            <?php else: ?>
                <svg class="logo-mark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"></path>
                </svg>
            <?php endif; ?>
            <span class="logo-text"><?= e($settings['site_title']) ?></span>
        </a>

        <nav class="main-nav" id="site-nav">
            <a href="index.php" class="<?= $navCurrent === 'index.php' ? 'active' : '' ?>">Anasayfa</a>
            <a href="index.php#urunler">Ürünler</a>
            <a href="iletisim.php" class="<?= $navCurrent === 'iletisim.php' ? 'active' : '' ?>">İletişim</a>
            <?php if ($settings['phone'] !== ''): ?>
                <a class="phone-btn" href="tel:<?= e(preg_replace('/\s+/', '', $settings['phone'])) ?>">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.79.66 2.64a2 2 0 0 1-.45 2.11L8 9.74a16 16 0 0 0 6.26 6.26l1.27-1.27a2 2 0 0 1 2.11-.45c.85.32 1.74.54 2.64.66A2 2 0 0 1 22 16.92z"></path></svg>
                    <span><?= e($settings['phone']) ?></span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <a href="sepet.php" class="cart-btn <?= $navCurrent === 'sepet.php' ? 'active' : '' ?>" aria-label="Sepetim<?= $cartCount > 0 ? ' (' . $cartCount . ' ürün)' : '' ?>">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1.4"></circle><circle cx="19" cy="21" r="1.4"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                <?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span><?php endif; ?>
            </a>
            <button type="button" class="nav-toggle" aria-label="Menüyü aç/kapat" aria-expanded="false" aria-controls="site-nav">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>
<?php public_flashes(); ?>
<main>
