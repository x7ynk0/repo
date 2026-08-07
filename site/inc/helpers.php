<?php
/**
 * Genel yardımcı fonksiyonlar.
 */

declare(strict_types=1);

/* =================================================================
 |  Çıktı & metin
 ================================================================= */

/** HTML kaçışlı çıktı. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** HTML özniteliği için güvenli çıktı. */
function attr($value): string
{
    return e($value);
}

/** Metni belirtilen uzunlukta keser. */
function str_limit(?string $text, int $limit = 140, string $end = '…'): string
{
    $text = trim(strip_tags((string) $text));
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . $end;
}

/** Türkçe karakterleri koruyarak URL dostu slug üretir. */
function slugify(string $text): string
{
    $map = [
        'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i',
        'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        'â' => 'a', 'î' => 'i', 'û' => 'u', 'Â' => 'a', 'Î' => 'i', 'Û' => 'u',
    ];
    $text = strtr($text, $map);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'kayit-' . substr(bin2hex(random_bytes(3)), 0, 6);
}

/** Koleksiyonda benzersiz slug üretir. */
function unique_slug(string $collection, string $text, ?string $ignoreId = null): string
{
    $base = slugify($text);
    $slug = $base;
    $i    = 2;
    while (true) {
        $hit = Store::findBy($collection, 'slug', $slug);
        if ($hit === null || (string) ($hit['id'] ?? '') === (string) $ignoreId) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

/** Basit ve güvenli mini-markdown (başlık, kalın, italik, liste, link, kod). */
function rich_text(?string $md): string
{
    $md = trim((string) $md);
    if ($md === '') {
        return '';
    }

    $md = e($md);
    $md = preg_replace('/^### (.*)$/m', '<h4>$1</h4>', $md) ?? $md;
    $md = preg_replace('/^## (.*)$/m', '<h3>$1</h3>', $md) ?? $md;
    $md = preg_replace('/^# (.*)$/m', '<h2>$1</h2>', $md) ?? $md;
    $md = preg_replace('/`([^`]+)`/', '<code>$1</code>', $md) ?? $md;
    $md = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $md) ?? $md;
    $md = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '<em>$1</em>', $md) ?? $md;
    $md = preg_replace(
        '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/',
        '<a href="$2" target="_blank" rel="noopener nofollow">$1</a>',
        $md
    ) ?? $md;

    // Listeler
    $md = preg_replace('/^\- (.*)$/m', '<li>$1</li>', $md) ?? $md;
    $md = preg_replace('/(<li>.*<\/li>\n?)+/s', "<ul>\n$0</ul>\n", $md) ?? $md;

    // Paragraflar
    $blocks = preg_split('/\n{2,}/', $md) ?: [];
    $out    = '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        if (preg_match('/^<(h[2-4]|ul|ol|pre|blockquote)/', $block)) {
            $out .= $block . "\n";
        } else {
            $out .= '<p>' . nl2br($block) . "</p>\n";
        }
    }
    return $out;
}

/** Okuma süresi (dakika). */
function read_time(?string $text): int
{
    $words = str_word_count(strip_tags((string) $text), 0, 'çÇğĞıİöÖşŞüÜ');
    return max(1, (int) ceil($words / 190));
}

/** Türkçe tarih biçimi. */
function tr_date($date, bool $withTime = false): string
{
    $ts = is_numeric($date) ? (int) $date : strtotime((string) $date);
    if (!$ts) {
        return '';
    }
    $months = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
        'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    $out = date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    return $withTime ? $out . ', ' . date('H:i', $ts) : $out;
}

/** "3 saat önce" biçimi. */
function time_ago($date): string
{
    $ts = is_numeric($date) ? (int) $date : strtotime((string) $date);
    if (!$ts) {
        return '';
    }
    $diff = time() - $ts;
    if ($diff < 60)     return 'az önce';
    if ($diff < 3600)   return floor($diff / 60) . ' dakika önce';
    if ($diff < 86400)  return floor($diff / 3600) . ' saat önce';
    if ($diff < 604800) return floor($diff / 86400) . ' gün önce';
    return tr_date($ts);
}

/** Sayıyı okunabilir biçime çevirir (1200 → 1,2B). */
function human_number($n): string
{
    $n = (float) $n;
    if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',') . 'M';
    if ($n >= 1000)    return rtrim(rtrim(number_format($n / 1000, 1, ',', '.'), '0'), ',') . 'B';
    return number_format($n, 0, ',', '.');
}

/** Para birimi biçimi. */
function money($amount, string $currency = '₺'): string
{
    return number_format((float) $amount, 0, ',', '.') . ' ' . $currency;
}

/* =================================================================
 |  URL & varlıklar
 ================================================================= */

/** Sitenin kök URL'i. */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Kurulum alt klasörde olabilir → script yolundan tabanı bul
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $script = preg_replace('#/' . preg_quote(ADMIN_DIR, '#') . '(/.*)?$#', '', $script) ?? $script;
        $script = rtrim($script, '/');
        $base   = $scheme . '://' . $host . $script;
    }
    return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

/** Varlık (asset) URL'i — tarayıcı önbelleğini kırmak için sürüm damgası ekler. */
function asset(string $path): string
{
    $file = ROOT_PATH . '/' . ltrim($path, '/');
    $ver  = is_file($file) ? (string) filemtime($file) : APP_VERSION;
    return base_url($path) . '?v=' . $ver;
}

/** Yükleme dosyası URL'i; yoksa yer tutucu döndürür. */
function upload_url(?string $path, string $fallback = ''): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return $fallback !== '' ? $fallback : base_url('assets/img/placeholder.svg');
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return base_url(ltrim($path, '/'));
}

/** Yönetici paneli URL'i. */
function admin_url(string $path = ''): string
{
    return base_url(ADMIN_DIR . ($path !== '' ? '/' . ltrim($path, '/') : '/'));
}

/** Yönlendirme. */
function redirect(string $url, int $code = 302): void
{
    if (!headers_sent()) {
        header('Location: ' . $url, true, $code);
    } else {
        echo '<script>location.href=' . json_encode($url) . ';</script>';
    }
    exit;
}

/** Geçerli sayfanın dosya adı. */
function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
}

/** Menüde aktif bağlantı kontrolü. */
function is_active(string ...$pages): string
{
    return in_array(current_page(), $pages, true) ? ' is-active' : '';
}

/* =================================================================
 |  İstek yardımcıları
 ================================================================= */

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** GET/POST değerini temizleyerek döndürür. */
function input(string $key, $default = '', ?array $source = null)
{
    $source = $source ?? ($_POST + $_GET);
    $val    = $source[$key] ?? $default;
    if (is_string($val)) {
        $val = trim($val);
        $val = str_replace(["\0", "\r"], '', $val);
    }
    return $val;
}

/** Dizi girdisi (checkbox grupları vb.). */
function input_array(string $key): array
{
    $val = $_POST[$key] ?? $_GET[$key] ?? [];
    if (is_string($val)) {
        $val = array_filter(array_map('trim', explode(',', $val)));
    }
    return is_array($val) ? array_values($val) : [];
}

function input_bool(string $key): bool
{
    $v = $_POST[$key] ?? $_GET[$key] ?? null;
    return in_array((string) $v, ['1', 'on', 'true', 'yes', 'evet'], true);
}

function input_int(string $key, int $default = 0): int
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_numeric($v) ? (int) $v : $default;
}

/** Ziyaretçi IP'si (yalnızca hız sınırlama için, hash'lenerek saklanır). */
function client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', (string) $_SERVER[$key])[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

function ip_hash(): string
{
    return substr(hash('sha256', client_ip() . '|oxit'), 0, 24);
}

function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

/** JSON yanıt döndürür ve çıkar. */
function json_response(array $payload, int $code = 200): void
{
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/* =================================================================
 |  CSRF
 ================================================================= */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token = null): bool
{
    $token = $token ?? (string) ($_POST['_token'] ?? $_GET['_token'] ?? '');
    return !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

/** CSRF geçersizse isteği durdurur. */
function csrf_guard(): void
{
    if (!csrf_verify()) {
        flash('error', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
        redirect($_SERVER['HTTP_REFERER'] ?? base_url());
    }
}

/* =================================================================
 |  Flash mesajlar
 ================================================================= */

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flash_all(): array
{
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

function flash_render(): string
{
    $out = '';
    foreach (flash_all() as $item) {
        $type = in_array($item['type'], ['success', 'error', 'warning', 'info'], true) ? $item['type'] : 'info';
        $out .= '<div class="flash flash--' . $type . '" role="alert">'
            . '<span class="flash__icon">' . icon($type === 'success' ? 'check' : ($type === 'error' ? 'alert' : 'info')) . '</span>'
            . '<span>' . e($item['message']) . '</span>'
            . '<button type="button" class="flash__close" aria-label="Kapat">&times;</button>'
            . '</div>';
    }
    return $out !== '' ? '<div class="flash-stack">' . $out . '</div>' : '';
}

/* =================================================================
 |  Ayarlar
 ================================================================= */

/** Nokta notasyonu ile ayar okur: setting('contact.email') */
function setting(string $key, $default = '')
{
    $data = $GLOBALS['settings'] ?? Store::read('settings');
    foreach (explode('.', $key) as $segment) {
        if (!is_array($data) || !array_key_exists($segment, $data)) {
            return $default;
        }
        $data = $data[$segment];
    }
    return $data === '' || $data === null ? $default : $data;
}

/* =================================================================
 |  Dosya yükleme
 ================================================================= */

/**
 * Görsel yükler ve göreli yolu döndürür.
 *
 * @return array{ok:bool, path?:string, error?:string}
 */
function upload_image(string $field, string $folder = 'misc'): array
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'Dosya seçilmedi.'];
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Yükleme sırasında hata oluştu (kod: ' . $file['error'] . ').'];
    }
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['ok' => false, 'error' => 'Dosya çok büyük. En fazla ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . ' MB olabilir.'];
    }

    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
        return ['ok' => false, 'error' => 'İzin verilmeyen dosya türü.'];
    }

    $mime = 'application/octet-stream';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string) finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    if (!in_array($mime, UPLOAD_ALLOWED_MIME, true)) {
        return ['ok' => false, 'error' => 'Dosya içeriği desteklenmiyor (' . $mime . ').'];
    }

    // SVG içinde script barındırma riskini azalt
    if ($ext === 'svg') {
        $svg = (string) file_get_contents($file['tmp_name']);
        if (preg_match('/<script|onload=|javascript:/i', $svg)) {
            return ['ok' => false, 'error' => 'Güvenli olmayan SVG içeriği reddedildi.'];
        }
    }

    $folder = preg_replace('/[^a-z0-9_\-]/i', '', $folder) ?: 'misc';
    $dir    = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'Dosya kaydedilemedi. Klasör izinlerini kontrol edin.'];
    }
    @chmod($dest, 0664);

    return ['ok' => true, 'path' => 'uploads/' . $folder . '/' . $name];
}

/** Yüklenmiş dosyayı siler. */
function delete_upload(?string $relPath): void
{
    $relPath = trim((string) $relPath);
    if ($relPath === '' || preg_match('#^https?://#i', $relPath)) {
        return;
    }
    $full = realpath(ROOT_PATH . '/' . ltrim($relPath, '/'));
    $base = realpath(UPLOAD_PATH);
    if ($full && $base && str_starts_with($full, $base) && is_file($full)) {
        @unlink($full);
    }
}

/* =================================================================
 |  Sayfalama
 ================================================================= */

/**
 * @return array{items:array, page:int, pages:int, total:int}
 */
function paginate(array $rows, int $perPage, int $page = 1): array
{
    $total = count($rows);
    $pages = max(1, (int) ceil($total / max(1, $perPage)));
    $page  = max(1, min($page, $pages));
    return [
        'items' => array_slice($rows, ($page - 1) * $perPage, $perPage),
        'page'  => $page,
        'pages' => $pages,
        'total' => $total,
    ];
}

/** Sayfalama HTML'i. */
function pagination_html(array $p, string $baseUrl): string
{
    if ($p['pages'] < 2) {
        return '';
    }
    $sep  = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav class="pagination" aria-label="Sayfalama">';

    if ($p['page'] > 1) {
        $html .= '<a class="pagination__link" href="' . e($baseUrl . $sep . 'sayfa=' . ($p['page'] - 1)) . '">‹ Önceki</a>';
    }
    for ($i = 1; $i <= $p['pages']; $i++) {
        if ($i === 1 || $i === $p['pages'] || abs($i - $p['page']) <= 1) {
            $cls = $i === $p['page'] ? ' is-current' : '';
            $html .= '<a class="pagination__link' . $cls . '" href="' . e($baseUrl . $sep . 'sayfa=' . $i) . '">' . $i . '</a>';
        } elseif (abs($i - $p['page']) === 2) {
            $html .= '<span class="pagination__gap">…</span>';
        }
    }
    if ($p['page'] < $p['pages']) {
        $html .= '<a class="pagination__link" href="' . e($baseUrl . $sep . 'sayfa=' . ($p['page'] + 1)) . '">Sonraki ›</a>';
    }

    return $html . '</nav>';
}

/* =================================================================
 |  SVG ikon kütüphanesi
 ================================================================= */

function icon(string $name, int $size = 24, string $class = ''): string
{
    $paths = [
        'code'        => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'mobile'      => '<rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
        'globe'       => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'search'      => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'trend'       => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'share'       => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
        'cart'        => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'cloud'       => '<path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>',
        'shield'      => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'cpu'         => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/>',
        'layers'      => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'pen'         => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
        'db'          => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'zap'         => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'check'       => '<polyline points="20 6 9 17 4 12"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'alert'       => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'info'        => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'arrow-left'  => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'arrow-up'    => '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>',
        'external'    => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'mail'        => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'phone'       => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'pin'         => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'clock'       => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'star'        => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'quote'       => '<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2-2-2H4c-1.25 0-2 .75-2 2v9c0 1.25.75 2 2 2h1c0 3-1 4-2 4z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2-2-2h-4c-1.25 0-2 .75-2 2v9c0 1.25.75 2 2 2h1c0 3-1 4-2 4z"/>',
        'user'        => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'users'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'lock'        => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'settings'    => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'dashboard'   => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>',
        'folder'      => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
        'file'        => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'inbox'       => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'plus'        => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'edit'        => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'trash'       => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'logout'      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'menu'        => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'x'           => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'chevron-down'=> '<polyline points="6 9 12 15 18 9"/>',
        'chevron-right'=> '<polyline points="9 18 15 12 9 6"/>',
        'sun'         => '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>',
        'moon'        => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
        'eye'         => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'award'       => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
        'target'      => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'rocket'      => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91 0z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
        'headset'     => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
        'refresh'     => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'download'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'upload'      => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        'grid'        => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'play'        => '<polygon points="5 3 19 12 5 21 5 3"/>',
        'help'        => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'chart'       => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'gift'        => '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        'link'        => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'terminal'    => '<polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/>',
        'bulb'        => '<line x1="9" y1="18" x2="15" y2="18"/><line x1="10" y1="22" x2="14" y2="22"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>',
        'bot'         => '<rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/>',
        'palette'     => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
    ];

    $body = $paths[$name] ?? $paths['code'];
    $cls  = 'ico' . ($class !== '' ? ' ' . $class : '');

    return '<svg class="' . e($cls) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none"'
        . ' stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"'
        . ' aria-hidden="true" focusable="false">' . $body . '</svg>';
}

/** Kullanılabilir ikon adları (yönetici panelinde seçim için). */
function icon_names(): array
{
    return ['code', 'mobile', 'globe', 'search', 'trend', 'share', 'cart', 'cloud', 'shield',
        'cpu', 'layers', 'pen', 'db', 'zap', 'rocket', 'target', 'bulb', 'bot', 'palette',
        'headset', 'chart', 'terminal', 'award', 'users', 'settings', 'lock', 'gift', 'link'];
}

/** Metinden baş harfleri üretir (avatar). */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out   = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return $out !== '' ? $out : '?';
}

/** Yıldız gösterimi. */
function stars(int $rating): string
{
    $out = '<span class="stars" aria-label="' . $rating . '/5">';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<span class="star' . ($i <= $rating ? ' is-on' : '') . '">' . icon('star', 16) . '</span>';
    }
    return $out . '</span>';
}
