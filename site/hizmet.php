<?php
/**
 * Hizmet detay sayfası.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$slug    = (string) input('s', '', $_GET);
$service = $slug !== '' ? Store::findBy('services', 'slug', $slug) : null;

if ($service === null || empty($service['active'])) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$all     = Store::all('services', ['only_active' => true]);
$related = array_values(array_filter($all, static fn($s) => (string) $s['id'] !== (string) $service['id']));
shuffle($related);
$related = array_slice($related, 0, 3);

$projects = array_slice(Store::all('projects', ['only_active' => true]), 0, 3);
$faq      = array_slice(Store::all('faq', ['only_active' => true]), 0, 5);

$page = [
    'title' => (string) $service['title'],
    'desc'  => (string) ($service['excerpt'] ?? ''),
    'image' => (string) ($service['image'] ?? ''),
    'type'  => 'article',
];

require INC_PATH . '/layout/header.php';
?>

<section class="page-hero section--grid-bg">
    <span class="aurora aurora--1"></span>
    <span class="aurora aurora--3"></span>

    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <a href="<?= e(base_url('hizmetler.php')) ?>">Hizmetler</a>
                <?= icon('chevron-right', 14) ?>
                <span><?= e($service['title']) ?></span>
            </nav>

            <div class="flex gap-4 mb-4" style="align-items:center">
                <span class="service-card__icon" style="margin-bottom:0"><?= icon((string) ($service['icon'] ?? 'code'), 26) ?></span>
                <?php if (!empty($service['featured'])): ?>
                    <span class="badge badge--accent"><?= icon('star', 13) ?> Öne çıkan hizmet</span>
                <?php endif; ?>
            </div>

            <h1><?= e($service['title']) ?></h1>
            <p class="lead"><?= e($service['excerpt'] ?? '') ?></p>

            <div class="flex flex-wrap gap-3 mt-6">
                <?php if (!empty($service['price_from'])): ?>
                    <span class="badge badge--accent" style="padding:9px 16px;font-size:.84rem">
                        <?= icon('gift', 15) ?> <?= e(money($service['price_from'])) ?>'den başlayan fiyatlarla
                    </span>
                <?php endif; ?>
                <?php if (!empty($service['duration'])): ?>
                    <span class="badge" style="padding:9px 16px;font-size:.84rem">
                        <?= icon('clock', 15) ?> Süre: <?= e($service['duration']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="split split--sidebar">
            <div class="reveal">
                <?php if (!empty($service['image'])): ?>
                    <div class="media-frame mb-6" style="aspect-ratio:16/9">
                        <img src="<?= e(upload_url($service['image'])) ?>" alt="<?= e($service['title']) ?>">
                    </div>
                <?php endif; ?>

                <div class="prose">
                    <?= rich_text((string) ($service['body'] ?? '')) ?>
                </div>

                <?php if (!empty($service['features'])): ?>
                    <div class="mt-7">
                        <h2 class="mb-6">Bu hizmete neler dahil?</h2>
                        <div class="grid grid--2">
                            <?php foreach ((array) $service['features'] as $i => $f): ?>
                                <div class="feature-item reveal" data-delay="<?= ($i % 4) + 1 ?>" style="padding:0;margin-bottom:var(--sp-4)">
                                    <span class="feature-item__ico"><?= icon('check', 19) ?></span>
                                    <div><h4 style="font-size:.98rem;margin:0"><?= e($f) ?></h4></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-note mt-7">
                    <?= icon('info', 18) ?>
                    <span>Bu hizmeti tek başına ya da diğer hizmetlerimizle birleştirerek alabilirsiniz. Paket halinde alınan hizmetlerde indirim uygulanır.</span>
                </div>
            </div>

            <aside class="sticky-side reveal reveal--right">
                <div class="info-box">
                    <h4><?= icon('zap', 18) ?> Hızlı bilgi</h4>
                    <ul class="info-list mt-4">
                        <li><span>Başlangıç fiyatı</span><span><?= !empty($service['price_from']) ? e(money($service['price_from'])) : 'Görüşmeye bağlı' ?></span></li>
                        <li><span>Tahmini süre</span><span><?= e($service['duration'] ?? '—') ?></span></li>
                        <li><span>Destek</span><span>3 ay ücretsiz</span></li>
                        <li><span>Kaynak kod</span><span>Size ait</span></li>
                        <li><span>Sözleşme</span><span>Sabit fiyat</span></li>
                    </ul>

                    <a class="btn btn--primary btn--block mt-6"
                       href="<?= e(base_url('iletisim.php?hizmet=' . urlencode((string) $service['title']))) ?>">
                        <span>Bu hizmet için teklif al</span><?= icon('arrow-right', 16) ?>
                    </a>
                    <a class="btn btn--ghost btn--block mt-4" href="mailto:<?= e(setting('contact.email', '')) ?>">
                        <?= icon('mail', 16) ?><span>Soru sor</span>
                    </a>
                </div>

                <div class="info-box mt-6">
                    <h4><?= icon('layers', 18) ?> Diğer hizmetler</h4>
                    <ul class="footer-links mt-4">
                        <?php foreach ($related as $r): ?>
                            <li><a href="<?= e(base_url('hizmet.php?s=' . urlencode((string) $r['slug']))) ?>"><?= e($r['title']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="<?= e(base_url('hizmetler.php')) ?>"><strong>Tüm hizmetler →</strong></a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php if ($projects): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('folder', 15) ?> İlgili çalışmalar</span>
            <h2>Bu alanda <span class="gradient-text">neler yaptık?</span></h2>
        </div>

        <div class="grid grid--3">
            <?php foreach ($projects as $i => $p): ?>
                <a class="project-card reveal" data-delay="<?= $i + 1 ?>"
                   href="<?= e(base_url('proje.php?p=' . urlencode((string) $p['slug']))) ?>">
                    <div class="project-card__media">
                        <span class="project-card__cat"><?= e($p['category'] ?? '') ?></span>
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= e(upload_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="project-card__placeholder"><?= e(explode(' ', (string) $p['title'])[0]) ?></span>
                        <?php endif; ?>
                        <div class="project-card__overlay">
                            <span class="project-card__cta">İncele <?= icon('arrow-right', 17) ?></span>
                        </div>
                    </div>
                    <div class="project-card__body">
                        <h3><?= e($p['title']) ?></h3>
                        <p><?= e(str_limit($p['excerpt'] ?? '', 100)) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($faq): ?>
<section class="section">
    <div class="container container--narrow">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('help', 15) ?> Sık sorulanlar</span>
            <h2>Merak edilenler</h2>
        </div>

        <div class="accordion reveal" data-single="1">
            <?php foreach ($faq as $i => $f): ?>
                <div class="accordion__item<?= $i === 0 ? ' is-open' : '' ?>">
                    <button class="accordion__head" type="button">
                        <span><?= e($f['q']) ?></span>
                        <span class="accordion__icon"><?= icon('chevron-down', 17) ?></span>
                    </button>
                    <div class="accordion__panel">
                        <div class="accordion__body"><?= e($f['a']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require INC_PATH . '/layout/footer.php'; ?>
