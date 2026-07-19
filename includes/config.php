<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_URL', 'uploads');
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5 MB
define('MAX_IMAGES_PER_PRODUCT', 8);

if (session_status() === PHP_SESSION_NONE) {
    session_name('OXITSESSID');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

require_once __DIR__ . '/functions.php';

ensure_storage();
