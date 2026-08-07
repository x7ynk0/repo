<?php
/**
 * İletişim / teklif formu.
 * Gönderilen talepler data/messages.json içine kaydedilir.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$services = Store::all('services', ['only_active' => true]);
$errors   = [];
$old      = [
    'name'     => '', 'email' => '', 'phone' => '', 'company' => '',
    'subject'  => '', 'message' => '', 'budget' => '2', 'deadline' => '',
    'services' => [],
];

/* Ön seçim (hizmet veya paket sayfasından geliş) */
$preselect = trim((string) input('hizmet', '', $_GET));
$prePaket  = trim((string) input('paket', '', $_GET));
if ($prePaket !== '') {
    $old['subject'] = $prePaket . ' paketi hakkında';
}
if ($preselect !== '') {
    $old['services'][] = $preselect;
    $old['subject'] = $preselect;
}

if (is_post()) {
    csrf_guard();

    /* Bal küpü (bot tuzağı) */
    if (trim((string) input('website')) !== '') {
        flash('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
        redirect(base_url('iletisim.php?durum=ok'));
    }

    /* Basit hız sınırlama: aynı oturumda 60 sn'de bir */
    $lastSent = (int) ($_SESSION['_last_msg'] ?? 0);
    if (time() - $lastSent < 60) {
        $errors['general'] = 'Çok hızlı gönderim yaptınız. Lütfen bir dakika bekleyip tekrar deneyin.';
    }

    $old['name']     = (string) input('name');
    $old['email']    = (string) input('email');
    $old['phone']    = (string) input('phone');
    $old['company']  = (string) input('company');
    $old['subject']  = (string) input('subject');
    $old['message']  = (string) input('message');
    $old['budget']   = (string) input('budget', '2');
    $old['deadline'] = (string) input('deadline');
    $old['services'] = input_array('services');

    if (mb_strlen($old['name']) < 2) {
        $errors['name'] = 'Lütfen adınızı ve soyadınızı girin.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Geçerli bir e-posta adresi girin.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9+()\s\-]{7,24}$/', $old['phone'])) {
        $errors['phone'] = 'Telefon numarası geçersiz görünüyor.';
    }
    if (mb_strlen($old['message']) < 20) {
        $errors['message'] = 'Lütfen projenizi biraz daha ayrıntılı anlatın (en az 20 karakter).';
    }
    if (!input_bool('consent')) {
        $errors['consent'] = 'Devam edebilmek için gizlilik metnini onaylamalısınız.';
    }

    if (!$errors) {
        $budgetLabels = [
            '0' => 'Henüz netleşmedi', '1' => '15.000 – 30.000 ₺', '2' => '30.000 – 60.000 ₺',
            '3' => '60.000 – 120.000 ₺', '4' => '120.000 – 250.000 ₺', '5' => '250.000 ₺ ve üzeri',
        ];

        Store::insert('messages', [
            'name'     => $old['name'],
            'email'    => $old['email'],
            'phone'    => $old['phone'],
            'company'  => $old['company'],
            'subject'  => $old['subject'] !== '' ? $old['subject'] : 'Genel talep',
            'message'  => $old['message'],
            'services' => $old['services'],
            'budget'   => $budgetLabels[$old['budget']] ?? '—',
            'deadline' => $old['deadline'],
            'status'   => 'new',
            'starred'  => false,
            'note'     => '',
            'ip'       => ip_hash(),
            'agent'    => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200),
            'source'   => (string) ($_SERVER['HTTP_REFERER'] ?? ''),
        ]);

        $_SESSION['_last_msg'] = time();
        auth_log('message', 'Yeni iletişim talebi: ' . $old['email']);

        flash('success', 'Teşekkürler! Talebiniz bize ulaştı. Ortalama 2 saat içinde dönüş yapıyoruz.');
        redirect(base_url('iletisim.php?durum=ok'));
    }

    flash('error', $errors['general'] ?? 'Formda eksik veya hatalı alanlar var. Lütfen kontrol edin.');
}

$sent = input('durum', '', $_GET) === 'ok';

$page = [
    'title' => 'İletişim',
    'desc'  => 'Projenizi konuşalım. Ücretsiz keşif görüşmesi için formu doldurun; ortalama 2 saat içinde dönüş yapıyoruz.',
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
                <span>İletişim</span>
            </nav>

            <span class="eyebrow"><span class="pulse-dot"></span> <?= e(setting('contact.reply_time', 'Hızlı dönüş')) ?></span>
            <h1>Projenizi <span class="gradient-text">konuşalım</span></h1>
            <p class="lead">İlk görüşme ücretsizdir ve hiçbir taahhüt içermez. 30 dakikada ihtiyacınızı netleştirir, size yazılı bir yol haritası ve net bütçe aralığı sunarız.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="split" style="grid-template-columns:1.15fr .85fr;align-items:flex-start">

            <!-- FORM -->
            <div class="reveal">
                <?php if ($sent): ?>
                    <div class="info-box" style="border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.06)">
                        <div class="flex gap-4">
                            <span class="feature-item__ico" style="background:rgba(34,197,94,.15);color:#4ade80"><?= icon('check', 22) ?></span>
                            <div>
                                <h3 style="margin-bottom:6px">Talebiniz alındı</h3>
                                <p>Mesajınız ekibimize ulaştı. <strong><?= e(setting('contact.work_hours', 'Mesai saatleri')) ?></strong> içinde,
                                   ortalama 2 saat içinde size dönüş yapıyoruz. Acil durumlar için doğrudan arayabilirsiniz.</p>
                                <div class="flex flex-wrap gap-3 mt-6">
                                    <a class="btn btn--primary" href="<?= e(base_url('projeler.php')) ?>">
                                        <span>Bu arada çalışmalarımıza göz atın</span><?= icon('arrow-right', 16) ?>
                                    </a>
                                    <a class="btn btn--ghost" href="<?= e(base_url('iletisim.php')) ?>">Yeni mesaj gönder</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="info-box">
                        <h2 style="font-size:1.5rem;margin-bottom:6px">Teklif formu</h2>
                        <p class="text-dim" style="font-size:.9rem">Yıldızlı alanlar zorunludur. Ne kadar ayrıntı verirseniz, teklifimiz o kadar isabetli olur.</p>

                        <form class="form mt-6" method="post" action="<?= e(base_url('iletisim.php')) ?>" data-validate novalidate>
                            <?= csrf_field() ?>

                            <!-- Bal küpü -->
                            <div class="honeypot" aria-hidden="true">
                                <label>Web siteniz <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                            </div>

                            <div class="form-row">
                                <div class="field">
                                    <label class="field__label" for="f-name">Ad Soyad <span class="req">*</span></label>
                                    <input class="input" type="text" id="f-name" name="name" required
                                           value="<?= e($old['name']) ?>" placeholder="Adınız ve soyadınız" autocomplete="name">
                                    <span class="field__error"><?= e($errors['name'] ?? '') ?></span>
                                </div>
                                <div class="field">
                                    <label class="field__label" for="f-email">E-posta <span class="req">*</span></label>
                                    <input class="input" type="email" id="f-email" name="email" required
                                           value="<?= e($old['email']) ?>" placeholder="ornek@sirket.com" autocomplete="email">
                                    <span class="field__error"><?= e($errors['email'] ?? '') ?></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="field">
                                    <label class="field__label" for="f-phone">Telefon</label>
                                    <input class="input" type="tel" id="f-phone" name="phone"
                                           value="<?= e($old['phone']) ?>" placeholder="+90 5xx xxx xx xx" autocomplete="tel">
                                    <span class="field__error"><?= e($errors['phone'] ?? '') ?></span>
                                </div>
                                <div class="field">
                                    <label class="field__label" for="f-company">Şirket / Marka</label>
                                    <input class="input" type="text" id="f-company" name="company"
                                           value="<?= e($old['company']) ?>" placeholder="Şirket adı" autocomplete="organization">
                                </div>
                            </div>

                            <div class="field">
                                <label class="field__label">İlgilendiğiniz hizmetler</label>
                                <div class="choice-grid">
                                    <?php foreach ($services as $s): ?>
                                        <label class="choice">
                                            <input type="checkbox" name="services[]" value="<?= e($s['title']) ?>"
                                                <?= in_array($s['title'], $old['services'], true) ? 'checked' : '' ?>>
                                            <span class="choice__box"><?= icon('check', 13) ?></span>
                                            <span><?= e($s['title']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="field">
                                    <label class="field__label" for="budgetRange">Tahmini bütçeniz</label>
                                    <div class="range-wrap">
                                        <input type="range" id="budgetRange" name="budget" min="0" max="5" step="1"
                                               value="<?= e($old['budget']) ?>" aria-describedby="budgetOut">
                                        <output class="badge badge--accent" id="budgetOut" style="width:fit-content">—</output>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="field__label" for="f-deadline">Hedef teslim tarihi</label>
                                    <select class="select" id="f-deadline" name="deadline">
                                        <?php foreach (['' => 'Seçiniz', 'acil' => 'Mümkün olan en kısa sürede', '1ay' => '1 ay içinde',
                                            '3ay' => '1 – 3 ay', '6ay' => '3 – 6 ay', 'esnek' => 'Esnek / acelesi yok'] as $val => $label): ?>
                                            <option value="<?= e($val) ?>" <?= $old['deadline'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="field">
                                <label class="field__label" for="f-subject">Konu</label>
                                <input class="input" type="text" id="f-subject" name="subject"
                                       value="<?= e($old['subject']) ?>" placeholder="Örn: E-ticaret sitesi yenileme">
                            </div>

                            <div class="field">
                                <label class="field__label" for="f-message">Projenizi anlatın <span class="req">*</span></label>
                                <textarea class="textarea" id="f-message" name="message" required data-minlength="20"
                                          maxlength="4000" placeholder="Ne yapmak istiyorsunuz? Şu an nasıl bir durumdasınız? Başarı sizin için ne anlama geliyor?"><?= e($old['message']) ?></textarea>
                                <div class="flex-between">
                                    <span class="field__error"><?= e($errors['message'] ?? '') ?></span>
                                    <span class="field__hint" data-counter-for="f-message"></span>
                                </div>
                            </div>

                            <label class="checkbox">
                                <input type="checkbox" name="consent" value="1" required>
                                <span><a href="<?= e(base_url('gizlilik.php')) ?>" target="_blank" style="color:var(--accent)">Gizlilik politikasını</a>
                                okudum; bilgilerimin teklif hazırlama amacıyla işlenmesini kabul ediyorum. <span class="req">*</span></span>
                            </label>
                            <?php if (!empty($errors['consent'])): ?>
                                <span class="field__error"><?= e($errors['consent']) ?></span>
                            <?php endif; ?>

                            <div class="form-note">
                                <?= icon('lock', 18) ?>
                                <span>Bilgileriniz yalnızca teklif hazırlamak için kullanılır, üçüncü taraflarla paylaşılmaz. Talep üzerine gizlilik sözleşmesi (NDA) imzalıyoruz.</span>
                            </div>

                            <button class="btn btn--primary btn--lg btn--shine" type="submit">
                                <span>Talebi gönder</span><?= icon('arrow-right', 18) ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- İLETİŞİM BİLGİLERİ -->
            <aside class="reveal reveal--right">
                <div class="grid" style="gap:var(--sp-4)">
                    <?php if ($email = setting('contact.email', '')): ?>
                        <div class="contact-card">
                            <span class="contact-card__ico"><?= icon('mail', 21) ?></span>
                            <div>
                                <h4>E-posta</h4>
                                <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($phone = setting('contact.phone', '')): ?>
                        <div class="contact-card">
                            <span class="contact-card__ico"><?= icon('phone', 21) ?></span>
                            <div>
                                <h4>Telefon</h4>
                                <a href="tel:<?= e(preg_replace('/\s+/', '', (string) $phone)) ?>"><?= e($phone) ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($wa = preg_replace('/\D+/', '', (string) setting('contact.whatsapp', ''))): ?>
                        <div class="contact-card">
                            <span class="contact-card__ico"><?= icon('headset', 21) ?></span>
                            <div>
                                <h4>WhatsApp</h4>
                                <a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">Hemen yazın →</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($addr = setting('contact.address', '')): ?>
                        <div class="contact-card">
                            <span class="contact-card__ico"><?= icon('pin', 21) ?></span>
                            <div>
                                <h4>Adres</h4>
                                <p><?= e($addr) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($hours = setting('contact.work_hours', '')): ?>
                        <div class="contact-card">
                            <span class="contact-card__ico"><?= icon('clock', 21) ?></span>
                            <div>
                                <h4>Çalışma saatleri</h4>
                                <p><?= e($hours) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-box mt-6">
                    <h4><?= icon('help', 18) ?> Görüşmeden ne beklemelisiniz?</h4>
                    <ul class="check-list mt-4">
                        <li><?= icon('check', 17) ?> Satış baskısı yok, teknik danışmanlık var</li>
                        <li><?= icon('check', 17) ?> 30 dakika, çevrim içi veya yüz yüze</li>
                        <li><?= icon('check', 17) ?> Görüşme sonunda yazılı özet</li>
                        <li><?= icon('check', 17) ?> İhtiyacınız yoksa "gerek yok" deriz</li>
                    </ul>
                </div>

                <?php if ($map = setting('contact.map', '')): ?>
                    <div class="media-frame mt-6" style="aspect-ratio:4/3">
                        <iframe src="<?= e($map) ?>" width="100%" height="100%" style="border:0"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Konum haritası"></iframe>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<?php require INC_PATH . '/layout/footer.php'; ?>
