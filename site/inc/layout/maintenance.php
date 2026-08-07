<?php
/** Bakım modu ekranı. */
declare(strict_types=1);
if (!defined('ROOT_PATH')) { exit; }
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bakımdayız — <?= e(setting('site.name', APP_NAME)) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
</head>
<body>
<div class="maintenance">
    <span class="aurora aurora--1"></span>
    <span class="aurora aurora--2"></span>

    <div class="container" style="position:relative;z-index:2;max-width:620px">
        <div class="service-card__icon" style="margin-inline:auto"><?= icon('settings', 26) ?></div>
        <h1 class="mt-4">Kısa bir <span class="gradient-text">bakımdayız</span></h1>
        <p class="lead mt-4"><?= e(setting('maintenance.message', 'Kısa süre içinde geri döneceğiz.')) ?></p>

        <div class="flex flex-wrap gap-3 mt-7" style="justify-content:center">
            <?php if ($mail = setting('contact.email', '')): ?>
                <a class="btn btn--primary" href="mailto:<?= e($mail) ?>"><?= icon('mail', 17) ?><span><?= e($mail) ?></span></a>
            <?php endif; ?>
            <?php if ($tel = setting('contact.phone', '')): ?>
                <a class="btn btn--ghost" href="tel:<?= e(preg_replace('/\s+/', '', (string) $tel)) ?>"><?= icon('phone', 17) ?><span><?= e($tel) ?></span></a>
            <?php endif; ?>
        </div>

        <p class="text-dim mt-7" style="font-size:.84rem">
            <a href="<?= e(admin_url()) ?>">Yönetici girişi</a>
        </p>
    </div>
</div>
</body>
</html>
