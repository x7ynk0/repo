<?php
/**
 * Yönetici paneli düzen (layout) ve form alanı bileşenleri.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

/** Panel menüsü. */
function admin_menu(): array
{
    $items = [
        ['p' => 'panel', 'label' => 'Kontrol Paneli', 'icon' => 'dashboard'],
        ['p' => 'mesajlar', 'label' => 'Gelen Talepler', 'icon' => 'inbox', 'badge' => Store::count('messages', static fn($m) => ($m['status'] ?? '') === 'new')],
    ];

    $items[] = ['divider' => 'İçerik'];
    foreach (admin_resources() as $key => $res) {
        $items[] = [
            'p'     => 'kaynak',
            'r'     => $key,
            'label' => $res['label'],
            'icon'  => $res['icon'],
            'count' => Store::count($key),
        ];
    }

    $items[] = ['divider' => 'Sistem'];
    $items[] = ['p' => 'ayarlar', 'label' => 'Site Ayarları', 'icon' => 'settings'];
    $items[] = ['p' => 'profil',  'label' => 'Profilim', 'icon' => 'user'];
    $items[] = ['p' => 'yedek',   'label' => 'Yedekleme', 'icon' => 'download'];
    $items[] = ['p' => 'kayitlar','label' => 'Sistem Kaydı', 'icon' => 'file'];

    return $items;
}

/** Panel başlığı. */
function admin_header(string $title, array $opts = []): void
{
    $user = auth_user();
    $currentP = (string) input('p', 'panel', $_GET);
    $currentR = (string) input('r', '', $_GET);
    ?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — <?= e(setting('site.name', APP_NAME)) ?> Yönetim</title>
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="icon" type="image/svg+xml" href="<?= e(base_url('assets/img/favicon.svg')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('oxit/assets/admin.css')) ?>">
<script>
(function(){try{var t=localStorage.getItem('oxit-admin-theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body class="admin">

<div class="admin-shell">
    <!-- KENAR ÇUBUĞU -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a class="admin-brand" href="<?= e(admin_url()) ?>">
            <span class="admin-brand__mark">
                <svg viewBox="0 0 40 40" width="30" height="30">
                    <defs><linearGradient id="ag" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="var(--accent)"/><stop offset="100%" stop-color="var(--accent-2)"/>
                    </linearGradient></defs>
                    <rect x="1.5" y="1.5" width="37" height="37" rx="11" fill="none" stroke="url(#ag)" stroke-width="2"/>
                    <path d="M13 15l-4 5 4 5M27 15l4 5-4 5M22 12l-4 16" fill="none" stroke="url(#ag)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="admin-brand__text">
                <strong><?= e(setting('site.short_name', 'OXIT')) ?></strong>
                <small>Yönetim Paneli</small>
            </span>
        </a>

        <nav class="admin-nav" aria-label="Panel menüsü">
            <?php foreach (admin_menu() as $item): ?>
                <?php if (isset($item['divider'])): ?>
                    <div class="admin-nav__divider"><?= e($item['divider']) ?></div>
                <?php continue; endif; ?>

                <?php
                $isActive = $item['p'] === $currentP
                    && (!isset($item['r']) || $item['r'] === $currentR);
                $href = admin_url('?p=' . $item['p'] . (isset($item['r']) ? '&r=' . $item['r'] : ''));
                ?>
                <a class="admin-nav__link<?= $isActive ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                    <span class="admin-nav__ico"><?= icon($item['icon'], 18) ?></span>
                    <span class="admin-nav__label"><?= e($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="admin-nav__badge"><?= (int) $item['badge'] ?></span>
                    <?php elseif (isset($item['count'])): ?>
                        <span class="admin-nav__count"><?= (int) $item['count'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar__foot">
            <a class="admin-nav__link" href="<?= e(base_url()) ?>" target="_blank">
                <span class="admin-nav__ico"><?= icon('external', 18) ?></span>
                <span class="admin-nav__label">Siteyi görüntüle</span>
            </a>
            <a class="admin-nav__link admin-nav__link--danger" href="<?= e(admin_url('?p=cikis&_token=' . urlencode(csrf_token()))) ?>">
                <span class="admin-nav__ico"><?= icon('logout', 18) ?></span>
                <span class="admin-nav__label">Çıkış yap</span>
            </a>
        </div>
    </aside>

    <div class="admin-main">
        <!-- ÜST BAR -->
        <header class="admin-topbar">
            <button class="admin-burger" id="adminBurger" type="button" aria-label="Menü"><?= icon('menu', 20) ?></button>

            <div class="admin-topbar__title">
                <h1><?= e($title) ?></h1>
                <?php if (!empty($opts['subtitle'])): ?>
                    <p><?= e($opts['subtitle']) ?></p>
                <?php endif; ?>
            </div>

            <div class="admin-topbar__actions">
                <?php if (!empty($opts['action'])): ?>
                    <a class="btn btn--primary btn--sm" href="<?= e($opts['action']['url']) ?>">
                        <?= icon($opts['action']['icon'] ?? 'plus', 16) ?><span><?= e($opts['action']['label']) ?></span>
                    </a>
                <?php endif; ?>

                <button class="theme-toggle" id="adminThemeToggle" type="button" aria-label="Tema">
                    <span class="theme-toggle__sun"><?= icon('sun', 17) ?></span>
                    <span class="theme-toggle__moon"><?= icon('moon', 17) ?></span>
                </button>

                <div class="admin-user">
                    <span class="avatar avatar--sm"><?= e(initials((string) ($user['name'] ?? 'A'))) ?></span>
                    <div class="admin-user__info">
                        <strong><?= e($user['name'] ?? '') ?></strong>
                        <small><?= e($user['username'] ?? '') ?></small>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <?= flash_render() ?>
    <?php
}

/** Panel alt bilgisi. */
function admin_footer(): void
{
    ?>
        </div><!-- /admin-content -->

        <footer class="admin-foot">
            <span><?= e(setting('site.name', APP_NAME)) ?> Yönetim Paneli · sürüm <?= e(APP_VERSION) ?></span>
            <span>Veriler <code>/data</code> klasöründe JSON olarak saklanıyor.</span>
        </footer>
    </div><!-- /admin-main -->
</div><!-- /admin-shell -->

<div class="admin-overlay" id="adminOverlay"></div>

<script src="<?= e(asset('oxit/assets/admin.js')) ?>" defer></script>
</body>
</html>
    <?php
}

/* =====================================================================
   FORM ALANI BİLEŞENLERİ
   ===================================================================== */

/**
 * Tek bir form alanını çizer.
 *
 * @param string $name  Alan adı
 * @param array  $def   Alan tanımı
 * @param mixed  $value Mevcut değer
 */
function admin_field(string $name, array $def, $value = null): void
{
    $type     = (string) ($def['type'] ?? 'text');
    $label    = (string) ($def['label'] ?? $name);
    $hint     = (string) ($def['hint'] ?? '');
    $required = !empty($def['required']);
    $id       = 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $col      = (int) ($def['col'] ?? 1);

    if ($value === null && isset($def['default'])) {
        $value = $def['default'];
    }

    echo '<div class="admin-field' . ($col === 2 ? ' admin-field--half' : '') . '" data-type="' . e($type) . '">';

    if ($type !== 'bool') {
        echo '<label class="field__label" for="' . e($id) . '">' . e($label);
        if ($required) echo ' <span class="req">*</span>';
        echo '</label>';
    }

    switch ($type) {

        case 'textarea':
        case 'richtext':
            $rows = (int) ($def['rows'] ?? 6);
            echo '<textarea class="textarea' . ($type === 'richtext' ? ' mono-area' : '') . '" id="' . e($id) . '" name="' . e($name) . '" rows="' . $rows . '"'
                . ($required ? ' required' : '') . '>' . e((string) $value) . '</textarea>';
            if ($type === 'richtext') {
                echo '<div class="admin-toolbar" data-editor-for="' . e($id) . '">'
                    . '<button type="button" data-md="## ">Başlık</button>'
                    . '<button type="button" data-md="**" data-md-wrap="**">Kalın</button>'
                    . '<button type="button" data-md="*" data-md-wrap="*">İtalik</button>'
                    . '<button type="button" data-md="- ">Liste</button>'
                    . '<button type="button" data-md="`" data-md-wrap="`">Kod</button>'
                    . '</div>';
            }
            break;

        case 'number':
        case 'price':
            echo '<input class="input" type="number" id="' . e($id) . '" name="' . e($name) . '"'
                . ' value="' . e((string) ($value ?? '')) . '"'
                . (isset($def['min']) ? ' min="' . (int) $def['min'] . '"' : '')
                . (isset($def['max']) ? ' max="' . (int) $def['max'] . '"' : '')
                . ($type === 'price' ? ' step="100"' : ' step="1"')
                . ($required ? ' required' : '') . '>';
            break;

        case 'range':
            $min = (int) ($def['min'] ?? 0);
            $max = (int) ($def['max'] ?? 100);
            echo '<div class="range-wrap">'
                . '<input type="range" id="' . e($id) . '" name="' . e($name) . '" min="' . $min . '" max="' . $max . '"'
                . ' value="' . (int) ($value ?? 0) . '" oninput="this.nextElementSibling.value=this.value+\'%\'">'
                . '<output class="badge badge--accent" style="width:fit-content">' . (int) ($value ?? 0) . '%</output>'
                . '</div>';
            break;

        case 'date':
            $dv = $value ? date('Y-m-d', strtotime((string) $value)) : date('Y-m-d');
            echo '<input class="input" type="date" id="' . e($id) . '" name="' . e($name) . '" value="' . e($dv) . '">';
            break;

        case 'color':
            echo '<div class="color-field">'
                . '<input type="color" id="' . e($id) . '" name="' . e($name) . '" value="' . e((string) ($value ?: '#7c5cff')) . '">'
                . '<input class="input" type="text" value="' . e((string) $value) . '" readonly>'
                . '</div>';
            break;

        case 'bool':
            echo '<label class="switch">'
                . '<input type="checkbox" name="' . e($name) . '" value="1"' . (!empty($value) ? ' checked' : '') . '>'
                . '<span class="switch__track"><span class="switch__thumb"></span></span>'
                . '<span class="switch__label">' . e($label) . '</span>'
                . '</label>';
            break;

        case 'select':
            echo '<select class="select" id="' . e($id) . '" name="' . e($name) . '">';
            foreach ((array) ($def['options'] ?? []) as $optVal => $optLabel) {
                echo '<option value="' . e((string) $optVal) . '"' . ((string) $value === (string) $optVal ? ' selected' : '') . '>'
                    . e((string) $optLabel) . '</option>';
            }
            echo '</select>';
            break;

        case 'icon':
            echo '<div class="icon-picker">';
            echo '<input type="hidden" id="' . e($id) . '" name="' . e($name) . '" value="' . e((string) $value) . '">';
            echo '<div class="icon-picker__grid">';
            foreach (icon_names() as $ico) {
                $sel = (string) $value === $ico ? ' is-selected' : '';
                echo '<button type="button" class="icon-picker__item' . $sel . '" data-icon="' . e($ico) . '" title="' . e($ico) . '">'
                    . icon($ico, 20) . '</button>';
            }
            echo '</div></div>';
            break;

        case 'image':
            $folder = (string) ($def['folder'] ?? 'misc');
            echo '<div class="image-field">';
            echo '<div class="image-field__preview' . ($value ? '' : ' is-empty') . '">';
            if ($value) {
                echo '<img src="' . e(upload_url((string) $value)) . '" alt="">';
            } else {
                echo icon('upload', 26);
            }
            echo '</div>';
            echo '<div class="image-field__body">';
            echo '<input class="input" type="file" name="' . e($name) . '__file" accept="image/*">';
            echo '<input type="hidden" name="' . e($name) . '__folder" value="' . e($folder) . '">';
            echo '<input class="input mt-2" type="text" id="' . e($id) . '" name="' . e($name) . '" value="' . e((string) $value) . '" placeholder="veya görsel adresi (URL) yapıştırın">';
            if ($value) {
                echo '<label class="checkbox mt-2"><input type="checkbox" name="' . e($name) . '__delete" value="1"><span>Görseli kaldır</span></label>';
            }
            echo '</div></div>';
            break;

        case 'gallery':
            $items = is_array($value) ? $value : [];
            $folder = (string) ($def['folder'] ?? 'misc');
            echo '<div class="gallery-field">';
            echo '<input class="input" type="file" name="' . e($name) . '__files[]" accept="image/*" multiple>';
            echo '<input type="hidden" name="' . e($name) . '__folder" value="' . e($folder) . '">';
            if ($items) {
                echo '<div class="gallery-field__grid">';
                foreach ($items as $i => $img) {
                    echo '<label class="gallery-field__item">'
                        . '<img src="' . e(upload_url((string) $img)) . '" alt="">'
                        . '<input type="hidden" name="' . e($name) . '[]" value="' . e((string) $img) . '">'
                        . '<span class="gallery-field__del"><input type="checkbox" name="' . e($name) . '__remove[]" value="' . $i . '"> Sil</span>'
                        . '</label>';
                }
                echo '</div>';
            }
            echo '</div>';
            break;

        case 'list':
            $items = is_array($value) ? $value : (array) array_filter(explode("\n", (string) $value));
            echo '<textarea class="textarea" id="' . e($id) . '" name="' . e($name) . '" rows="' . max(4, min(12, count($items) + 2)) . '"'
                . ' placeholder="Her satıra bir madde yazın">' . e(implode("\n", array_map('strval', $items))) . '</textarea>';
            break;

        case 'pairs':
            $pairs = is_array($value) ? $value : [];
            $keys  = (array) ($def['pair_keys'] ?? ['label' => 'Etiket', 'value' => 'Değer']);
            $k1    = array_keys($keys)[0];
            $k2    = array_keys($keys)[1];
            echo '<div class="pairs-field" data-pairs>';
            echo '<div class="pairs-field__rows">';
            $rows = $pairs ?: [['', '']];
            foreach ($rows as $pair) {
                $v1 = is_array($pair) ? (string) ($pair[$k1] ?? '') : '';
                $v2 = is_array($pair) ? (string) ($pair[$k2] ?? '') : '';
                echo '<div class="pairs-field__row">'
                    . '<input class="input" type="text" name="' . e($name) . '_' . e($k1) . '[]" value="' . e($v1) . '" placeholder="' . e((string) $keys[$k1]) . '">'
                    . '<input class="input" type="text" name="' . e($name) . '_' . e($k2) . '[]" value="' . e($v2) . '" placeholder="' . e((string) $keys[$k2]) . '">'
                    . '<button type="button" class="pairs-field__del" aria-label="Satırı sil">' . icon('trash', 16) . '</button>'
                    . '</div>';
            }
            echo '</div>';
            echo '<button type="button" class="btn btn--ghost btn--sm mt-2" data-pairs-add>' . icon('plus', 15) . '<span>Satır ekle</span></button>';
            echo '</div>';
            break;

        case 'hidden':
            echo '<input type="hidden" name="' . e($name) . '" value="' . e((string) $value) . '">';
            break;

        default: // text
            echo '<input class="input" type="text" id="' . e($id) . '" name="' . e($name) . '"'
                . ' value="' . e((string) ($value ?? '')) . '"'
                . (!empty($def['placeholder']) ? ' placeholder="' . e($def['placeholder']) . '"' : '')
                . ($required ? ' required' : '') . '>';
    }

    if ($hint !== '') {
        echo '<span class="field__hint">' . e($hint) . '</span>';
    }

    echo '</div>';
}

/** Onay isteyen silme bağlantısı. */
function admin_delete_link(string $url, string $label = 'Sil'): string
{
    return '<a class="admin-table__action admin-table__action--danger" href="' . e($url) . '"'
        . ' data-confirm="Bu kaydı kalıcı olarak silmek istediğinize emin misiniz?">'
        . icon('trash', 16) . '<span class="sr-only">' . e($label) . '</span></a>';
}
