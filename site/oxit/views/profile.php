<?php
/**
 * Yönetici profili ve parola değişimi.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$user = auth_require();

if (is_post()) {
    csrf_guard();
    $form = (string) input('form');

    if ($form === 'profile') {
        $name  = (string) input('name');
        $email = (string) input('email');

        if (mb_strlen($name) < 2) {
            flash('error', 'Ad soyad en az 2 karakter olmalı.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Geçerli bir e-posta adresi girin.');
        } else {
            $avatar = (string) ($user['avatar'] ?? '');
            if (input_bool('avatar__delete')) {
                delete_upload($avatar);
                $avatar = '';
            }
            if (!empty($_FILES['avatar__file']['name'])) {
                $up = upload_image('avatar__file', 'misc');
                if ($up['ok']) {
                    if ($avatar !== '') delete_upload($avatar);
                    $avatar = (string) $up['path'];
                } else {
                    flash('error', $up['error']);
                }
            }

            Store::update('users', $user['id'], ['name' => $name, 'email' => $email, 'avatar' => $avatar]);
            auth_log('profile', 'Profil bilgileri güncellendi.');
            flash('success', 'Profil bilgileriniz güncellendi.');
            redirect(admin_url('?p=profil'));
        }
    }

    if ($form === 'password') {
        $result = auth_change_password(
            (string) $user['id'],
            (string) input('current_password'),
            (string) input('new_password'),
            (string) input('new_password_confirm')
        );

        if ($result['ok']) {
            flash('success', 'Parolanız güncellendi.');
            redirect(admin_url('?p=profil'));
        }
        flash('error', (string) $result['error']);
    }
}

$user = auth_user() ?? $user;

admin_header('Profilim', ['subtitle' => 'Hesap bilgilerinizi ve parolanızı yönetin']);
?>

<div class="admin-grid admin-grid--1-1">

    <!-- PROFİL -->
    <div class="admin-card">
        <div class="admin-card__head"><h2><?= icon('user', 18) ?> Hesap bilgileri</h2></div>

        <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('?p=profil')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="form" value="profile">

            <div class="admin-form-grid">
                <?php
                admin_field('name', ['label' => 'Ad Soyad', 'type' => 'text', 'required' => true, 'col' => 2], $user['name'] ?? '');
                admin_field('email', ['label' => 'E-posta', 'type' => 'text', 'required' => true, 'col' => 2], $user['email'] ?? '');
                admin_field('avatar', ['label' => 'Profil fotoğrafı', 'type' => 'image', 'folder' => 'misc'], $user['avatar'] ?? '');
                ?>

                <div class="admin-field">
                    <label class="field__label">Kullanıcı adı</label>
                    <input class="input" type="text" value="<?= e($user['username'] ?? '') ?>" disabled>
                    <span class="field__hint">Kullanıcı adı güvenlik nedeniyle değiştirilemez.</span>
                </div>
            </div>

            <div class="admin-form-actions">
                <button class="btn btn--primary" type="submit"><?= icon('check', 17) ?><span>Bilgileri kaydet</span></button>
            </div>
        </form>
    </div>

    <!-- PAROLA -->
    <div class="admin-card">
        <div class="admin-card__head"><h2><?= icon('lock', 18) ?> Parola değiştir</h2></div>

        <form class="form" method="post" action="<?= e(admin_url('?p=profil')) ?>" data-validate novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="form" value="password">

            <div class="field">
                <label class="field__label" for="p-cur">Mevcut parola <span class="req">*</span></label>
                <div class="password-field">
                    <input class="input" type="password" id="p-cur" name="current_password" required autocomplete="current-password">
                    <button class="password-field__eye" type="button" data-toggle-password="p-cur" aria-label="Göster"><?= icon('eye', 17) ?></button>
                </div>
                <span class="field__error"></span>
            </div>

            <div class="field">
                <label class="field__label" for="p-new">Yeni parola <span class="req">*</span></label>
                <div class="password-field">
                    <input class="input" type="password" id="p-new" name="new_password" required
                           data-minlength="<?= PASSWORD_MIN ?>" autocomplete="new-password">
                    <button class="password-field__eye" type="button" data-toggle-password="p-new" aria-label="Göster"><?= icon('eye', 17) ?></button>
                </div>
                <div class="pw-meter" id="pwMeter"><span></span></div>
                <span class="field__hint">En az <?= PASSWORD_MIN ?> karakter, bir harf ve bir rakam.</span>
                <span class="field__error"></span>
            </div>

            <div class="field">
                <label class="field__label" for="p-new2">Yeni parola (tekrar) <span class="req">*</span></label>
                <div class="password-field">
                    <input class="input" type="password" id="p-new2" name="new_password_confirm" required autocomplete="new-password">
                    <button class="password-field__eye" type="button" data-toggle-password="p-new2" aria-label="Göster"><?= icon('eye', 17) ?></button>
                </div>
                <span class="field__error"></span>
            </div>

            <button class="btn btn--primary" type="submit"><?= icon('lock', 17) ?><span>Parolayı güncelle</span></button>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card__head"><h2><?= icon('shield', 18) ?> Hesap güvenliği</h2></div>
    <ul class="info-list">
        <li><span>Kullanıcı adı</span><span class="mono"><?= e($user['username'] ?? '') ?></span></li>
        <li><span>Rol</span><span><?= e(($user['role'] ?? 'admin') === 'admin' ? 'Yönetici' : $user['role']) ?></span></li>
        <li><span>Son giriş</span><span><?= e($user['last_login'] ? tr_date($user['last_login'], true) : '—') ?></span></li>
        <li><span>Toplam giriş</span><span class="mono"><?= (int) ($user['login_count'] ?? 0) ?></span></li>
        <li><span>Hesap oluşturma</span><span><?= e(tr_date($user['created_at'] ?? '')) ?></span></li>
    </ul>

    <div class="form-note mt-6">
        <?= icon('alert', 17) ?>
        <span><strong>Parolanızı unutursanız:</strong> Sunucudaki <code>data/users.json</code> dosyasını silin.
        Bir sonraki <code>/oxit</code> ziyaretinizde kurulum ekranı yeniden açılır ve yeni bir yönetici hesabı oluşturabilirsiniz.
        Diğer verileriniz (hizmetler, projeler, mesajlar) etkilenmez.</span>
    </div>
</div>

<?php admin_footer(); ?>
