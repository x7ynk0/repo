<?php
/**
 * Proje (vaka çalışması) detay sayfası.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$slug    = (string) input('p', '', $_GET);
$project = $slug !== '' ? Store::findBy('projects', 'slug', $slug) : null;

if ($project === null || empty($project['active'])) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$all  = Store::all('projects', ['only_active' => true]);
$ids  = array_map(static fn($p) => (string) $p['id'], $all);
$pos  = array_search((string) $project['id'], $ids, true);
$prev = $pos !== false && $pos > 0 ? $all[$pos - 1] : null;
$next = $pos !== false && $pos < count($all) - 1 ? $all[$pos + 1] : null;

$related = array_values(array_filter($all, static fn($p) =>
    (string) $p['id'] !== (string) $project['id']
    && mb_strtolower((string) ($p['category'] ?? '')) === mb_strtolower((string) ($project['category'] ?? ''))
));
if (count($related) < 3) {
    foreach ($all as $p) {
        if ((string) $p['id'] === (string) $project['id']) continue;
        if (in_array($p, $related, true)) continue;
        $related[] = $p;
        if (count($related) >= 3) break;
    }
}
$related = array_slice($related, 0, 3);

$page = [
    'title' => (string) $project['title'],
    'desc'  => (string) ($project['excerpt'] ?? ''),
    'image' => (string) ($project['image'] ?? ''),
    'type'  => 'article',
];

require INC_PATH . '/layout/header.php';
?>

<section class="page-hero section--grid-bg">
    <span class="aurora aurora--1"></span>

    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <a href="<?= e(base_url('projeler.php')) ?>">Referanslar</a>
                <?= icon('chevron-right', 14) ?>
                <span><?= e($project['title']) ?></span>
            </nav>

            <div class="flex flex-wrap gap-3 mb-4">
                <span class="badge badge--accent"><?= e($project['category'] ?? '') ?></span>
                <?php if (!empty($project['year'])): ?>
                    <span class="badge"><?= icon('clock', 13) ?> <?= e($project['year']) ?></span>
                <?php endif; ?>
                <?php if (!empty($project['client'])): ?>
                    <span class="badge"><?= icon('user', 13) ?> <?= e($project['client']) ?></span>
                <?php endif; ?>
            </div>

            <h1><?= e($project['title']) ?></h1>
            <p class="lead"><?= e($project['excerpt'] ?? '') ?></p>
        </div>
    </div>
</section>

<?php if (!empty($project['metrics'])): ?>
<section class="section section--tight">
    <div class="container">
        <div class="stats-grid">
            <?php foreach ((array) $project['metrics'] as $i => $m): ?>
                <div class="stat reveal" data-delay="<?= $i + 1 ?>">
                    <div class="stat__value"><?= e($m['value'] ?? '') ?></div>
                    <div class="stat__label"><?= e($m['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <?php if (!empty($project['image'])): ?>
            <div class="media-frame reveal mb-7" style="aspect-ratio:16/9" data-parallax="0.05">
                <img src="<?= e(upload_url($project['image'])) ?>" alt="<?= e($project['title']) ?>">
            </div>
        <?php endif; ?>

        <div class="split split--sidebar">
            <div class="reveal">
                <div class="prose">
                    <?= rich_text((string) ($project['body'] ?? '')) ?>
                </div>

                <?php if (!empty($project['gallery'])): ?>
                    <div class="grid grid--2 mt-7">
                        <?php foreach ((array) $project['gallery'] as $g): ?>
                            <div class="media-frame" style="aspect-ratio:16/10">
                                <img src="<?= e(upload_url($g)) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="sticky-side reveal reveal--right">
                <div class="info-box">
                    <h4><?= icon('file', 18) ?> Proje künyesi</h4>
                    <ul class="info-list mt-4">
                        <li><span>Müşteri</span><span><?= e($project['client'] ?? '—') ?></span></li>
                        <li><span>Kategori</span><span><?= e($project['category'] ?? '—') ?></span></li>
                        <li><span>Yıl</span><span><?= e($project['year'] ?? '—') ?></span></li>
                    </ul>

                    <?php if (!empty($project['tags'])): ?>
                        <div class="mt-6">
                            <span class="footer-title">Kullanılan teknolojiler</span>
                            <div class="flex flex-wrap gap-2 mt-4">
                                <?php foreach ((array) $project['tags'] as $t): ?>
                                    <span class="tag"><?= e($t) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($project['url'])): ?>
                        <a class="btn btn--ghost btn--block mt-6" href="<?= e($project['url']) ?>" target="_blank" rel="noopener">
                            <?= icon('external', 16) ?><span>Canlı siteyi gör</span>
                        </a>
                    <?php endif; ?>

                    <a class="btn btn--primary btn--block mt-4" href="<?= e(base_url('iletisim.php')) ?>">
                        <span>Benzer bir proje istiyorum</span><?= icon('arrow-right', 16) ?>
                    </a>
                </div>
            </aside>
        </div>

        <!-- Önceki / sonraki -->
        <nav class="flex-between flex-wrap gap-4 mt-7" style="padding-top:var(--sp-6);border-top:1px solid var(--line)" aria-label="Proje gezinme">
            <?php if ($prev): ?>
                <a class="btn btn--ghost" href="<?= e(base_url('proje.php?p=' . urlencode((string) $prev['slug']))) ?>">
                    <?= icon('arrow-left', 16) ?><span><?= e(str_limit($prev['title'], 30)) ?></span>
                </a>
            <?php else: ?><span></span><?php endif; ?>

            <a class="btn btn--outline" href="<?= e(base_url('projeler.php')) ?>"><?= icon('grid', 16) ?><span>Tüm projeler</span></a>

            <?php if ($next): ?>
                <a class="btn btn--ghost" href="<?= e(base_url('proje.php?p=' . urlencode((string) $next['slug']))) ?>">
                    <span><?= e(str_limit($next['title'], 30)) ?></span><?= icon('arrow-right', 16) ?>
                </a>
            <?php else: ?><span></span><?php endif; ?>
        </nav>
    </div>
</section>

<?php if ($related): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow"><?= icon('layers', 15) ?> Benzer çalışmalar</span>
            <h2>Bunlar da ilginizi çekebilir</h2>
        </div>

        <div class="grid grid--3">
            <?php foreach ($related as $i => $r): ?>
                <a class="project-card reveal" data-delay="<?= $i + 1 ?>"
                   href="<?= e(base_url('proje.php?p=' . urlencode((string) $r['slug']))) ?>">
                    <div class="project-card__media">
                        <span class="project-card__cat"><?= e($r['category'] ?? '') ?></span>
                        <?php if (!empty($r['image'])): ?>
                            <img src="<?= e(upload_url($r['image'])) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="project-card__placeholder"><?= e(explode(' ', (string) $r['title'])[0]) ?></span>
                        <?php endif; ?>
                        <div class="project-card__overlay"><span class="project-card__cta">İncele <?= icon('arrow-right', 17) ?></span></div>
                    </div>
                    <div class="project-card__body">
                        <h3><?= e($r['title']) ?></h3>
                        <p><?= e(str_limit($r['excerpt'] ?? '', 100)) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require INC_PATH . '/layout/footer.php'; ?>
