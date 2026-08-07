<?php
/**
 * =====================================================================
 *  YÖNETİCİ PANELİ — /oxit
 * ---------------------------------------------------------------------
 *  İlk açılışta tek seferlik yönetici oluşturma ekranı gösterilir.
 *  Hesap oluşturulduktan sonra bu ekrana bir daha erişilemez.
 * =====================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/inc/resources.php';
require_once __DIR__ . '/inc/admin_layout.php';

$p = (string) input('p', '', $_GET);

/* -----------------------------------------------------------------
 |  Kurulum kontrolü — hiç yönetici yoksa kurulum ekranına zorla
 ----------------------------------------------------------------- */
if (!auth_installed()) {
    if ($p !== 'kurulum') {
        redirect(admin_url('?p=kurulum'));
    }
    require __DIR__ . '/views/setup.php';
    exit;
}

/* Kurulum tamamlandıysa kurulum ekranı erişilemez */
if ($p === 'kurulum') {
    flash('info', 'Yönetici hesabı zaten oluşturulmuş. Lütfen giriş yapın.');
    redirect(admin_url('?p=giris'));
}

/* -----------------------------------------------------------------
 |  Çıkış
 ----------------------------------------------------------------- */
if ($p === 'cikis') {
    if (csrf_verify()) {
        auth_logout();
        flash('success', 'Güvenli biçimde çıkış yaptınız.');
    }
    redirect(admin_url('?p=giris'));
}

/* -----------------------------------------------------------------
 |  Giriş
 ----------------------------------------------------------------- */
if ($p === 'giris') {
    if (auth_check()) {
        redirect(admin_url('?p=panel'));
    }
    require __DIR__ . '/views/login.php';
    exit;
}

/* -----------------------------------------------------------------
 |  Buradan sonrası oturum gerektirir
 ----------------------------------------------------------------- */
$currentUser = auth_require();

if ($p === '') {
    $p = 'panel';
}

switch ($p) {
    case 'panel':
        require __DIR__ . '/views/dashboard.php';
        break;

    case 'kaynak':
        require __DIR__ . '/inc/crud.php';
        break;

    case 'mesajlar':
        require __DIR__ . '/views/messages.php';
        break;

    case 'ayarlar':
        require __DIR__ . '/views/settings.php';
        break;

    case 'profil':
        require __DIR__ . '/views/profile.php';
        break;

    case 'yedek':
        require __DIR__ . '/views/backup.php';
        break;

    case 'kayitlar':
        require __DIR__ . '/views/logs.php';
        break;

    default:
        flash('error', 'Sayfa bulunamadı.');
        redirect(admin_url('?p=panel'));
}
