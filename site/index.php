<?php
/**
 * Ana sayfa.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$services     = Store::all('services', ['only_active' => true]);
$featuredSrv  = array_values(array_filter($services, static fn($s) => !empty($s['featured'])));
$topServices  = array_slice($featuredSrv ?: $services, 0, 6);
$projects     = Store::all('projects', ['only_active' => true]);
$featuredPrj  = array_slice(array_values(array_filter($projects, static fn($p) => !empty($p['featured']))) ?: $projects, 0, 6);
$testimonials = Store::all('testimonials', ['only_active' => true]);
$packages     = Store::all('packages', ['only_active' => true]);
$process      = Store::all('process', ['only_active' => true]);
$skills       = Store::all('skills', ['only_active' => true]);
$faq          = array_slice(Store::all('faq', ['only_active' => true]), 0, 6);
$clients      = Store::all('clients', ['only_active' => true]);
$posts        = array_slice(Store::all('posts', ['only_active' => true, 'sort' => 'date', 'dir' => 'desc']), 0, 3);
$stats        = (array) setting('stats', []);
$hero         = (array) setting('hero', []);
$features     = (array) setting('features', []);

$page = [
    'title' => '',
    'desc'  => setting('site.description', ''),
];

require INC_PATH . '/layout/header.php';
?>

<!-- =================================================================
     HERO
     ================================================================= -->
<section class="hero">
    <div class="hero__bg">
        <div class="hero__grid"></div>
        <span class="aurora aurora--1"></span>
        <span class="aurora aurora--2"></span>
        <span class="aurora aurora--3"></span>
    </div>

    <div class="container hero__inner">
        <div class="hero__content">
            <?php if (!empty($hero['badge'])): ?>
                <div class="hero__badge">
                    <span class="pulse-dot" aria-hidden="true"></span>
                    <?= e($hero['badge']) ?>
                </div>
            <?php endif; ?>

            <h1 class="hero__title">
                <?php
                $lines = (array) ($hero['title_lines'] ?? []);
                $last  = count($lines) - 1;
                foreach ($lines as $i => $line): ?>
                    <span class="hero__line"><span<?= $i === $last ? ' class="gradient-text"' : '' ?>><?= e($line) ?></span></span>
                <?php endforeach; ?>
            </h1>

            <span class="hero__rotator" id="heroRotator"
                  data-words='<?= e(json_encode(array_values((array) ($hero['rotating'] ?? [])), JSON_UNESCAPED_UNICODE)) ?>'></span>

            <p class="hero__text lead"><?= e($hero['subtitle'] ?? '') ?></p>

            <div class="hero__actions">
                <a class="btn btn--primary btn--lg btn--shine" href="<?= e(base_url('iletisim.php')) ?>" data-magnetic>
                    <span><?= e($hero['cta_primary'] ?? 'Teklif Al') ?></span><?= icon('arrow-right', 18) ?>
                </a>
                <a class="btn btn--ghost btn--lg" href="<?= e(base_url('projeler.php')) ?>" data-magnetic>
                    <?= icon('play', 17) ?><span><?= e($hero['cta_secondary'] ?? 'Çalışmalar') ?></span>
                </a>
            </div>

            <div class="hero__proof">
                <span class="hero__proof-item"><?= icon('check-circle', 18) ?> Sabit fiyat garantisi</span>
                <span class="hero__proof-item"><?= icon('check-circle', 18) ?> Kaynak kod size ait</span>
                <span class="hero__proof-item"><?= icon('check-circle', 18) ?> 3 ay ücretsiz destek</span>
            </div>
        </div>

        <div class="hero__visual">
            <div class="code-window" data-tilt="6">
                <div class="code-window__bar">
                    <span class="code-window__dot"></span>
                    <span class="code-window__dot"></span>
                    <span class="code-window__dot"></span>
                    <span class="code-window__name">project.js</span>
                </div>
                <div class="code-window__body">
                    <pre data-highlight><?= e($hero['code_snippet'] ?? '') ?></pre>
                </div>
            </div>

            <div class="float-card float-card--1">
                <span class="float-card__ico"><?= icon('zap', 18) ?></span>
                <div><strong>99<span style="font-size:.7em">/100</span></strong><span>PageSpeed skoru</span></div>
            </div>
            <div class="float-card float-card--2">
                <span class="float-card__ico"><?= icon('trend', 18) ?></span>
                <div><strong>+210%</strong><span>Ortalama trafik artışı</span></div>
            </div>
            <div class="float-card float-card--3">
                <span class="float-card__ico"><?= icon('shield', 18) ?></span>
                <div><strong>99.9%</strong><span>Çalışma süresi</span></div>
            </div>
        </div>
    </div>

    <div class="scroll-hint" aria-hidden="true">
        <span>Kaydır</span>
        <span class="scroll-hint__line"></span>
    </div>
</section>

<!-- =================================================================
     MÜŞTERİ LOGO ŞERİDİ
     ================================================================= -->
<?php if (!empty($features['clients']) && $clients): ?>
<section class="marquee" aria-label="Birlikte çalıştığımız markalar">
    <div class="marquee__track">
        <?php foreach ($clients as $c): ?>
            <span class="marquee__item">
                <?php if (!empty($c['logo'])): ?>
                    <img src="<?= e(upload_url($c['logo'])) ?>" alt="<?= e($c['name']) ?>" loading="lazy">
                <?php else: ?>
                    <?= e($c['name']) ?>
                <?php endif; ?>
                <span class="marquee__sep" aria-hidden="true">✦</span>
            </span>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     İSTATİSTİKLER
     ================================================================= -->
<?php if (!empty($features['stats']) && $stats): ?>
<section class="section section--tight">
    <div class="container">
        <div class="stats-grid">
            <?php foreach ($stats as $i => $s): ?>
                <div class="stat reveal" data-delay="<?= $i + 1 ?>">
                    <div class="stat__value">
                        <span data-count="<?= e((string) ($s['value'] ?? 0)) ?>" data-suffix="<?= e($s['suffix'] ?? '') ?>">0</span>
                    </div>
                    <div class="stat__label"><?= e($s['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     HİZMETLER
     ================================================================= -->
<section class="section section--grid-bg" id="hizmetler">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('layers', 15) ?> Hizmetlerimiz</span>
            <h2>Dijitalde ihtiyacınız olan <span class="gradient-text">her şey tek çatı altında</span></h2>
            <p>Fikir aşamasından yayına, yayından büyümeye kadar tüm süreci yönetiyoruz. Ajanslar arasında koordinasyon derdi olmadan, tek muhatapla çalışın.</p>
        </div>

        <div class="grid grid--auto-lg">
            <?php foreach ($topServices as $i => $s): ?>
                <article class="card service-card reveal" data-delay="<?= ($i % 3) + 1 ?>">
                    <span class="service-card__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <div class="service-card__icon"><?= icon((string) ($s['icon'] ?? 'code'), 26) ?></div>
                    <h3><?= e($s['title']) ?></h3>
                    <p><?= e($s['excerpt'] ?? '') ?></p>

                    <?php if (!empty($s['features'])): ?>
                        <div class="service-card__meta">
                            <?php foreach (array_slice((array) $s['features'], 0, 3) as $f): ?>
                                <span class="tag"><?= e($f) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <a class="service-card__link" href="<?= e(base_url('hizmet.php?s=' . urlencode((string) $s['slug']))) ?>">
                        Detayları gör <?= icon('arrow-right', 16) ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-7 reveal">
            <a class="btn btn--outline btn--lg" href="<?= e(base_url('hizmetler.php')) ?>" data-magnetic>
                <span>Tüm hizmetleri incele (<?= count($services) ?>)</span><?= icon('arrow-right', 17) ?>
            </a>
        </div>
    </div>
</section>

<!-- =================================================================
     NEDEN BİZ
     ================================================================= -->
<section class="section section--alt">
    <div class="container">
        <div class="split">
            <div class="reveal reveal--left">
                <span class="eyebrow"><?= icon('award', 15) ?> Neden biz?</span>
                <h2>Ajans değil, <span class="gradient-text">teknoloji ortağınız</span></h2>
                <p class="lead mt-4">Projeyi teslim edip kaybolmuyoruz. Ürününüzün ilk satırından ilk 10.000 kullanıcısına kadar yanınızdayız.</p>

                <div class="mt-6">
                    <?php
                    $whyItems = [
                        ['icon' => 'target', 'title' => 'İş sonucuna odaklı geliştirme', 'text' => 'Her özelliği "bu, hangi metriği iyileştirecek?" sorusuyla değerlendiriyoruz. Kod değil, sonuç teslim ediyoruz.'],
                        ['icon' => 'zap',    'title' => 'Hız takıntısı', 'text' => 'Teslim ettiğimiz her projede 90+ PageSpeed skoru hedefliyoruz. Yavaş site, kaybedilen müşteri demektir.'],
                        ['icon' => 'shield', 'title' => 'Güvenlik ilk günden itibaren', 'text' => 'OWASP standartları, KVKK uyumu ve düzenli güvenlik taraması standart sürecimizin parçası.'],
                        ['icon' => 'users',  'title' => 'Şeffaf iletişim', 'text' => 'Haftalık ilerleme raporu, canlı proje panosu ve doğrudan erişebileceğiniz bir ekip.'],
                    ];
                    foreach ($whyItems as $w): ?>
                        <div class="feature-item">
                            <span class="feature-item__ico"><?= icon($w['icon'], 20) ?></span>
                            <div>
                                <h4><?= e($w['title']) ?></h4>
                                <p><?= e($w['text']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="reveal reveal--right">
                <div class="compare" style="grid-template-columns:1fr">
                    <div class="compare__col compare__col--bad">
                        <div class="compare__title"><?= icon('x', 19) ?> Klasik ajans deneyimi</div>
                        <ul class="check-list check-list--muted">
                            <li><?= icon('x', 17) ?> Teslimden sonra ulaşılamayan ekip</li>
                            <li><?= icon('x', 17) ?> Her değişiklikte sürpriz ek fatura</li>
                            <li><?= icon('x', 17) ?> Şablon tema üzerine "özel tasarım"</li>
                            <li><?= icon('x', 17) ?> Kaynak koda erişememe</li>
                            <li><?= icon('x', 17) ?> Sadece "güzel görünen" ama dönüşmeyen site</li>
                        </ul>
                    </div>
                    <div class="compare__col compare__col--good">
                        <div class="compare__title"><?= icon('check', 19) ?> Bizimle çalışmak</div>
                        <ul class="check-list">
                            <li><?= icon('check', 17) ?> Yayın sonrası 3 ay ücretsiz destek</li>
                            <li><?= icon('check', 17) ?> Kapsam sabitse fiyat sabit</li>
                            <li><?= icon('check', 17) ?> Sıfırdan, markanıza özel tasarım</li>
                            <li><?= icon('check', 17) ?> Tüm kaynak kod ve haklar size ait</li>
                            <li><?= icon('check', 17) ?> Ölçülebilir dönüşüm hedefi ile teslim</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =================================================================
     SÜREÇ
     ================================================================= -->
<?php if ($process): ?>
<section class="section">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('refresh', 15) ?> Nasıl çalışıyoruz?</span>
            <h2>Belirsizlik yok. <span class="gradient-text">Net bir süreç var.</span></h2>
            <p>İlk görüşmeden yayına kadar her adımda ne olacağını, ne zaman olacağını ve sizden ne beklendiğini önceden bilirsiniz.</p>
        </div>

        <div class="process-grid">
            <?php foreach ($process as $i => $p): ?>
                <article class="process-step reveal" data-delay="<?= ($i % 4) + 1 ?>">
                    <span class="process-step__num"><?= e($p['duration'] ?? '') ?></span>
                    <div class="process-step__ico"><?= icon((string) ($p['icon'] ?? 'zap'), 22) ?></div>
                    <h3><?= e($p['title']) ?></h3>
                    <p><?= e($p['body'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     ÖNE ÇIKAN PROJELER
     ================================================================= -->
<?php if ($featuredPrj): ?>
<section class="section section--alt" id="projeler">
    <div class="container">
        <div class="flex-between flex-wrap reveal mb-6">
            <div class="section-head" style="margin-bottom:0">
                <span class="eyebrow"><?= icon('folder', 15) ?> Referanslar</span>
                <h2>Konuşan işler, <span class="gradient-text">konuşan rakamlar</span></h2>
                <p>Her projeyi "ne kadar güzel oldu" ile değil, "ne kadar iş sonucu üretti" ile ölçüyoruz.</p>
            </div>
            <a class="btn btn--ghost" href="<?= e(base_url('projeler.php')) ?>" data-magnetic>
                <span>Tümü</span><?= icon('arrow-right', 16) ?>
            </a>
        </div>

        <div class="grid grid--auto-lg">
            <?php foreach ($featuredPrj as $i => $p): ?>
                <a class="project-card reveal" data-delay="<?= ($i % 3) + 1 ?>"
                   href="<?= e(base_url('proje.php?p=' . urlencode((string) $p['slug']))) ?>">
                    <div class="project-card__media">
                        <span class="project-card__cat"><?= e($p['category'] ?? '') ?></span>
                        <span class="project-card__year"><?= e($p['year'] ?? '') ?></span>
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= e(upload_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="project-card__placeholder"><?= e(explode(' ', (string) $p['title'])[0]) ?></span>
                        <?php endif; ?>
                        <div class="project-card__overlay">
                            <span class="project-card__cta">Vaka çalışmasını oku <?= icon('arrow-right', 17) ?></span>
                        </div>
                    </div>
                    <div class="project-card__body">
                        <h3><?= e($p['title']) ?></h3>
                        <p><?= e(str_limit($p['excerpt'] ?? '', 110)) ?></p>

                        <?php if (!empty($p['metrics'])): ?>
                            <div class="project-card__metrics">
                                <?php foreach (array_slice((array) $p['metrics'], 0, 3) as $m): ?>
                                    <div class="project-card__metric">
                                        <strong><?= e($m['value'] ?? '') ?></strong>
                                        <span><?= e($m['label'] ?? '') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     TEKNOLOJİ ŞERİDİ + YETENEKLER
     ================================================================= -->
<?php if ($skills): ?>
<section class="section">
    <div class="container">
        <div class="split">
            <div class="reveal reveal--left">
                <span class="eyebrow"><?= icon('cpu', 15) ?> Teknoloji yığını</span>
                <h2>Modaya değil, <span class="gradient-text">probleme göre teknoloji</span></h2>
                <p class="lead mt-4">Her projeye aynı çekiçle yaklaşmıyoruz. İhtiyaca, ekibinizin yetkinliğine ve uzun vadeli bakım maliyetine göre teknoloji seçiyoruz.</p>

                <div class="mt-6">
                    <?php foreach (array_slice($skills, 0, 8) as $i => $sk): ?>
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

            <div class="reveal reveal--right">
                <div class="info-box">
                    <h4><?= icon('terminal', 18) ?> Çalıştığımız teknolojiler</h4>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <?php
                        $stack = ['PHP', 'Laravel', 'JavaScript', 'TypeScript', 'React', 'Next.js', 'Vue', 'Nuxt',
                            'Node.js', 'Python', 'Go', 'React Native', 'Flutter', 'MySQL', 'PostgreSQL', 'MongoDB',
                            'Redis', 'Elasticsearch', 'Docker', 'Kubernetes', 'AWS', 'Google Cloud', 'Tailwind',
                            'GraphQL', 'REST', 'WebSocket', 'Figma', 'Git'];
                        foreach ($stack as $t): ?>
                            <span class="tag"><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <hr class="divider" style="margin-block:var(--sp-5)">

                    <ul class="check-list">
                        <li><?= icon('check', 17) ?> Test kapsamı %80'in altına düşmez</li>
                        <li><?= icon('check', 17) ?> Her sürüm kod incelemesinden geçer</li>
                        <li><?= icon('check', 17) ?> Otomatik dağıtım (CI/CD) standarttır</li>
                        <li><?= icon('check', 17) ?> Teknik dokümantasyon teslimata dahildir</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="marquee tech-marquee marquee--reverse mt-7" aria-hidden="true">
        <div class="marquee__track">
            <?php foreach (['React', 'Laravel', 'Next.js', 'Node.js', 'TypeScript', 'Flutter', 'PostgreSQL', 'Docker', 'AWS', 'Python', 'Vue', 'Redis', 'GraphQL', 'Kubernetes'] as $t): ?>
                <span class="marquee__item"><?= e($t) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     REFERANS GÖRÜŞLERİ
     ================================================================= -->
<?php if (!empty($features['testimonials']) && $testimonials): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('quote', 15) ?> Müşteri görüşleri</span>
            <h2>Bizi en iyi <span class="gradient-text">onlar anlatır</span></h2>
            <p>Tamamladığımız projelerin ardından müşterilerimizin bıraktığı gerçek geri bildirimler.</p>
        </div>

        <div class="slider reveal" data-slider data-interval="5600" tabindex="0" aria-roledescription="carousel">
            <div class="slider__viewport">
                <div class="slider__track">
                    <?php foreach ($testimonials as $t): ?>
                        <div class="slider__slide">
                            <article class="testimonial">
                                <span class="testimonial__quote"><?= icon('quote', 38) ?></span>
                                <?= stars((int) ($t['rating'] ?? 5)) ?>
                                <p class="testimonial__body">"<?= e($t['body']) ?>"</p>
                                <div class="testimonial__author">
                                    <span class="avatar">
                                        <?php if (!empty($t['avatar'])): ?>
                                            <img src="<?= e(upload_url($t['avatar'])) ?>" alt="<?= e($t['name']) ?>" loading="lazy">
                                        <?php else: ?>
                                            <?= e(initials((string) $t['name'])) ?>
                                        <?php endif; ?>
                                    </span>
                                    <div>
                                        <div class="testimonial__name"><?= e($t['name']) ?></div>
                                        <div class="testimonial__role"><?= e($t['role'] ?? '') ?><?= !empty($t['company']) ? ' · ' . e($t['company']) : '' ?></div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="slider__nav">
                <div class="slider__dots"></div>
                <div class="slider__btns">
                    <button class="slider__btn" type="button" data-slider-prev aria-label="Önceki"><?= icon('arrow-left', 19) ?></button>
                    <button class="slider__btn" type="button" data-slider-next aria-label="Sonraki"><?= icon('arrow-right', 19) ?></button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     PAKETLER
     ================================================================= -->
<?php if (!empty($features['packages']) && $packages): ?>
<section class="section" id="paketler">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow"><?= icon('gift', 15) ?> Paketler</span>
            <h2>Şeffaf fiyatlandırma, <span class="gradient-text">sürpriz yok</span></h2>
            <p>Aşağıdaki paketler başlangıç noktasıdır. Kapsam netleştikten sonra size özel, bağlayıcı bir teklif hazırlıyoruz.</p>
        </div>

        <div class="pricing-grid">
            <?php foreach ($packages as $i => $pk): ?>
                <article class="price-card<?= !empty($pk['featured']) ? ' price-card--featured' : '' ?> reveal" data-delay="<?= $i + 1 ?>">
                    <?php if (!empty($pk['badge'])): ?>
                        <span class="price-card__badge"><?= e($pk['badge']) ?></span>
                    <?php endif; ?>

                    <h3><?= e($pk['title']) ?></h3>
                    <p class="price-card__sub"><?= e($pk['subtitle'] ?? '') ?></p>

                    <div class="price-card__price">
                        <?php if ((float) ($pk['price'] ?? 0) > 0): ?>
                            <div>
                                <span class="price-card__from">başlangıç</span>
                                <span class="price-card__amount"><?= e(money($pk['price'])) ?></span>
                            </div>
                        <?php else: ?>
                            <span class="price-card__amount">Özel</span>
                        <?php endif; ?>
                        <span class="price-card__period">/ <?= e($pk['period'] ?? '') ?></span>
                    </div>

                    <ul class="check-list">
                        <?php foreach ((array) ($pk['features'] ?? []) as $f): ?>
                            <li><?= icon('check', 17) ?> <?= e($f) ?></li>
                        <?php endforeach; ?>
                        <?php foreach ((array) ($pk['excluded'] ?? []) as $f): ?>
                            <li style="color:var(--text-dim);opacity:.6"><?= icon('x', 17) ?> <?= e($f) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a class="btn <?= !empty($pk['featured']) ? 'btn--primary' : 'btn--outline' ?>"
                       href="<?= e(base_url('iletisim.php?paket=' . urlencode((string) $pk['title']))) ?>">
                        <span><?= e($pk['cta'] ?? 'Teklif Al') ?></span><?= icon('arrow-right', 16) ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="form-note mt-6 reveal" style="max-width:760px;margin-inline:auto">
            <?= icon('info', 18) ?>
            <span>Fiyatlar KDV hariçtir ve projenin kapsamına göre değişebilir. Aylık bakım, hosting ve içerik üretimi hizmetleri ayrıca fiyatlandırılır.</span>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     SSS
     ================================================================= -->
<?php if (!empty($features['faq']) && $faq): ?>
<section class="section section--alt">
    <div class="container">
        <div class="split split--sidebar" style="align-items:flex-start">
            <div class="reveal">
                <span class="eyebrow"><?= icon('help', 15) ?> Sıkça sorulanlar</span>
                <h2 class="mb-6">Aklınızdaki sorular</h2>

                <div class="accordion" data-single="1">
                    <?php foreach ($faq as $i => $f): ?>
                        <div class="accordion__item<?= $i === 0 ? ' is-open' : '' ?>">
                            <button class="accordion__head" type="button">
                                <span><?= e($f['q']) ?></span>
                                <span class="accordion__icon"><?= icon('chevron-down', 17) ?></span>
                            </button>
                            <div class="accordion__panel">
                                <div class="accordion__body"><?= e($f['a']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6">
                    <a class="btn btn--outline" href="<?= e(base_url('sss.php')) ?>">
                        <span>Tüm soruları gör</span><?= icon('arrow-right', 16) ?>
                    </a>
                </div>
            </div>

            <aside class="sticky-side reveal reveal--right">
                <div class="info-box">
                    <h4><?= icon('headset', 18) ?> Sorunuz listede yok mu?</h4>
                    <p class="mt-4" style="font-size:.93rem">Aklınıza takılan her şeyi doğrudan sorabilirsiniz. Genellikle 2 saat içinde yanıt veriyoruz.</p>
                    <div class="mt-6 flex" style="flex-direction:column;gap:10px">
                        <a class="btn btn--primary btn--block" href="<?= e(base_url('iletisim.php')) ?>">
                            <span>Bize yazın</span><?= icon('arrow-right', 16) ?>
                        </a>
                        <a class="btn btn--ghost btn--block" href="mailto:<?= e(setting('contact.email', '')) ?>">
                            <?= icon('mail', 16) ?><span>E-posta gönder</span>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     BLOG
     ================================================================= -->
<?php if (!empty($features['blog']) && $posts): ?>
<section class="section">
    <div class="container">
        <div class="flex-between flex-wrap reveal mb-6">
            <div class="section-head" style="margin-bottom:0">
                <span class="eyebrow"><?= icon('pen', 15) ?> Blog</span>
                <h2>Bildiklerimizi <span class="gradient-text">saklamıyoruz</span></h2>
            </div>
            <a class="btn btn--ghost" href="<?= e(base_url('blog.php')) ?>" data-magnetic>
                <span>Tüm yazılar</span><?= icon('arrow-right', 16) ?>
            </a>
        </div>

        <div class="grid grid--3">
            <?php foreach ($posts as $i => $post): ?>
                <a class="card post-card reveal" data-delay="<?= $i + 1 ?>"
                   href="<?= e(base_url('yazi.php?y=' . urlencode((string) $post['slug']))) ?>">
                    <div class="post-card__media">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= e(upload_url($post['image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="post-card__meta">
                        <span class="badge badge--accent"><?= e($post['category'] ?? '') ?></span>
                        <span><?= e(tr_date($post['date'] ?? '')) ?></span>
                        <span aria-hidden="true">·</span>
                        <span><?= read_time($post['body'] ?? '') ?> dk okuma</span>
                    </div>
                    <h3><?= e($post['title']) ?></h3>
                    <p><?= e(str_limit($post['excerpt'] ?? '', 120)) ?></p>
                    <div class="post-card__foot">
                        <span>Yazıyı oku</span><?= icon('arrow-right', 16) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require INC_PATH . '/layout/footer.php'; ?>
