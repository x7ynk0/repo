<?php
/**
 * Referanslar / proje listesi (filtrelenebilir).
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$projects = Store::all('projects', ['only_active' => true]);

/* Kategori listesi */
$categories = [];
foreach ($projects as $p) {
    $cat = trim((string) ($p['category'] ?? ''));
    if ($cat !== '') {
        $categories[$cat] = ($categories[$cat] ?? 0) + 1;
    }
}
ksort($categories);

$pageNo = max(1, input_int('sayfa', 1));
$paged  = paginate($projects, PER_PAGE_PROJECT, $pageNo);

$page = [
    'title' => 'Referanslar',
    'desc'  => 'Tamamladığımız projeler ve elde edilen ölçülebilir sonuçlar. Web, mobil, e-ticaret, SEO ve yapay zekâ projelerinden vaka çalışmaları.',
];

require INC_PATH . '/layout/header.php';
?>

<section class="page-hero section--grid-bg">
    <span class="aurora aurora--2"></span>
    <span class="aurora aurora--3"></span>

    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <span>Referanslar</span>
            </nav>

            <span class="eyebrow"><?= icon('folder', 15) ?> <?= count($projects) ?> vaka çalışması</span>
            <h1>İşimizi <span class="gradient-text">rakamlarla</span> anlatıyoruz</h1>
            <p class="lead">Aşağıdaki her proje için problemi, uyguladığımız çözümü ve elde edilen ölçülebilir sonucu paylaşıyoruz. Güzel ekran görüntüsü değil, gerçek etki.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($categories): ?>
            <div class="filter-bar reveal" data-filter-group data-filter-target=".project-item" data-filter-empty="#filterEmpty">
                <button class="filter-btn is-active" type="button" data-filter="*">
                    Tümü <span class="mono" style="opacity:.6">(<?= count($projects) ?>)</span>
                </button>
                <?php foreach ($categories as $cat => $count): ?>
                    <button class="filter-btn" type="button" data-filter="<?= e(mb_strtolower($cat)) ?>">
                        <?= e($cat) ?> <span class="mono" style="opacity:.6">(<?= $count ?>)</span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid--auto-lg">
            <?php foreach ($paged['items'] as $i => $p): ?>
                <a class="project-card project-item reveal" data-delay="<?= ($i % 3) + 1 ?>"
                   data-category="<?= e(mb_strtolower((string) ($p['category'] ?? ''))) ?>"
                   href="<?= e(base_url('proje.php?p=' . urlencode((string) $p['slug']))) ?>">
                    <div class="project-card__media">
                        <span class="project-card__cat"><?= e($p['category'] ?? '') ?></span>
                        <span class="project-card__year"><?= e($p['year'] ?? '') ?></span>
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= e(upload_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="project-card__placeholder"><?= e(explode(' ', (string) $p['title'])[0]) ?></span>
                        <?php endif; ?>
                        <div class="project-card__overlay">
                            <span class="project-card__cta">Vaka çalışmasını oku <?= icon('arrow-right', 17) ?></span>
                        </div>
                    </div>

                    <div class="project-card__body">
                        <?php if (!empty($p['client'])): ?>
                            <span class="mono" style="font-size:.74rem;color:var(--text-dim)"><?= e($p['client']) ?></span>
                        <?php endif; ?>
                        <h3><?= e($p['title']) ?></h3>
                        <p><?= e(str_limit($p['excerpt'] ?? '', 120)) ?></p>

                        <?php if (!empty($p['tags'])): ?>
                            <div class="project-card__tags">
                                <?php foreach (array_slice((array) $p['tags'], 0, 4) as $t): ?>
                                    <span class="tag"><?= e($t) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($p['metrics'])): ?>
                            <div class="project-card__metrics">
                                <?php foreach (array_slice((array) $p['metrics'], 0, 3) as $m): ?>
                                    <div class="project-card__metric">
                                        <strong><?= e($m['value'] ?? '') ?></strong>
                                        <span><?= e($m['label'] ?? '') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div id="filterEmpty" hidden>
            <div class="empty-state mt-6">
                <?= icon('search', 46) ?>
                <h3>Bu kategoride proje bulunamadı</h3>
                <p>Farklı bir kategori seçmeyi deneyin.</p>
            </div>
        </div>

        <?php if (!$projects): ?>
            <div class="empty-state">
                <?= icon('folder', 46) ?>
                <h3>Henüz referans eklenmemiş</h3>
                <p>Yönetim panelinden referans projelerinizi ekleyebilirsiniz.</p>
            </div>
        <?php endif; ?>

        <?= pagination_html($paged, base_url('projeler.php')) ?>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('chart', 15) ?> Toplu bakış</span>
            <h2>Projelerimizin <span class="gradient-text">ortalama etkisi</span></h2>
        </div>

        <div class="stats-grid">
            <?php
            $impact = [
                ['v' => 187, 's' => '%', 'l' => 'Ortalama organik trafik artışı'],
                ['v' => 64,  's' => '%', 'l' => 'Ortalama dönüşüm iyileşmesi'],
                ['v' => 92,  's' => '',  'l' => 'Ortalama PageSpeed skoru'],
                ['v' => 100, 's' => '%', 'l' => 'Zamanında teslim oranı'],
            ];
            foreach ($impact as $i => $s): ?>
                <div class="stat reveal" data-delay="<?= $i + 1 ?>">
                    <div class="stat__value">
                        <span data-count="<?= $s['v'] ?>" data-suffix="<?= e($s['s']) ?>">0</span>
                    </div>
                    <div class="stat__label"><?= e($s['l']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require INC_PATH . '/layout/footer.php'; ?>
