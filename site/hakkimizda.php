<?php
/**
 * Hakkımızda sayfası.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$about   = (array) setting('about', []);
$stats   = (array) setting('stats', []);
$skills  = Store::all('skills', ['only_active' => true]);
$process = Store::all('process', ['only_active' => true]);
$clients = Store::all('clients', ['only_active' => true]);

/* Yetenekleri gruplandır */
$grouped = [];
foreach ($skills as $s) {
    $grouped[(string) ($s['group'] ?? 'Genel')][] = $s;
}

$page = [
    'title' => 'Hakkımızda',
    'desc'  => str_limit((string) ($about['body'] ?? setting('site.description', '')), 160),
];

require INC_PATH . '/layout/header.php';
?>

<section class="page-hero section--grid-bg">
    <span class="aurora aurora--1"></span>
    <span class="aurora aurora--2"></span>

    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <span>Hakkımızda</span>
            </nav>

            <span class="eyebrow"><?= icon('users', 15) ?> <?= e($about['subtitle'] ?? 'Hakkımızda') ?></span>
            <h1><?= e($about['title'] ?? 'Hakkımızda') ?></h1>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="split">
            <div class="reveal reveal--left">
                <div class="prose">
                    <?= rich_text((string) ($about['body'] ?? '')) ?>
                </div>

                <?php if (!empty($about['highlights'])): ?>
                    <ul class="check-list mt-6">
                        <?php foreach ((array) $about['highlights'] as $h): ?>
                            <li><?= icon('check-circle', 18) ?> <?= e($h) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="flex flex-wrap gap-3 mt-7">
                    <a class="btn btn--primary btn--lg" href="<?= e(base_url('iletisim.php')) ?>" data-magnetic>
                        <span>Bizimle çalışın</span><?= icon('arrow-right', 18) ?>
                    </a>
                    <a class="btn btn--ghost btn--lg" href="<?= e(base_url('projeler.php')) ?>">
                        <?= icon('folder', 17) ?><span>Çalışmalarımız</span>
                    </a>
                </div>
            </div>

            <div class="reveal reveal--right">
                <div class="media-frame" data-tilt="5">
                    <?php if (!empty($about['image'])): ?>
                        <img src="<?= e(upload_url($about['image'])) ?>" alt="<?= e($about['title'] ?? '') ?>">
                    <?php else: ?>
                        <div class="media-frame__deco"><?= icon('code', 78) ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($stats): ?>
                    <div class="stats-grid mt-5" style="grid-template-columns:repeat(2,1fr)">
                        <?php foreach (array_slice($stats, 0, 4) as $i => $s): ?>
                            <div class="stat">
                                <div class="stat__value">
                                    <span data-count="<?= e((string) ($s['value'] ?? 0)) ?>" data-suffix="<?= e($s['suffix'] ?? '') ?>">0</span>
                                </div>
                                <div class="stat__label"><?= e($s['label'] ?? '') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Değerler -->
<section class="section section--alt">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('award', 15) ?> Değerlerimiz</span>
            <h2>Bizi <span class="gradient-text">farklı kılan</span> ilkeler</h2>
            <p>Bunlar duvara asılan sloganlar değil; her projede uyguladığımız, gerektiğinde işi kaybetmeyi göze aldığımız kurallardır.</p>
        </div>

        <div class="grid grid--3">
            <?php
            $values = [
                ['icon' => 'target', 'title' => 'Dürüst kapsam', 'text' => 'İhtiyacınız olmayan bir hizmeti satmayız. Bütçenizin daha küçük bir çözümle çözüleceğini düşünüyorsak bunu söyleriz.'],
                ['icon' => 'zap', 'title' => 'Mühendislik disiplini', 'text' => 'Hızlı teslim için kalite feda edilmez. Test, kod incelemesi ve dokümantasyon pazarlık konusu değildir.'],
                ['icon' => 'users', 'title' => 'Ortak sorumluluk', 'text' => 'Proje bizim de projemizdir. Bir şey ters gittiğinde parmak göstermek yerine çözüme odaklanırız.'],
                ['icon' => 'bulb', 'title' => 'Sürekli öğrenme', 'text' => 'Her ekip üyesi haftalık öğrenme zamanına sahiptir. Dünkü en iyi yöntem, bugünün standardı olmayabilir.'],
                ['icon' => 'shield', 'title' => 'Veri sorumluluğu', 'text' => 'Kullanıcılarınızın verisi emanettir. Güvenlik ve gizlilik, sonradan eklenen bir özellik değil, tasarımın parçasıdır.'],
                ['icon' => 'chart', 'title' => 'Ölçülebilir sonuç', 'text' => '"Güzel oldu" bir başarı ölçütü değildir. Her projede hangi sayının değişeceğini baştan belirleriz.'],
            ];
            foreach ($values as $i => $v): ?>
                <article class="card reveal" data-delay="<?= ($i % 3) + 1 ?>">
                    <div class="service-card__icon"><?= icon($v['icon'], 24) ?></div>
                    <h3><?= e($v['title']) ?></h3>
                    <p><?= e($v['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Uzmanlıklar -->
<?php if ($grouped): ?>
<section class="section">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('cpu', 15) ?> Uzmanlık haritası</span>
            <h2>Ne yapabildiğimizi <span class="gradient-text">açıkça gösteriyoruz</span></h2>
        </div>

        <div class="grid grid--2">
            <?php foreach ($grouped as $group => $items): ?>
                <div class="info-box reveal">
                    <h4><?= icon('terminal', 18) ?> <?= e($group) ?></h4>
                    <div class="mt-4">
                        <?php foreach ($items as $i => $sk): ?>
                            <div class="skill">
                                <div class="skill__head">
                                    <span class="skill__name"><?= e($sk['name']) ?></span>
                                    <span class="skill__val"><?= (int) ($sk['level'] ?? 0) ?>%</span>
                                </div>
                                <div class="skill__bar">
                                    <span class="skill__fill" data-level="<?= (int) ($sk['level'] ?? 0) ?>" data-delay="<?= $i ?>"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Süreç -->
<?php if ($process): ?>
<section class="section section--alt">
    <div class="container container--narrow">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('refresh', 15) ?> Çalışma biçimimiz</span>
            <h2>Adım adım ne oluyor?</h2>
        </div>

        <div class="timeline">
            <?php foreach ($process as $i => $p): ?>
                <div class="timeline-item reveal" data-delay="<?= ($i % 4) + 1 ?>">
                    <div class="timeline-item__dot"><?= icon((string) ($p['icon'] ?? 'zap'), 22) ?></div>
                    <div class="timeline-item__body">
                        <span class="timeline-item__step">Adım <?= (int) ($p['step'] ?? $i + 1) ?> · <?= e($p['duration'] ?? '') ?></span>
                        <h3><?= e($p['title']) ?></h3>
                        <p><?= e($p['body'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($clients): ?>
<section class="section">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('users', 15) ?> Güven</span>
            <h2>Bize güvenen <span class="gradient-text">markalar</span></h2>
        </div>

        <div class="grid grid--auto" style="--min:180px">
            <?php foreach ($clients as $i => $c): ?>
                <div class="card text-center reveal" data-delay="<?= ($i % 4) + 1 ?>" style="align-items:center;padding:var(--sp-5)">
                    <?php if (!empty($c['logo'])): ?>
                        <img src="<?= e(upload_url($c['logo'])) ?>" alt="<?= e($c['name']) ?>" loading="lazy" style="max-height:44px;margin-inline:auto">
                    <?php else: ?>
                        <strong style="font-family:var(--font-display);font-size:1.05rem"><?= e($c['name']) ?></strong>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require INC_PATH . '/layout/footer.php'; ?>
