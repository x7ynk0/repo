<?php
/**
 * Blog listesi (arama + kategori filtresi + sayfalama).
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (!setting('features.blog', true)) {
    redirect(base_url());
}

$posts = Store::all('posts', ['only_active' => true, 'sort' => 'date', 'dir' => 'desc']);

$q   = trim((string) input('q', '', $_GET));
$cat = trim((string) input('kategori', '', $_GET));

$categories = [];
foreach ($posts as $p) {
    $c = trim((string) ($p['category'] ?? ''));
    if ($c !== '') {
        $categories[$c] = ($categories[$c] ?? 0) + 1;
    }
}
ksort($categories);

$filtered = array_values(array_filter($posts, static function (array $p) use ($q, $cat): bool {
    if ($cat !== '' && mb_strtolower((string) ($p['category'] ?? '')) !== mb_strtolower($cat)) {
        return false;
    }
    if ($q === '') {
        return true;
    }
    $hay = mb_strtolower(($p['title'] ?? '') . ' ' . ($p['excerpt'] ?? '') . ' ' . ($p['body'] ?? '') . ' ' . implode(' ', (array) ($p['tags'] ?? [])));
    return str_contains($hay, mb_strtolower($q));
}));

$pageNo = max(1, input_int('sayfa', 1));
$paged  = paginate($filtered, PER_PAGE_BLOG, $pageNo);

$baseUrl = base_url('blog.php') . '?' . http_build_query(array_filter(['q' => $q, 'kategori' => $cat]));

$page = [
    'title' => 'Blog',
    'desc'  => 'Yazılım geliştirme, SEO, dijital pazarlama ve ürün yönetimi üzerine sahadan çıkarılmış dersler ve pratik rehberler.',
];

require INC_PATH . '/layout/header.php';
?>

<section class="page-hero section--grid-bg">
    <span class="aurora aurora--2"></span>

    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <span>Blog</span>
            </nav>

            <span class="eyebrow"><?= icon('pen', 15) ?> <?= count($posts) ?> yazı</span>
            <h1>Sahadan çıkan <span class="gradient-text">dersler</span></h1>
            <p class="lead">Teori değil, gerçek projelerde karşılaştığımız problemler ve uyguladığımız çözümler. Kopyalayıp kullanabileceğiniz pratik bilgiler.</p>

            <form class="mt-6" method="get" action="<?= e(base_url('blog.php')) ?>" role="search"
                  style="display:flex;gap:10px;flex-wrap:wrap;max-width:560px">
                <input class="input" type="search" name="q" value="<?= e($q) ?>"
                       placeholder="Yazılarda ara…" aria-label="Blog içinde ara" style="flex:1;min-width:220px">
                <?php if ($cat !== ''): ?><input type="hidden" name="kategori" value="<?= e($cat) ?>"><?php endif; ?>
                <button class="btn btn--primary" type="submit"><?= icon('search', 17) ?><span>Ara</span></button>
            </form>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($categories): ?>
            <div class="filter-bar reveal">
                <a class="filter-btn<?= $cat === '' ? ' is-active' : '' ?>"
                   href="<?= e(base_url('blog.php') . ($q !== '' ? '?q=' . urlencode($q) : '')) ?>">
                    Tümü <span class="mono" style="opacity:.6">(<?= count($posts) ?>)</span>
                </a>
                <?php foreach ($categories as $c => $count): ?>
                    <a class="filter-btn<?= mb_strtolower($cat) === mb_strtolower($c) ? ' is-active' : '' ?>"
                       href="<?= e(base_url('blog.php') . '?' . http_build_query(array_filter(['kategori' => $c, 'q' => $q]))) ?>">
                        <?= e($c) ?> <span class="mono" style="opacity:.6">(<?= $count ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($q !== '' || $cat !== ''): ?>
            <p class="text-dim mb-6">
                <strong><?= $paged['total'] ?></strong> sonuç bulundu
                <?= $q !== '' ? ' — "' . e($q) . '"' : '' ?>
                <?= $cat !== '' ? ' — kategori: ' . e($cat) : '' ?>
            </p>
        <?php endif; ?>

        <?php if ($paged['items']): ?>
            <div class="grid grid--3">
                <?php foreach ($paged['items'] as $i => $post): ?>
                    <a class="card post-card reveal" data-delay="<?= ($i % 3) + 1 ?>"
                       href="<?= e(base_url('yazi.php?y=' . urlencode((string) $post['slug']))) ?>">
                        <div class="post-card__media">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= e(upload_url($post['image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="post-card__meta">
                            <span class="badge badge--accent"><?= e($post['category'] ?? '') ?></span>
                            <span><?= e(tr_date($post['date'] ?? '')) ?></span>
                            <span aria-hidden="true">·</span>
                            <span><?= read_time($post['body'] ?? '') ?> dk</span>
                        </div>
                        <h3><?= e($post['title']) ?></h3>
                        <p><?= e(str_limit($post['excerpt'] ?? '', 130)) ?></p>
                        <div class="post-card__foot"><span>Yazıyı oku</span><?= icon('arrow-right', 16) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?= pagination_html($paged, $baseUrl) ?>
        <?php else: ?>
            <div class="empty-state">
                <?= icon('search', 46) ?>
                <h3>Sonuç bulunamadı</h3>
                <p>Farklı bir arama terimi deneyin ya da tüm yazılara göz atın.</p>
                <a class="btn btn--outline mt-6" href="<?= e(base_url('blog.php')) ?>">Tüm yazılar</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require INC_PATH . '/layout/footer.php'; ?>
