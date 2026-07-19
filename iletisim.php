<?php
require_once __DIR__ . '/includes/config.php';

$settings  = get_settings();
$pageTitle = 'İletişim';
require __DIR__ . '/includes/header.php';
?>

<section class="products-section">
    <div class="container">
        <div class="section-head">
            <h2>İletişim</h2>
        </div>

        <div class="contact-grid">
            <div class="contact-card">
                <h3>İletişim Bilgileri</h3>
                <ul class="contact-list big">
                    <?php if ($settings['phone'] !== ''): ?>
                        <li><strong>Telefon:</strong> <a href="tel:<?= e(preg_replace('/\s+/', '', $settings['phone'])) ?>"><?= e($settings['phone']) ?></a></li>
                    <?php endif; ?>
                    <?php if ($settings['whatsapp'] !== ''): ?>
                        <li><strong>WhatsApp:</strong> <a href="https://wa.me/<?= e(preg_replace('/\D+/', '', $settings['whatsapp'])) ?>" target="_blank" rel="noopener"><?= e($settings['whatsapp']) ?></a></li>
                    <?php endif; ?>
                    <?php if ($settings['email'] !== ''): ?>
                        <li><strong>E-posta:</strong> <a href="mailto:<?= e($settings['email']) ?>"><?= e($settings['email']) ?></a></li>
                    <?php endif; ?>
                    <?php if ($settings['address'] !== ''): ?>
                        <li><strong>Adres:</strong><br><?= nl2br(e($settings['address'])) ?></li>
                    <?php endif; ?>
                </ul>
                <?php if ($settings['phone'] === '' && $settings['whatsapp'] === '' && $settings['email'] === '' && $settings['address'] === ''): ?>
                    <p class="muted">İletişim kanallarımız kısa süre içinde bu sayfada yayınlanacaktır.</p>
                <?php endif; ?>
            </div>

            <div class="contact-card">
                <h3>Çalışma Prensibimiz</h3>
                <p>Sipariş öncesi parça uyumluluğunu birlikte doğrular, ödemeniz onaylandığında siparişinizi hızla kargoya teslim ederiz. Merak ettikleriniz için <a class="inline-link" href="sss.php">Sık Sorulan Sorular</a> sayfamıza göz atabilir, firmamızı daha yakından tanımak için <a class="inline-link" href="hakkimizda.php">Hakkımızda</a> sayfamızı ziyaret edebilirsiniz.</p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
