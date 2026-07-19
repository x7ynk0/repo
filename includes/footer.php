<?php /** @var array $settings */ ?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4><?= e($settings['site_title']) ?></h4>
            <?php if ($settings['about'] !== ''): ?>
                <p><?= nl2br(e(mb_substr($settings['about'], 0, 260))) ?></p>
            <?php else: ?>
                <p><?= e($settings['slogan']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <h4>Hızlı Erişim</h4>
            <ul>
                <li><a href="index.php">Anasayfa</a></li>
                <li><a href="index.php#urunler">Ürünler</a></li>
                <?php if (count(get_bank_accounts($settings)) > 0): ?><li><a href="odeme.php">Ödeme Bilgileri</a></li><?php endif; ?>
                <li><a href="iletisim.php">İletişim</a></li>
            </ul>
        </div>
        <?php if ($settings['phone'] !== '' || $settings['whatsapp'] !== '' || $settings['email'] !== '' || $settings['address'] !== ''): ?>
        <div>
            <h4>İletişim</h4>
            <ul class="contact-list">
                <?php if ($settings['phone'] !== ''): ?><li>Telefon: <a href="tel:<?= e(preg_replace('/\s+/', '', $settings['phone'])) ?>"><?= e($settings['phone']) ?></a></li><?php endif; ?>
                <?php if ($settings['whatsapp'] !== ''): ?><li>WhatsApp: <a href="https://wa.me/<?= e(preg_replace('/\D+/', '', $settings['whatsapp'])) ?>" target="_blank" rel="noopener"><?= e($settings['whatsapp']) ?></a></li><?php endif; ?>
                <?php if ($settings['email'] !== ''): ?><li>E-posta: <a href="mailto:<?= e($settings['email']) ?>"><?= e($settings['email']) ?></a></li><?php endif; ?>
                <?php if ($settings['address'] !== ''): ?><li><?= nl2br(e($settings['address'])) ?></li><?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <?php if ($settings['footer_text'] !== ''): ?>
                <span><?= e($settings['footer_text']) ?></span>
            <?php else: ?>
                <span>© <?= date('Y') ?> <?= e($settings['site_title']) ?> — Tüm hakları saklıdır.</span>
            <?php endif; ?>
        </div>
    </div>
</footer>
<script src="<?= e(asset('assets/app.js')) ?>"></script>
</body>
</html>
