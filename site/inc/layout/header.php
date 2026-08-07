<?php
/**
 * Genel site başlığı (header) — tüm ön yüz sayfaları bunu kullanır.
 *
 * Sayfalar dahil etmeden önce şu değişkenleri tanımlayabilir:
 *   $page = ['title'=>'', 'desc'=>'', 'image'=>'', 'type'=>'website', 'noindex'=>false]
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$page = array_merge([
    'title'    => '',
    'desc'     => '',
    'image'    => '',
    'type'     => 'website',
    'noindex'  => false,
    'body_class' => '',
], $page ?? []);

$siteName  = (string) setting('site.name', APP_NAME);
$pageTitle = $page['title'] !== ''
    ? $page['title'] . ' — ' . $siteName
    : $siteName . ' — ' . setting('site.tagline', '');
$pageDesc  = $page['desc'] !== '' ? $page['desc'] : (string) setting('site.description', '');
$ogImage   = $page['image'] !== '' ? upload_url($page['image']) : upload_url(setting('site.og_image', ''), base_url('assets/img/og-default.svg'));
$canonical = rtrim((string) setting('seo.canonical', base_url()), '/') . ($_SERVER['REQUEST_URI'] ?? '');
$theme     = setting('theme', []);
$accent    = (string) ($theme['accent']  ?? '#7c5cff');
$accent2   = (string) ($theme['accent2'] ?? '#22d3ee');
$accent3   = (string) ($theme['accent3'] ?? '#f472b6');
$radius    = (string) ($theme['radius']  ?? '18');
$mode      = ($theme['mode'] ?? 'dark') === 'light' ? 'light' : 'dark';

/* Bakım modu — yönetici girişi hariç */
if (setting('maintenance.enabled', false) && !auth_check()) {
    http_response_code(503);
    include INC_PATH . '/layout/maintenance.php';
    exit;
}

$navItems = [
    ['url' => base_url('index.php'),      'label' => 'Ana Sayfa', 'pages' => ['index.php']],
    ['url' => base_url('hizmetler.php'),  'label' => 'Hizmetler', 'pages' => ['hizmetler.php', 'hizmet.php']],
    ['url' => base_url('projeler.php'),   'label' => 'Referanslar', 'pages' => ['projeler.php', 'proje.php']],
    ['url' => base_url('hakkimizda.php'), 'label' => 'Hakkımızda', 'pages' => ['hakkimizda.php']],
];
if (setting('features.blog', true)) {
    $navItems[] = ['url' => base_url('blog.php'), 'label' => 'Blog', 'pages' => ['blog.php', 'yazi.php']];
}
$navItems[] = ['url' => base_url('iletisim.php'), 'label' => 'İletişim', 'pages' => ['iletisim.php']];
?>
<!DOCTYPE html>
<html lang="<?= e(setting('site.lang', 'tr')) ?>" data-theme="<?= e($mode) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($pageTitle) ?></title>

<meta name="description" content="<?= e(str_limit($pageDesc, 160)) ?>">
<meta name="keywords" content="<?= e(setting('site.keywords', '')) ?>">
<meta name="author" content="<?= e(setting('site.author', $siteName)) ?>">
<meta name="robots" content="<?= $page['noindex'] ? 'noindex, nofollow' : e(setting('seo.robots', 'index, follow')) ?>">
<meta name="theme-color" content="<?= e($accent) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">

<!-- Open Graph / Twitter -->
<meta property="og:type" content="<?= e($page['type']) ?>">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e(str_limit($pageDesc, 200)) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:locale" content="tr_TR">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e(str_limit($pageDesc, 200)) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">

<link rel="icon" type="image/svg+xml" href="<?= e(upload_url(setting('site.favicon', ''), base_url('assets/img/favicon.svg'))) ?>">
<link rel="apple-touch-icon" href="<?= e(upload_url(setting('site.favicon', ''), base_url('assets/img/favicon.svg'))) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">

<style>
    :root{
        --accent: <?= e($accent) ?>;
        --accent-2: <?= e($accent2) ?>;
        --accent-3: <?= e($accent3) ?>;
        --radius: <?= (int) $radius ?>px;
    }
</style>

<script>
    // Tema tercihini FOUC olmadan uygula
    (function () {
        try {
            var saved = localStorage.getItem('oxit-theme');
            if (saved === 'light' || saved === 'dark') {
                document.documentElement.setAttribute('data-theme', saved);
            }
        } catch (e) {}
    })();
</script>

<!-- Yapısal veri -->
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'ProfessionalService',
    'name'     => $siteName,
    'description' => setting('site.description', ''),
    'url'      => base_url(),
    'image'    => $ogImage,
    'email'    => setting('contact.email', ''),
    'telephone'=> setting('contact.phone', ''),
    'address'  => ['@type' => 'PostalAddress', 'addressLocality' => setting('contact.address', ''), 'addressCountry' => 'TR'],
    'sameAs'   => array_values(array_filter((array) setting('social', []))),
    'priceRange' => '₺₺₺',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>

<?php if ($gaId = setting('seo.ga_id', '')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', <?= json_encode($gaId) ?>);
</script>
<?php endif; ?>
<?php if ($sc = setting('seo.search_console', '')): ?>
<meta name="google-site-verification" content="<?= e($sc) ?>">
<?php endif; ?>
</head>

<body class="<?= e($page['body_class']) ?>"
      data-cursor="<?= !empty($theme['cursor']) ? '1' : '0' ?>"
      data-particles="<?= !empty($theme['particles']) ? '1' : '0' ?>">

<?php if (!empty($theme['preloader'])): ?>
<div class="preloader" id="preloader" aria-hidden="true">
    <div class="preloader__inner">
        <div class="preloader__logo"><?= e(setting('site.short_name', 'OXIT')) ?></div>
        <div class="preloader__bar"><span id="preloaderBar"></span></div>
        <div class="preloader__pct" id="preloaderPct">0%</div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($theme['noise'])): ?><div class="grain" aria-hidden="true"></div><?php endif; ?>

<div class="cursor" id="cursor" aria-hidden="true"><span class="cursor__dot"></span></div>
<div class="cursor-ring" id="cursorRing" aria-hidden="true"></div>

<div class="scroll-progress" aria-hidden="true"><span id="scrollProgress"></span></div>

<a class="skip-link" href="#main">İçeriğe geç</a>

<header class="site-header" id="siteHeader">
    <div class="container site-header__inner">
        <a class="brand" href="<?= e(base_url()) ?>" data-magnetic>
            <?php if ($logo = setting('site.logo', '')): ?>
                <img class="brand__img" src="<?= e(upload_url($logo)) ?>" alt="<?= e($siteName) ?>">
            <?php else: ?>
                <span class="brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 40 40" width="38" height="38">
                        <defs>
                            <linearGradient id="brandGrad" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="var(--accent)"/>
                                <stop offset="100%" stop-color="var(--accent-2)"/>
                            </linearGradient>
                        </defs>
                        <rect x="1.5" y="1.5" width="37" height="37" rx="11" fill="none" stroke="url(#brandGrad)" stroke-width="2"/>
                        <path d="M13 15l-4 5 4 5M27 15l4 5-4 5M22 12l-4 16" fill="none" stroke="url(#brandGrad)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="brand__text">
                    <strong><?= e($siteName) ?></strong>
                    <small><?= e(setting('site.tagline', '')) ?></small>
                </span>
            <?php endif; ?>
        </a>

        <nav class="nav" aria-label="Ana menü">
            <ul class="nav__list">
                <?php foreach ($navItems as $item): ?>
                    <li>
                        <a class="nav__link<?= is_active(...$item['pages']) ?>" href="<?= e($item['url']) ?>">
                            <span><?= e($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="site-header__actions">
            <button class="theme-toggle" id="themeToggle" type="button" aria-label="Temayı değiştir" title="Açık / koyu tema">
                <span class="theme-toggle__sun"><?= icon('sun', 18) ?></span>
                <span class="theme-toggle__moon"><?= icon('moon', 18) ?></span>
            </button>

            <a class="btn btn--primary btn--sm hide-sm" href="<?= e(base_url('iletisim.php')) ?>" data-magnetic>
                <span>Teklif Al</span>
                <?= icon('arrow-right', 16) ?>
            </a>

            <button class="burger" id="burger" type="button" aria-label="Menüyü aç" aria-expanded="false" aria-controls="mobileMenu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
    <div class="mobile-menu__panel">
        <nav aria-label="Mobil menü">
            <ul class="mobile-menu__list">
                <?php foreach ($navItems as $i => $item): ?>
                    <li style="--i:<?= $i ?>">
                        <a class="mobile-menu__link<?= is_active(...$item['pages']) ?>" href="<?= e($item['url']) ?>">
                            <span class="mobile-menu__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <?= e($item['label']) ?>
                            <?= icon('arrow-right', 18) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="mobile-menu__footer">
            <a class="btn btn--primary btn--block" href="<?= e(base_url('iletisim.php')) ?>">Ücretsiz Teklif Al</a>
            <div class="mobile-menu__contact">
                <a href="mailto:<?= e(setting('contact.email', '')) ?>"><?= icon('mail', 16) ?> <?= e(setting('contact.email', '')) ?></a>
                <a href="tel:<?= e(preg_replace('/\s+/', '', (string) setting('contact.phone', ''))) ?>"><?= icon('phone', 16) ?> <?= e(setting('contact.phone', '')) ?></a>
            </div>
        </div>
    </div>
</div>

<?= flash_render() ?>

<main id="main">
