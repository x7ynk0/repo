<?php
/**
 * Uygulama önyükleyici.
 * Oturum, yardımcı fonksiyonlar ve JSON veri katmanını ayağa kaldırır.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    http_response_code(500);
    exit('config.php yüklenmeden bootstrap çağrılamaz.');
}

require_once INC_PATH . '/Store.php';
require_once INC_PATH . '/helpers.php';
require_once INC_PATH . '/auth.php';
require_once INC_PATH . '/seed.php';

/* ---------------------------------------------------------------
 |  Oturum
 --------------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* Oturum sabitleme (session fixation) koruması */
if (empty($_SESSION['__started'])) {
    $_SESSION['__started'] = time();
    session_regenerate_id(true);
}

/* Boşta kalma süresi aşıldıysa oturumu düşür */
if (!empty($_SESSION['__last']) && (time() - (int) $_SESSION['__last']) > SESSION_LIFETIME) {
    $_SESSION = [];
    session_destroy();
    session_start();
    $_SESSION['__started'] = time();
}
$_SESSION['__last'] = time();

/* ---------------------------------------------------------------
 |  Veri dosyalarını hazırla (ilk çalıştırmada tohumlama)
 --------------------------------------------------------------- */
Store::ensureStorage();
seed_data_files();

/* ---------------------------------------------------------------
 |  Global site ayarları
 --------------------------------------------------------------- */
$GLOBALS['settings'] = Store::read('settings');

/* ---------------------------------------------------------------
 |  Güvenlik başlıkları
 --------------------------------------------------------------- */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('X-XSS-Protection: 0');
}

/* ---------------------------------------------------------------
 |  Ziyaretçi istatistiği (basit, kişisel veri saklamaz)
 --------------------------------------------------------------- */
if (PHP_SAPI !== 'cli' && empty($_SESSION['__counted'])) {
    $_SESSION['__counted'] = true;
    Store::mutate('stats', function (array $stats): array {
        $today = date('Y-m-d');
        $stats['total_visits']         = (int) ($stats['total_visits'] ?? 0) + 1;
        $stats['daily'][$today]        = (int) ($stats['daily'][$today] ?? 0) + 1;
        // Yalnızca son 60 günü tut
        if (count($stats['daily']) > 60) {
            $stats['daily'] = array_slice($stats['daily'], -60, null, true);
        }
        return $stats;
    });
}
