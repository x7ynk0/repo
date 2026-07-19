<?php
require_once __DIR__ . '/includes/config.php';

$settings  = get_settings();
$faq       = get_faq();
$pageTitle = 'Sık Sorulan Sorular';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Sık Sorulan Sorular</h1>
        <p>Sipariş, ödeme, kargo ve iade süreçleriyle ilgili merak ettikleriniz.</p>
    </div>
</section>

<section class="products-section">
    <div class="container narrow">
        <?php if (empty($faq)): ?>
            <div class="empty-state">
                <h3>İçerik hazırlanıyor</h3>
                <p>Sık sorulan sorular kısa süre içinde bu sayfada yayınlanacaktır. Sorularınız için bizimle iletişime geçebilirsiniz.</p>
                <a class="btn btn-primary" href="iletisim.php">İletişime Geç</a>
            </div>
        <?php else: ?>
            <div class="faq-list">
                <?php foreach ($faq as $i => $f): ?>
                    <details class="faq-item" <?= $i === 0 ? 'open' : '' ?>>
                        <summary>
                            <span><?= e($f['question'] ?? '') ?></span>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                        </summary>
                        <div class="faq-answer"><p><?= nl2br(e($f['answer'] ?? '')) ?></p></div>
                    </details>
                <?php endforeach; ?>
            </div>

            <div class="faq-more">
                <p>Aradığınız cevabı bulamadınız mı?</p>
                <a class="btn btn-primary" href="iletisim.php">Bize Sorun</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
