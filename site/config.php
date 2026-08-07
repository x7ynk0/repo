<?php
/**
 * =====================================================================
 *  OXIT STUDIO — Profesyonel Yazılım Hizmetleri Platformu
 *  ---------------------------------------------------------------
 *  Yapılandırma dosyası. Tüm veriler /data klasöründe JSON olarak
 *  saklanır; veritabanı kurulumu gerektirmez.
 * =====================================================================
 */

declare(strict_types=1);

/* ---------------------------------------------------------------
 |  Ortam
 --------------------------------------------------------------- */
define('APP_NAME',        'Oxit Studio');
define('APP_VERSION',     '1.0.0');
define('APP_DEBUG',       false);          // Canlıda mutlaka false bırakın
define('APP_TIMEZONE',    'Europe/Istanbul');
define('APP_LOCALE',      'tr_TR');

/* ---------------------------------------------------------------
 |  Dizinler
 --------------------------------------------------------------- */
define('ROOT_PATH',       __DIR__);
define('INC_PATH',        ROOT_PATH . '/inc');
define('DATA_PATH',       ROOT_PATH . '/data');
define('UPLOAD_PATH',     ROOT_PATH . '/uploads');
define('ADMIN_DIR',       'oxit');         // Yönetici paneli uzantısı: /oxit

/* ---------------------------------------------------------------
 |  Güvenlik
 --------------------------------------------------------------- */
define('SESSION_NAME',    'oxit_session');
define('SESSION_LIFETIME', 60 * 60 * 4);   // 4 saat
define('LOGIN_MAX_TRY',   5);              // Ardışık hatalı giriş limiti
define('LOGIN_LOCK_TIME', 60 * 10);        // 10 dakika kilit
define('PASSWORD_MIN',    8);

/* ---------------------------------------------------------------
 |  Yükleme kuralları
 --------------------------------------------------------------- */
define('UPLOAD_MAX_SIZE', 4 * 1024 * 1024); // 4 MB
const UPLOAD_ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif'];
const UPLOAD_ALLOWED_MIME = [
    'image/jpeg', 'image/png', 'image/webp',
    'image/gif', 'image/svg+xml', 'image/avif',
];

/* ---------------------------------------------------------------
 |  Sayfalama
 --------------------------------------------------------------- */
define('PER_PAGE_BLOG',    6);
define('PER_PAGE_PROJECT', 9);
define('PER_PAGE_ADMIN',   15);

/* ---------------------------------------------------------------
 |  Hata gösterimi
 --------------------------------------------------------------- */
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

date_default_timezone_set(APP_TIMEZONE);

require_once INC_PATH . '/bootstrap.php';
