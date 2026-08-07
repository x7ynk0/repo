<?php
/**
 * Dinamik XML site haritası.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [
    ['loc' => base_url('index.php'),      'priority' => '1.0', 'freq' => 'weekly'],
    ['loc' => base_url('hizmetler.php'),  'priority' => '0.9', 'freq' => 'weekly'],
    ['loc' => base_url('projeler.php'),   'priority' => '0.9', 'freq' => 'weekly'],
    ['loc' => base_url('hakkimizda.php'), 'priority' => '0.7', 'freq' => 'monthly'],
    ['loc' => base_url('sss.php'),        'priority' => '0.6', 'freq' => 'monthly'],
    ['loc' => base_url('iletisim.php'),   'priority' => '0.8', 'freq' => 'monthly'],
    ['loc' => base_url('gizlilik.php'),   'priority' => '0.3', 'freq' => 'yearly'],
];

foreach (Store::all('services', ['only_active' => true]) as $s) {
    $urls[] = [
        'loc'      => base_url('hizmet.php?s=' . urlencode((string) $s['slug'])),
        'lastmod'  => substr((string) ($s['updated_at'] ?? ''), 0, 10),
        'priority' => '0.8',
        'freq'     => 'monthly',
    ];
}

foreach (Store::all('projects', ['only_active' => true]) as $p) {
    $urls[] = [
        'loc'      => base_url('proje.php?p=' . urlencode((string) $p['slug'])),
        'lastmod'  => substr((string) ($p['updated_at'] ?? ''), 0, 10),
        'priority' => '0.7',
        'freq'     => 'monthly',
    ];
}

if (setting('features.blog', true)) {
    $urls[] = ['loc' => base_url('blog.php'), 'priority' => '0.8', 'freq' => 'weekly'];
    foreach (Store::all('posts', ['only_active' => true]) as $post) {
        $urls[] = [
            'loc'      => base_url('yazi.php?y=' . urlencode((string) $post['slug'])),
            'lastmod'  => substr((string) ($post['date'] ?? $post['updated_at'] ?? ''), 0, 10),
            'priority' => '0.6',
            'freq'     => 'monthly',
        ];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?= e($u['loc']) ?></loc>
        <?php if (!empty($u['lastmod'])): ?><lastmod><?= e($u['lastmod']) ?></lastmod><?php endif; ?>
        <changefreq><?= e($u['freq']) ?></changefreq>
        <priority><?= e($u['priority']) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
