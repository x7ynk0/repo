<?php
/**
 * Tek seferlik yönetici oluşturma ekranı.
 * Yalnızca sistemde hiç yönetici yokken erişilebilir.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$errors = [];
$old = ['name' => '', 'username' => '', 'email' => ''];

if (is_post()) {
    if (!csrf_verify()) {
        $errors[] = 'Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.';
    } else {
        $old['name']     = (string) input('name');
        $old['username'] = (string) input('username');
        $old['email']    = (string) input('email');

        $result = auth_install(
            $old['name'],
            $old['username'],
            $old['email'],
            (string) input('password'),
            (string) input('password_confirm')
        );

        if ($result['ok']) {
            // Kurulumdan hemen sonra otomatik giriş
            auth_login($old['username'], (string) input('password'));
            flash('success', 'Yönetici hesabınız oluşturuldu. Hoş geldiniz!');
            redirect(admin_url('?p=panel'));
        }

        $errors[] = (string) $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kurulum — <?= e(setting('site.name', APP_NAME)) ?></title>
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

<div class="auth-wrap auth-wrap--wide">
    <div class="auth-side">
        <div class="auth-side__inner">
            <span class="eyebrow"><?= icon('rocket', 15) ?> Kuruluma hoş geldiniz</span>
            <h1>Yönetici hesabınızı <span class="gradient-text">oluşturun</span></h1>
            <p class="lead mt-4">Bu ekran yalnızca <strong>bir kez</strong> gösterilir. Hesabınızı oluşturduktan sonra
               bu adres kalıcı olarak giriş ekranına dönüşür.</p>

            <ul class="check-list mt-7">
                <li><?= icon('check', 17) ?> Tüm verileriniz sunucunuzda JSON olarak saklanır</li>
                <li><?= icon('check', 17) ?> Veritabanı kurulumu gerekmez</li>
                <li><?= icon('check', 17) ?> Parolanız geri döndürülemez biçimde şifrelenir</li>
                <li><?= icon('check', 17) ?> Hatalı giriş denemelerine karşı kilit koruması vardır</li>
            </ul>

            <div class="form-note mt-7">
                <?= icon('alert', 18) ?>
                <span><strong>Önemli:</strong> Bu bilgileri güvenli bir yerde saklayın.
                Parolanızı unutursanız, sunucudaki <code>data/users.json</code> dosyasını silerek kurulumu sıfırlayabilirsiniz.</span>
            </div>
        </div>
    </div>

    <div class="auth-card">
        <div class="auth-card__head">
            <span class="auth-card__step">Adım 1 / 1</span>
            <h2>Yönetici bilgileri</h2>
            <p class="text-dim">Panele giriş yapmak için kullanacağınız bilgiler.</p>
        </div>

        <?php foreach ($errors as $err): ?>
            <div class="flash flash--error" style="position:static;margin-bottom:14px">
                <span class="flash__icon"><?= icon('alert', 18) ?></span><span><?= e($err) ?></span>
            </div>
        <?php endforeach; ?>

        <form class="form" method="post" action="<?= e(admin_url('?p=kurulum')) ?>" data-validate novalidate>
            <?= csrf_field() ?>

            <div class="field">
                <label class="field__label" for="s-name">Ad Soyad <span class="req">*</span></label>
                <input class="input" type="text" id="s-name" name="name" required autofocus
                       value="<?= e($old['name']) ?>" placeholder="Örn: Ahmet Yılmaz" autocomplete="name">
                <span class="field__error"></span>
            </div>

            <div class="field">
                <label class="field__label" for="s-username">Kullanıcı adı <span class="req">*</span></label>
                <input class="input" type="text" id="s-username" name="username" required
                       value="<?= e($old['username']) ?>" placeholder="admin" autocomplete="username"
                       pattern="[a-zA-Z0-9_.\-]{3,32}">
                <span class="field__hint">3-32 karakter; harf, rakam, nokta, tire ve alt çizgi kullanılabilir.</span>
                <span class="field__error"></span>
            </div>

            <div class="field">
                <label class="field__label" for="s-email">E-posta <span class="req">*</span></label>
                <input class="input" type="email" id="s-email" name="email" required
                       value="<?= e($old['email']) ?>" placeholder="siz@sirketiniz.com" autocomplete="email">
                <span class="field__error"></span>
            </div>

            <div class="field">
                <label class="field__label" for="s-password">Parola <span class="req">*</span></label>
                <div class="password-field">
                    <input class="input" type="password" id="s-password" name="password" required
                           data-minlength="<?= PASSWORD_MIN ?>" placeholder="En az <?= PASSWORD_MIN ?> karakter"
                           autocomplete="new-password">
                    <button class="password-field__eye" type="button" data-toggle-password="s-password" aria-label="Parolayı göster"><?= icon('eye', 17) ?></button>
                </div>
                <div class="pw-meter" id="pwMeter"><span></span></div>
                <span class="field__hint">En az <?= PASSWORD_MIN ?> karakter, bir harf ve bir rakam içermelidir.</span>
                <span class="field__error"></span>
            </div>

            <div class="field">
                <label class="field__label" for="s-password2">Parola (tekrar) <span class="req">*</span></label>
                <div class="password-field">
                    <input class="input" type="password" id="s-password2" name="password_confirm" required
                           placeholder="Parolayı tekrar girin" autocomplete="new-password">
                    <button class="password-field__eye" type="button" data-toggle-password="s-password2" aria-label="Parolayı göster"><?= icon('eye', 17) ?></button>
                </div>
                <span class="field__error"></span>
            </div>

            <button class="btn btn--primary btn--lg btn--block btn--shine" type="submit">
                <span>Hesabı oluştur ve panele gir</span><?= icon('arrow-right', 18) ?>
            </button>
        </form>

        <p class="auth-card__foot">
            <a href="<?= e(base_url()) ?>"><?= icon('arrow-left', 14) ?> Siteye dön</a>
        </p>
    </div>
</div>

<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
<script src="<?= e(asset('oxit/assets/admin.js')) ?>" defer></script>
</body>
</html>
