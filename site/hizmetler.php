<?php
/**
 * Hizmetler listesi.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$services = Store::all('services', ['only_active' => true]);
$process  = Store::all('process', ['only_active' => true]);
$packages = Store::all('packages', ['only_active' => true]);

$page = [
    'title' => 'Hizmetler',
    'desc'  => 'Web sitesi geliştirme, mobil uygulama, e-ticaret, SEO, sosyal medya yönetimi, yapay zekâ entegrasyonu ve daha fazlası. ' . count($services) . ' farklı dijital hizmet.',
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
                <span>Hizmetler</span>
            </nav>

            <span class="eyebrow"><?= icon('layers', 15) ?> <?= count($services) ?> uzmanlık alanı</span>
            <h1>Dijital ürününüzün <span class="gradient-text">her katmanı</span> için hizmet</h1>
            <p class="lead">Tasarımdan koda, altyapıdan büyümeye. Bir işi yapabilecek 5 farklı tedarikçiyle uğraşmak yerine, tek muhatapla tüm süreci yönetin.</p>

            <div class="hero__actions mt-6">
                <a class="btn btn--primary btn--lg btn--shine" href="<?= e(base_url('iletisim.php')) ?>" data-magnetic>
                    <span>İhtiyacımı anlatayım</span><?= icon('arrow-right', 18) ?>
                </a>
                <a class="btn btn--ghost btn--lg" href="#hizmet-listesi">
                    <?= icon('grid', 17) ?><span>Hizmetleri gör</span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section" id="hizmet-listesi">
    <div class="container">
        <div class="grid grid--auto-lg">
            <?php foreach ($services as $i => $s): ?>
                <article class="card service-card reveal" data-delay="<?= ($i % 3) + 1 ?>">
                    <span class="service-card__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <div class="service-card__icon"><?= icon((string) ($s['icon'] ?? 'code'), 26) ?></div>

                    <h3><?= e($s['title']) ?></h3>
                    <p><?= e($s['excerpt'] ?? '') ?></p>

                    <?php if (!empty($s['features'])): ?>
                        <ul class="check-list mt-4" style="font-size:.88rem">
                            <?php foreach (array_slice((array) $s['features'], 0, 4) as $f): ?>
                                <li><?= icon('check', 16) ?> <?= e($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="service-card__meta">
                        <?php if (!empty($s['price_from'])): ?>
                            <span class="badge badge--accent"><?= e(money($s['price_from'])) ?>'den başlayan</span>
                        <?php endif; ?>
                        <?php if (!empty($s['duration'])): ?>
                            <span class="badge"><?= icon('clock', 13) ?> <?= e($s['duration']) ?></span>
                        <?php endif; ?>
                    </div>

                    <a class="service-card__link" href="<?= e(base_url('hizmet.php?s=' . urlencode((string) $s['slug']))) ?>">
                        Detaylı bilgi <?= icon('arrow-right', 16) ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!$services): ?>
            <div class="empty-state">
                <?= icon('inbox', 46) ?>
                <h3>Henüz hizmet eklenmemiş</h3>
                <p>Yönetim panelinden hizmet ekleyebilirsiniz.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($process): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('refresh', 15) ?> Çalışma sürecimiz</span>
            <h2>Hangi hizmeti alırsanız alın, <span class="gradient-text">süreç aynı netlikte</span></h2>
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

<section class="section">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('bulb', 15) ?> Emin değil misiniz?</span>
            <h2>Hangi hizmete ihtiyacınız olduğunu <span class="gradient-text">birlikte bulalım</span></h2>
            <p>Çoğu müşterimiz bize "web sitesi lazım" diye gelir; görüşme sonunda asıl ihtiyacın farklı bir şey olduğu ortaya çıkar. Ücretsiz keşif görüşmesinde doğru soruları sormak bizim işimiz.</p>
        </div>

        <div class="grid grid--3">
            <?php
            $helpItems = [
                ['icon' => 'search', 'title' => '30 dakikalık keşif', 'text' => 'İhtiyacınızı, hedefinizi ve mevcut durumunuzu konuşuyoruz. Satış konuşması değil, teknik danışmanlık.'],
                ['icon' => 'file', 'title' => 'Yazılı yol haritası', 'text' => 'Görüşmenin ardından size hangi hizmetlerin hangi sırayla gerektiğini yazılı olarak iletiyoruz.'],
                ['icon' => 'target', 'title' => 'Bağlayıcı teklif', 'text' => 'Kapsam netleştiğinde sabit fiyatlı, tarih taahhütlü teklif hazırlıyoruz. Karar tamamen sizin.'],
            ];
            foreach ($helpItems as $i => $h): ?>
                <div class="card reveal" data-delay="<?= $i + 1 ?>">
                    <div class="service-card__icon"><?= icon($h['icon'], 24) ?></div>
                    <h3><?= e($h['title']) ?></h3>
                    <p><?= e($h['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require INC_PATH . '/layout/footer.php'; ?>
