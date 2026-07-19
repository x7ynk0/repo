<?php
require_once __DIR__ . '/includes/config.php';

$settings  = get_settings();
$aboutText = trim((string)$settings['about']) !== '' ? $settings['about'] : default_about_text();
$pageTitle = 'Hakkımızda';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Hakkımızda</h1>
        <p>Çeyrek asırdır aynı ilkeyle çalışıyoruz: doğru parça, dürüst fiyat, zamanında teslimat.</p>
    </div>
</section>

<section class="products-section">
    <div class="container narrow">
        <article class="content-card about-content">
            <?php foreach (preg_split('/\n{2,}/', $aboutText) as $paragraph): ?>
                <?php if (trim($paragraph) !== ''): ?><p><?= nl2br(e(trim($paragraph))) ?></p><?php endif; ?>
            <?php endforeach; ?>
        </article>

        <div class="about-cta">
            <a class="btn btn-primary" href="index.php#urunler">Ürünlerimize Göz Atın</a>
            <a class="btn btn-ghost" href="iletisim.php">Bize Ulaşın</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
