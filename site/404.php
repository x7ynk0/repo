<?php
/**
 * 404 — Sayfa bulunamadı.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (http_response_code() === 200) {
    http_response_code(404);
}

$services = array_slice(Store::all('services', ['only_active' => true]), 0, 4);

$page = [
    'title'   => 'Sayfa bulunamadı',
    'desc'    => 'Aradığınız sayfa taşınmış veya hiç var olmamış olabilir.',
    'noindex' => true,
];

require INC_PATH . '/layout/header.php';
?>

<section class="section" style="padding-top:calc(var(--header-h) + 90px);min-height:78vh;display:flex;align-items:center">
    <span class="aurora aurora--1"></span>
    <span class="aurora aurora--2"></span>

    <div class="container text-center" style="position:relative;z-index:2">
        <div style="font-family:var(--font-display);font-size:clamp(5rem,20vw,12rem);font-weight:800;line-height:.9;letter-spacing:-.06em"
             class="gradient-text">404</div>

        <h1 class="mt-4" style="font-size:clamp(1.6rem,4vw,2.6rem)">Bu sayfa derlenmedi</h1>
        <p class="lead mt-4" style="max-width:52ch;margin-inline:auto">
            Aradığınız sayfa taşınmış, adı değişmiş ya da hiç var olmamış olabilir.
            Aşağıdaki bağlantılardan devam edebilirsiniz.
        </p>

        <div class="flex flex-wrap gap-3 mt-7" style="justify-content:center">
            <a class="btn btn--primary btn--lg" href="<?= e(base_url()) ?>" data-magnetic>
                <?= icon('arrow-left', 17) ?><span>Ana sayfaya dön</span>
            </a>
            <a class="btn btn--ghost btn--lg" href="<?= e(base_url('iletisim.php')) ?>">
                <?= icon('headset', 17) ?><span>Yardım isteyin</span>
            </a>
        </div>

        <?php if ($services): ?>
            <div class="mt-7" style="max-width:900px;margin-inline:auto">
                <p class="footer-title" style="text-align:center">Popüler hizmetler</p>
                <div class="flex flex-wrap gap-2 mt-4" style="justify-content:center">
                    <?php foreach ($services as $s): ?>
                        <a class="tag" href="<?= e(base_url('hizmet.php?s=' . urlencode((string) $s['slug']))) ?>"
                           style="padding:9px 16px;font-size:.85rem"><?= e($s['title']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require INC_PATH . '/layout/footer.php'; ?>
