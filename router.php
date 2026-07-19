<?php
/**
 * PHP yerleşik sunucusu için yönlendirici.
 * Kullanım: php -S localhost:8000 router.php
 * (Apache kullanıyorsanız bu dosyaya gerek yoktur; .htaccess aynı işi yapar.)
 */
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($uri, '/');

// /oxit → yönetici paneli
if ($path === '/oxit') {
    require __DIR__ . '/oxit.php';
    return true;
}

// data klasörünü dışarıya kapat
if (str_starts_with($uri, '/data/') || $uri === '/data') {
    http_response_code(403);
    echo 'Erişim engellendi.';
    return true;
}

// Mevcut dosyaları (görseller, css, js) doğrudan sun
if ($uri !== '/' && is_file(__DIR__ . $uri)) {
    return false;
}

// Kök adres → anasayfa
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

http_response_code(404);
require __DIR__ . '/index.php';
return true;
