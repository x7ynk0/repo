<?php
/**
 * Yönetici giriş ekranı.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$error = '';
$old   = ['username' => ''];

if (is_post()) {
    if (!csrf_verify()) {
        $error = 'Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.';
    } else {
        $old['username'] = (string) input('username');
        $result = auth_login($old['username'], (string) input('password'));

        if ($result['ok']) {
            $intended = $_SESSION['_intended'] ?? admin_url('?p=panel');
            unset($_SESSION['_intended']);
            flash('success', 'Hoş geldiniz, ' . (auth_user()['name'] ?? '') . '!');
            redirect($intended);
        }
        $error = (string) $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Giriş — <?= e(setting('site.name', APP_NAME)) ?> Yönetim</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/svg+xml" href="<?= e(base_url('assets/img/favicon.svg')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('oxit/assets/admin.css')) ?>">
</head>
<body class="admin admin-auth">

<span class="aurora aurora--1"></span>
<span class="aurora aurora--2"></span>
<div class="grain" aria-hidden="true"></div>

<div class="auth-wrap">
    <div class="auth-card">
        <a class="auth-card__brand" href="<?= e(base_url()) ?>">
            <svg viewBox="0 0 40 40" width="42" height="42">
                <defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="var(--accent)"/><stop offset="100%" stop-color="var(--accent-2)"/>
                </linearGradient></defs>
                <rect x="1.5" y="1.5" width="37" height="37" rx="11" fill="none" stroke="url(#lg)" stroke-width="2"/>
                <path d="M13 15l-4 5 4 5M27 15l4 5-4 5M22 12l-4 16" fill="none" stroke="url(#lg)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>

        <div class="auth-card__head">
            <h2>Yönetim paneli</h2>
            <p class="text-dim">Devam etmek için giriş yapın.</p>
        </div>

        <?= flash_render() ?>

        <?php if ($error !== ''): ?>
            <div class="flash flash--error" style="position:static;margin-bottom:14px">
                <span class="flash__icon"><?= icon('alert', 18) ?></span><span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form class="form" method="post" action="<?= e(admin_url('?p=giris')) ?>" data-validate novalidate>
            <?= csrf_field() ?>

            <div class="field">
                <label class="field__label" for="l-user">Kullanıcı adı veya e-posta <span class="req">*</span></label>
                <input class="input" type="text" id="l-user" name="username" required autofocus
                       value="<?= e($old['username']) ?>" autocomplete="username">
                <span class="field__error"></span>
            </div>

            <div class="field">
                <label class="field__label" for="l-pass">Parola <span class="req">*</span></label>
                <div class="password-field">
                    <input class="input" type="password" id="l-pass" name="password" required autocomplete="current-password">
                    <button class="password-field__eye" type="button" data-toggle-password="l-pass" aria-label="Parolayı göster"><?= icon('eye', 17) ?></button>
                </div>
                <span class="field__error"></span>
            </div>

            <button class="btn btn--primary btn--lg btn--block btn--shine" type="submit">
                <span>Giriş yap</span><?= icon('arrow-right', 18) ?>
            </button>
        </form>

        <div class="form-note mt-6">
            <?= icon('lock', 17) ?>
            <span><?= LOGIN_MAX_TRY ?> hatalı denemeden sonra hesap <?= (int) (LOGIN_LOCK_TIME / 60) ?> dakika kilitlenir.</span>
        </div>

        <p class="auth-card__foot">
            <a href="<?= e(base_url()) ?>"><?= icon('arrow-left', 14) ?> Siteye dön</a>
        </p>
    </div>
</div>

<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
<script src="<?= e(asset('oxit/assets/admin.js')) ?>" defer></script>
</body>
</html>
