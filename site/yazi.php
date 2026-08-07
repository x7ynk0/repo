<?php
/**
 * Blog yazı detayı.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (!setting('features.blog', true)) {
    redirect(base_url());
}

$slug = (string) input('y', '', $_GET);
$post = $slug !== '' ? Store::findBy('posts', 'slug', $slug) : null;

if ($post === null || empty($post['active'])) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

/* Görüntülenme sayacı (oturum başına bir kez) */
$viewKey = 'viewed_' . $post['id'];
if (empty($_SESSION[$viewKey])) {
    $_SESSION[$viewKey] = true;
    Store::update('posts', $post['id'], ['views' => (int) ($post['views'] ?? 0) + 1]);
    $post['views'] = (int) ($post['views'] ?? 0) + 1;
}

$all  = Store::all('posts', ['only_active' => true, 'sort' => 'date', 'dir' => 'desc']);
$ids  = array_map(static fn($p) => (string) $p['id'], $all);
$pos  = array_search((string) $post['id'], $ids, true);
$prev = $pos !== false && $pos > 0 ? $all[$pos - 1] : null;
$next = $pos !== false && $pos < count($all) - 1 ? $all[$pos + 1] : null;

$related = array_values(array_filter($all, static fn($p) =>
    (string) $p['id'] !== (string) $post['id']
    && mb_strtolower((string) ($p['category'] ?? '')) === mb_strtolower((string) ($post['category'] ?? ''))
));
if (count($related) < 3) {
    foreach ($all as $p) {
        if ((string) $p['id'] === (string) $post['id'] || in_array($p, $related, true)) continue;
        $related[] = $p;
        if (count($related) >= 3) break;
    }
}
$related = array_slice($related, 0, 3);

$shareUrl  = rawurlencode(base_url('yazi.php?y=' . urlencode((string) $post['slug'])));
$shareText = rawurlencode((string) $post['title']);

$page = [
    'title' => (string) $post['title'],
    'desc'  => (string) ($post['excerpt'] ?? ''),
    'image' => (string) ($post['image'] ?? ''),
    'type'  => 'article',
];

require INC_PATH . '/layout/header.php';
?>

<article>
<section class="page-hero section--grid-bg">
    <span class="aurora aurora--1"></span>

    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <a href="<?= e(base_url('blog.php')) ?>">Blog</a>
                <?= icon('chevron-right', 14) ?>
                <span><?= e(str_limit($post['title'], 42)) ?></span>
            </nav>

            <div class="flex flex-wrap gap-3 mb-4">
                <span class="badge badge--accent"><?= e($post['category'] ?? '') ?></span>
                <span class="badge"><?= icon('clock', 13) ?> <?= read_time($post['body'] ?? '') ?> dk okuma</span>
                <span class="badge"><?= icon('eye', 13) ?> <?= human_number($post['views'] ?? 0) ?> görüntülenme</span>
            </div>

            <h1><?= e($post['title']) ?></h1>
            <p class="lead"><?= e($post['excerpt'] ?? '') ?></p>

            <div class="flex gap-3 mt-6" style="align-items:center">
                <span class="avatar avatar--sm"><?= e(initials((string) ($post['author'] ?? 'Oxit'))) ?></span>
                <div>
                    <div style="font-weight:600;font-size:.92rem"><?= e($post['author'] ?? '') ?></div>
                    <div class="text-dim" style="font-size:.8rem"><?= e(tr_date($post['date'] ?? '')) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="split split--sidebar">
            <div class="reveal">
                <?php if (!empty($post['image'])): ?>
                    <div class="media-frame mb-7" style="aspect-ratio:16/9">
                        <img src="<?= e(upload_url($post['image'])) ?>" alt="<?= e($post['title']) ?>">
                    </div>
                <?php endif; ?>

                <div class="prose">
                    <?= rich_text((string) ($post['body'] ?? '')) ?>
                </div>

                <?php if (!empty($post['tags'])): ?>
                    <div class="flex flex-wrap gap-2 mt-7">
                        <?php foreach ((array) $post['tags'] as $t): ?>
                            <span class="tag"># <?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="flex flex-wrap gap-3 mt-6" style="padding-top:var(--sp-5);border-top:1px solid var(--line)">
                    <span class="text-dim" style="font-size:.88rem;align-self:center">Paylaş:</span>
                    <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener"
                       href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareText ?>">X</a>
                    <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener"
                       href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>">LinkedIn</a>
                    <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener"
                       href="https://wa.me/?text=<?= $shareText ?>%20<?= $shareUrl ?>">WhatsApp</a>
                    <button class="btn btn--ghost btn--sm" type="button"
                            data-copy="<?= e(base_url('yazi.php?y=' . urlencode((string) $post['slug']))) ?>"
                            title="Bağlantıyı kopyala"><?= icon('link', 15) ?><span>Bağlantıyı kopyala</span></button>
                </div>

                <nav class="flex-between flex-wrap gap-4 mt-7" style="padding-top:var(--sp-5);border-top:1px solid var(--line)" aria-label="Yazı gezinme">
                    <?php if ($prev): ?>
                        <a class="btn btn--ghost btn--sm" href="<?= e(base_url('yazi.php?y=' . urlencode((string) $prev['slug']))) ?>">
                            <?= icon('arrow-left', 15) ?><span><?= e(str_limit($prev['title'], 28)) ?></span>
                        </a>
                    <?php else: ?><span></span><?php endif; ?>
                    <?php if ($next): ?>
                        <a class="btn btn--ghost btn--sm" href="<?= e(base_url('yazi.php?y=' . urlencode((string) $next['slug']))) ?>">
                            <span><?= e(str_limit($next['title'], 28)) ?></span><?= icon('arrow-right', 15) ?>
                        </a>
                    <?php else: ?><span></span><?php endif; ?>
                </nav>
            </div>

            <aside class="sticky-side reveal reveal--right">
                <div class="info-box">
                    <h4><?= icon('rocket', 18) ?> Bu konuda yardım mı lazım?</h4>
                    <p class="mt-4" style="font-size:.92rem">Yazıda anlattığımız işlerin hepsini sizin için de yapıyoruz. 30 dakikalık ücretsiz görüşmede durumunuzu değerlendirelim.</p>
                    <a class="btn btn--primary btn--block mt-6" href="<?= e(base_url('iletisim.php')) ?>">
                        <span>Görüşme planla</span><?= icon('arrow-right', 16) ?>
                    </a>
                </div>

                <?php if ($related): ?>
                    <div class="info-box mt-6">
                        <h4><?= icon('pen', 18) ?> Benzer yazılar</h4>
                        <ul class="footer-links mt-4">
                            <?php foreach ($related as $r): ?>
                                <li><a href="<?= e(base_url('yazi.php?y=' . urlencode((string) $r['slug']))) ?>"><?= e(str_limit($r['title'], 54)) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>
</article>

<?php require INC_PATH . '/layout/footer.php'; ?>
