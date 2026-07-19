<?php
require_once __DIR__ . '/includes/config.php';

$settings     = get_settings();
$bankAccounts = get_bank_accounts($settings);
$pageTitle    = 'Ödeme Bilgileri';
require __DIR__ . '/includes/header.php';
?>

<section class="products-section">
    <div class="container narrow">
        <div class="section-head">
            <h2>Ödeme Bilgileri</h2>
        </div>

        <?php if (empty($bankAccounts)): ?>
            <div class="empty-state">
                <h3>Ödeme bilgileri güncelleniyor</h3>
                <p>Ödeme seçeneklerimiz kısa süre içinde bu sayfada yayınlanacaktır. Dilerseniz bizimle iletişime geçebilirsiniz.</p>
                <a class="btn btn-primary" href="iletisim.php">İletişime Geç</a>
            </div>
        <?php else: ?>
            <div class="payment-page-card">
                <div class="payment-box-head">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path></svg>
                    <h3>Havale / EFT ile Ödeme</h3>
                </div>
                <p class="payment-note">Ödemenizi aşağıdaki banka hesaplarımızdan dilediğinize yapabilirsiniz. Ödeme açıklamasına <strong>ad-soyad</strong> ve <strong>sipariş verdiğiniz parça bilgisini</strong> yazmanızı rica ederiz. Dekontunuzu telefon veya WhatsApp üzerinden ilettiğinizde siparişiniz işleme alınır.</p>

                <?php foreach ($bankAccounts as $acc): ?>
                    <div class="iban-row">
                        <div class="iban-meta">
                            <?php if (($acc['bank'] ?? '') !== ''): ?><span class="iban-bank"><?= e($acc['bank']) ?></span><?php endif; ?>
                            <span class="iban-holder"><?= e($acc['holder'] ?? '') ?></span>
                            <span class="iban-number"><?= e(format_iban($acc['iban'])) ?></span>
                        </div>
                        <button type="button" class="btn btn-ghost btn-copy" data-copy="<?= e(normalize_iban($acc['iban'])) ?>">Kopyala</button>
                    </div>
                <?php endforeach; ?>

                <div class="payment-steps">
                    <h4>Nasıl çalışır?</h4>
                    <ol>
                        <li>Sipariş vermek istediğiniz ürünü telefon veya WhatsApp üzerinden bize bildirin.</li>
                        <li>Ödemenizi yukarıdaki hesaba havale/EFT ile gönderin.</li>
                        <li>Dekontunuzu ilettiğinizde siparişiniz onaylanır ve kargoya hazırlanır.</li>
                    </ol>
                </div>

                <?php if ($settings['phone'] !== '' || $settings['whatsapp'] !== ''): ?>
                <div class="detail-contact">
                    <?php if ($settings['phone'] !== ''): ?>
                        <a class="btn btn-primary" href="tel:<?= e(preg_replace('/\s+/', '', $settings['phone'])) ?>">Hemen Ara: <?= e($settings['phone']) ?></a>
                    <?php endif; ?>
                    <?php if ($settings['whatsapp'] !== ''): ?>
                        <a class="btn btn-whatsapp" href="https://wa.me/<?= e(preg_replace('/\D+/', '', $settings['whatsapp'])) ?>" target="_blank" rel="noopener">WhatsApp ile Yaz</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
