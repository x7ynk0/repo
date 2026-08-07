<?php
/**
 * Sıkça Sorulan Sorular.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$faq = Store::all('faq', ['only_active' => true]);

$page = [
    'title' => 'Sıkça Sorulan Sorular',
    'desc'  => 'Fiyatlandırma, süreç, teslim süreleri, destek ve kaynak kod hakkında en sık sorulan soruların yanıtları.',
];

require INC_PATH . '/layout/header.php';
?>

<section class="page-hero section--grid-bg">
    <span class="aurora aurora--3"></span>

    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <span>SSS</span>
            </nav>

            <span class="eyebrow"><?= icon('help', 15) ?> <?= count($faq) ?> soru</span>
            <h1>Aklınıza takılan <span class="gradient-text">her şey</span></h1>
            <p class="lead">Müşterilerimizin en sık sorduğu soruları ve dürüst yanıtlarını burada topladık. Aradığınızı bulamazsanız doğrudan bize yazın.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container container--narrow">
        <?php if ($faq): ?>
            <div class="accordion reveal" data-single="0">
                <?php foreach ($faq as $i => $f): ?>
                    <div class="accordion__item<?= $i === 0 ? ' is-open' : '' ?>">
                        <button class="accordion__head" type="button">
                            <span><?= e($f['q']) ?></span>
                            <span class="accordion__icon"><?= icon('chevron-down', 17) ?></span>
                        </button>
                        <div class="accordion__panel">
                            <div class="accordion__body"><?= nl2br(e($f['a'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <?= icon('help', 46) ?>
                <h3>Henüz soru eklenmemiş</h3>
            </div>
        <?php endif; ?>

        <div class="info-box mt-7 reveal text-center">
            <h3>Sorunuzun cevabını bulamadınız mı?</h3>
            <p class="mt-4">Doğrudan sorun; teknik olsun ticari olsun, dürüst cevap veriyoruz. Ortalama yanıt süremiz 2 saat.</p>
            <div class="flex flex-wrap gap-3 mt-6" style="justify-content:center">
                <a class="btn btn--primary" href="<?= e(base_url('iletisim.php')) ?>" data-magnetic>
                    <span>Soru sor</span><?= icon('arrow-right', 16) ?>
                </a>
                <a class="btn btn--ghost" href="mailto:<?= e(setting('contact.email', '')) ?>">
                    <?= icon('mail', 16) ?><span><?= e(setting('contact.email', '')) ?></span>
                </a>
            </div>
        </div>
    </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => array_map(static fn($f) => [
        '@type' => 'Question',
        'name'  => $f['q'] ?? '',
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a'] ?? ''],
    ], $faq),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<?php require INC_PATH . '/layout/footer.php'; ?>
