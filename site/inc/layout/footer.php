<?php
/** Genel site alt bilgisi (footer). */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$footerServices = array_slice(Store::all('services', ['only_active' => true]), 0, 7);
$social = (array) setting('social', []);
$socialLabels = [
    'github' => 'GitHub', 'linkedin' => 'LinkedIn', 'x' => 'X', 'instagram' => 'Instagram',
    'youtube' => 'YouTube', 'dribbble' => 'Dribbble', 'behance' => 'Behance',
];
$wa = preg_replace('/\D+/', '', (string) setting('contact.whatsapp', ''));
?>
</main>

<section class="cta-band reveal">
    <div class="cta-band__glow" aria-hidden="true"></div>
    <div class="container cta-band__inner">
        <div>
            <span class="eyebrow"><?= icon('rocket', 16) ?> Sıradaki proje sizinki olsun</span>
            <h2 class="cta-band__title"><?= e(setting('cta.title', 'Projenizi konuşalım')) ?></h2>
            <p class="cta-band__text"><?= e(setting('cta.subtitle', '')) ?></p>
        </div>
        <div class="cta-band__actions">
            <a class="btn btn--primary btn--lg" href="<?= e(base_url('iletisim.php')) ?>" data-magnetic>
                <span><?= e(setting('cta.button', 'Teklif Al')) ?></span><?= icon('arrow-right', 18) ?>
            </a>
            <a class="btn btn--ghost btn--lg" href="mailto:<?= e(setting('contact.email', '')) ?>">
                <?= icon('mail', 18) ?><span><?= e(setting('contact.email', '')) ?></span>
            </a>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col footer-col--brand">
                <a class="brand brand--footer" href="<?= e(base_url()) ?>">
                    <span class="brand__mark" aria-hidden="true">
                        <svg viewBox="0 0 40 40" width="34" height="34">
                            <defs>
                                <linearGradient id="brandGradF" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="var(--accent)"/>
                                    <stop offset="100%" stop-color="var(--accent-2)"/>
                                </linearGradient>
                            </defs>
                            <rect x="1.5" y="1.5" width="37" height="37" rx="11" fill="none" stroke="url(#brandGradF)" stroke-width="2"/>
                            <path d="M13 15l-4 5 4 5M27 15l4 5-4 5M22 12l-4 16" fill="none" stroke="url(#brandGradF)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="brand__text"><strong><?= e(setting('site.name', APP_NAME)) ?></strong></span>
                </a>
                <p class="footer-about"><?= e(str_limit(setting('site.description', ''), 190)) ?></p>

                <div class="socials">
                    <?php foreach ($social as $key => $url): ?>
                        <?php if (!$url) continue; ?>
                        <a class="social" href="<?= e($url) ?>" target="_blank" rel="noopener"
                           aria-label="<?= e($socialLabels[$key] ?? $key) ?>" title="<?= e($socialLabels[$key] ?? $key) ?>">
                            <?= e(mb_strtoupper(mb_substr((string) ($socialLabels[$key] ?? $key), 0, 2))) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="footer-col">
                <h3 class="footer-title">Hizmetler</h3>
                <ul class="footer-links">
                    <?php foreach ($footerServices as $s): ?>
                        <li><a href="<?= e(base_url('hizmet.php?s=' . urlencode((string) $s['slug']))) ?>"><?= e($s['title']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?= e(base_url('hizmetler.php')) ?>"><strong>Tüm hizmetler →</strong></a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3 class="footer-title">Kurumsal</h3>
                <ul class="footer-links">
                    <li><a href="<?= e(base_url('hakkimizda.php')) ?>">Hakkımızda</a></li>
                    <li><a href="<?= e(base_url('projeler.php')) ?>">Referanslar</a></li>
                    <li><a href="<?= e(base_url('sss.php')) ?>">Sıkça Sorulan Sorular</a></li>
                    <?php if (setting('features.blog', true)): ?>
                        <li><a href="<?= e(base_url('blog.php')) ?>">Blog</a></li>
                    <?php endif; ?>
                    <li><a href="<?= e(base_url('iletisim.php')) ?>">İletişim</a></li>
                    <li><a href="<?= e(base_url('gizlilik.php')) ?>">Gizlilik &amp; KVKK</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3 class="footer-title">İletişim</h3>
                <ul class="footer-contact">
                    <?php if ($m = setting('contact.email', '')): ?>
                        <li><?= icon('mail', 18) ?><a href="mailto:<?= e($m) ?>"><?= e($m) ?></a></li>
                    <?php endif; ?>
                    <?php if ($p = setting('contact.phone', '')): ?>
                        <li><?= icon('phone', 18) ?><a href="tel:<?= e(preg_replace('/\s+/', '', (string) $p)) ?>"><?= e($p) ?></a></li>
                    <?php endif; ?>
                    <?php if ($a = setting('contact.address', '')): ?>
                        <li><?= icon('pin', 18) ?><span><?= e($a) ?></span></li>
                    <?php endif; ?>
                    <?php if ($h = setting('contact.work_hours', '')): ?>
                        <li><?= icon('clock', 18) ?><span><?= e($h) ?></span></li>
                    <?php endif; ?>
                </ul>
                <div class="footer-badge">
                    <span class="pulse-dot" aria-hidden="true"></span>
                    <span><?= e(setting('hero.badge', 'Yeni projeler için müsait')) ?></span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p><?= e(setting('site.copyright', '© ' . date('Y'))) ?></p>
            <p class="footer-bottom__meta">
                <a href="<?= e(base_url('sitemap.php')) ?>">Site Haritası</a>
                <span aria-hidden="true">•</span>
                <a href="<?= e(base_url('gizlilik.php')) ?>">Gizlilik Politikası</a>
                <span aria-hidden="true">•</span>
                <a href="<?= e(admin_url()) ?>" rel="nofollow">Yönetim</a>
            </p>
        </div>
    </div>
</footer>

<?php if ($wa !== ''): ?>
<a class="whatsapp-fab" href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Merhaba, bir proje hakkında görüşmek istiyorum.') ?>"
   target="_blank" rel="noopener" aria-label="WhatsApp'tan yazın" title="WhatsApp'tan yazın">
    <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" aria-hidden="true">
        <path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.64-2.05-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.06 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.28.17-1.41-.07-.13-.27-.2-.57-.35z"/>
        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.96L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2zm0 18.13h-.01c-1.52 0-3.02-.41-4.32-1.18l-.31-.18-3.21.84.86-3.13-.2-.32a8.19 8.19 0 0 1-1.26-4.37c0-4.54 3.7-8.23 8.25-8.23 2.2 0 4.27.86 5.83 2.42a8.18 8.18 0 0 1 2.41 5.82c0 4.54-3.7 8.23-8.24 8.23z"/>
    </svg>
</a>
<?php endif; ?>

<button class="to-top" id="toTop" type="button" aria-label="Yukarı çık"><?= icon('arrow-up', 20) ?></button>

<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
</body>
</html>
