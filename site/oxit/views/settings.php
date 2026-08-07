<?php
/**
 * Site ayarları.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$settings = Store::read('settings', true);

/* ---------------------------------------------------------------
 |  Kaydetme
 --------------------------------------------------------------- */
if (is_post()) {
    csrf_guard();

    $lines = static function (string $key): array {
        $raw = (string) input($key, '');
        $out = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        return array_values(array_filter(array_map('trim', $out), static fn($v) => $v !== ''));
    };

    /* --- Görsel yüklemeleri --- */
    $imageFields = [
        'site.logo'      => 'misc',
        'site.favicon'   => 'misc',
        'site.og_image'  => 'misc',
        'about.image'    => 'misc',
    ];
    $uploaded = [];
    foreach ($imageFields as $path => $folder) {
        $field = str_replace('.', '_', $path);
        [$grp, $key] = explode('.', $path);
        $current = (string) ($settings[$grp][$key] ?? '');

        if (input_bool($field . '__delete')) {
            delete_upload($current);
            $current = '';
        }
        if (!empty($_FILES[$field . '__file']['name'])) {
            $up = upload_image($field . '__file', $folder);
            if ($up['ok']) {
                if ($current !== '') delete_upload($current);
                $current = (string) $up['path'];
            } else {
                flash('error', $path . ': ' . $up['error']);
            }
        } else {
            $typed = (string) input($field, '');
            if ($typed !== '' || input_bool($field . '__delete')) {
                $current = $typed;
            }
        }
        $uploaded[$path] = $current;
    }

    /* --- İstatistikler --- */
    $stats = [];
    $sv = (array) ($_POST['stat_value'] ?? []);
    $ss = (array) ($_POST['stat_suffix'] ?? []);
    $sl = (array) ($_POST['stat_label'] ?? []);
    foreach ($sv as $i => $v) {
        $label = trim((string) ($sl[$i] ?? ''));
        if ($label === '') continue;
        $stats[] = [
            'value'  => is_numeric($v) ? (float) $v : 0,
            'suffix' => trim((string) ($ss[$i] ?? '')),
            'label'  => $label,
        ];
    }

    $new = [
        'site' => [
            'name'        => (string) input('site_name'),
            'short_name'  => (string) input('site_short_name'),
            'tagline'     => (string) input('site_tagline'),
            'description' => (string) input('site_description'),
            'keywords'    => (string) input('site_keywords'),
            'logo'        => $uploaded['site.logo'],
            'favicon'     => $uploaded['site.favicon'],
            'og_image'    => $uploaded['site.og_image'],
            'author'      => (string) input('site_author'),
            'copyright'   => (string) input('site_copyright'),
            'lang'        => (string) input('site_lang', 'tr'),
        ],
        'theme' => [
            'accent'    => (string) input('theme_accent', '#7c5cff'),
            'accent2'   => (string) input('theme_accent2', '#22d3ee'),
            'accent3'   => (string) input('theme_accent3', '#f472b6'),
            'radius'    => (string) input('theme_radius', '18'),
            'mode'      => input('theme_mode', 'dark') === 'light' ? 'light' : 'dark',
            'cursor'    => input_bool('theme_cursor'),
            'preloader' => input_bool('theme_preloader'),
            'particles' => input_bool('theme_particles'),
            'noise'     => input_bool('theme_noise'),
        ],
        'hero' => [
            'badge'         => (string) input('hero_badge'),
            'title_lines'   => $lines('hero_title_lines'),
            'rotating'      => $lines('hero_rotating'),
            'subtitle'      => (string) input('hero_subtitle'),
            'cta_primary'   => (string) input('hero_cta_primary'),
            'cta_secondary' => (string) input('hero_cta_secondary'),
            'code_snippet'  => (string) ($_POST['hero_code_snippet'] ?? ''),
        ],
        'stats' => $stats,
        'about' => [
            'title'      => (string) input('about_title'),
            'subtitle'   => (string) input('about_subtitle'),
            'image'      => $uploaded['about.image'],
            'body'       => (string) ($_POST['about_body'] ?? ''),
            'highlights' => $lines('about_highlights'),
        ],
        'contact' => [
            'email'      => (string) input('contact_email'),
            'phone'      => (string) input('contact_phone'),
            'whatsapp'   => (string) input('contact_whatsapp'),
            'address'    => (string) input('contact_address'),
            'work_hours' => (string) input('contact_work_hours'),
            'map'        => (string) input('contact_map'),
            'reply_time' => (string) input('contact_reply_time'),
        ],
        'social' => [
            'github'    => (string) input('social_github'),
            'linkedin'  => (string) input('social_linkedin'),
            'x'         => (string) input('social_x'),
            'instagram' => (string) input('social_instagram'),
            'youtube'   => (string) input('social_youtube'),
            'dribbble'  => (string) input('social_dribbble'),
            'behance'   => (string) input('social_behance'),
        ],
        'cta' => [
            'title'    => (string) input('cta_title'),
            'subtitle' => (string) input('cta_subtitle'),
            'button'   => (string) input('cta_button'),
        ],
        'seo' => [
            'ga_id'          => (string) input('seo_ga_id'),
            'search_console' => (string) input('seo_search_console'),
            'robots'         => (string) input('seo_robots', 'index, follow'),
            'canonical'      => rtrim((string) input('seo_canonical'), '/'),
        ],
        'features' => [
            'blog'         => input_bool('feat_blog'),
            'testimonials' => input_bool('feat_testimonials'),
            'packages'     => input_bool('feat_packages'),
            'faq'          => input_bool('feat_faq'),
            'stats'        => input_bool('feat_stats'),
            'clients'      => input_bool('feat_clients'),
        ],
        'maintenance' => [
            'enabled' => input_bool('maint_enabled'),
            'message' => (string) input('maint_message'),
        ],
    ];

    Store::write('settings', $new);
    $GLOBALS['settings'] = $new;
    auth_log('settings', 'Site ayarları güncellendi.');
    flash('success', 'Ayarlar kaydedildi.');
    redirect(admin_url('?p=ayarlar'));
}

/* Kısayol okuma yardımcıları */
$g = static function (string $path, $default = '') use ($settings) {
    $parts = explode('.', $path);
    $val = $settings;
    foreach ($parts as $part) {
        if (!is_array($val) || !array_key_exists($part, $val)) return $default;
        $val = $val[$part];
    }
    return $val;
};

$tabs = [
    'genel'    => ['Genel', 'settings'],
    'hero'     => ['Ana Sayfa', 'rocket'],
    'hakkinda' => ['Hakkımızda', 'users'],
    'iletisim' => ['İletişim', 'phone'],
    'sosyal'   => ['Sosyal Medya', 'share'],
    'tema'     => ['Tema & Efektler', 'palette'],
    'seo'      => ['SEO', 'search'],
    'moduller' => ['Modüller', 'grid'],
];

admin_header('Site Ayarları', ['subtitle' => 'Sitenin tüm içeriğini ve görünümünü buradan yönetin']);
?>

<form method="post" action="<?= e(admin_url('?p=ayarlar')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="admin-tabs" data-tabs>
        <?php foreach ($tabs as $key => [$label, $ico]): ?>
            <button type="button" class="admin-tab<?= $key === 'genel' ? ' is-active' : '' ?>" data-tab="<?= e($key) ?>">
                <?= icon($ico, 16) ?><span><?= e($label) ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- ============ GENEL ============ -->
    <div class="admin-card admin-tab-panel is-active" data-tab-panel="genel" id="genel">
        <div class="admin-card__head"><h2><?= icon('settings', 18) ?> Genel bilgiler</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('site_name', ['label' => 'Site / marka adı', 'type' => 'text', 'required' => true, 'col' => 2], $g('site.name'));
            admin_field('site_short_name', ['label' => 'Kısa ad (logo metni)', 'type' => 'text', 'col' => 2, 'hint' => 'Ön yükleyicide ve panelde görünür.'], $g('site.short_name'));
            admin_field('site_tagline', ['label' => 'Slogan', 'type' => 'text'], $g('site.tagline'));
            admin_field('site_description', ['label' => 'Site açıklaması (meta description)', 'type' => 'textarea', 'rows' => 3, 'hint' => 'Arama sonuçlarında görünür. 150-160 karakter idealdir.'], $g('site.description'));
            admin_field('site_keywords', ['label' => 'Anahtar kelimeler', 'type' => 'text', 'hint' => 'Virgülle ayırın.'], $g('site.keywords'));
            admin_field('site_author', ['label' => 'Yazar / firma', 'type' => 'text', 'col' => 2], $g('site.author'));
            admin_field('site_lang', ['label' => 'Dil kodu', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: tr, en'], $g('site.lang'));
            admin_field('site_copyright', ['label' => 'Telif metni', 'type' => 'text'], $g('site.copyright'));
            admin_field('site_logo', ['label' => 'Logo', 'type' => 'image', 'folder' => 'misc', 'col' => 2], $g('site.logo'));
            admin_field('site_favicon', ['label' => 'Favicon', 'type' => 'image', 'folder' => 'misc', 'col' => 2, 'hint' => 'Kare, tercihen SVG veya 512×512 PNG.'], $g('site.favicon'));
            admin_field('site_og_image', ['label' => 'Paylaşım görseli (OG image)', 'type' => 'image', 'folder' => 'misc', 'hint' => 'Sosyal medyada paylaşıldığında görünür. 1200×630 px önerilir.'], $g('site.og_image'));
            ?>
        </div>
    </div>

    <!-- ============ ANA SAYFA ============ -->
    <div class="admin-card admin-tab-panel" data-tab-panel="hero" id="hero">
        <div class="admin-card__head"><h2><?= icon('rocket', 18) ?> Giriş bölümü (hero)</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('hero_badge', ['label' => 'Üst rozet metni', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: Yeni projeler için müsait'], $g('hero.badge'));
            admin_field('hero_cta_primary', ['label' => 'Ana buton metni', 'type' => 'text', 'col' => 2], $g('hero.cta_primary'));
            admin_field('hero_title_lines', ['label' => 'Başlık satırları', 'type' => 'list', 'hint' => 'Her satır ayrı bir animasyonla belirir. Son satır renkli gösterilir.'], (array) $g('hero.title_lines', []));
            admin_field('hero_rotating', ['label' => 'Dönen yazılar', 'type' => 'list', 'hint' => 'Daktilo efektiyle sırayla yazılır.'], (array) $g('hero.rotating', []));
            admin_field('hero_subtitle', ['label' => 'Alt açıklama', 'type' => 'textarea', 'rows' => 3], $g('hero.subtitle'));
            admin_field('hero_cta_secondary', ['label' => 'İkincil buton metni', 'type' => 'text', 'col' => 2], $g('hero.cta_secondary'));
            admin_field('hero_code_snippet', ['label' => 'Kod penceresi içeriği', 'type' => 'richtext', 'rows' => 12, 'hint' => 'Sağdaki animasyonlu kod penceresinde gösterilir.'], $g('hero.code_snippet'));
            ?>
        </div>

        <hr class="divider">

        <div class="admin-card__head"><h2><?= icon('chart', 18) ?> Sayaç istatistikleri</h2></div>
        <div class="stats-editor">
            <?php
            $statRows = (array) $g('stats', []);
            if (!$statRows) $statRows = [['value' => '', 'suffix' => '', 'label' => '']];
            for ($i = 0; $i < max(4, count($statRows)); $i++):
                $row = $statRows[$i] ?? ['value' => '', 'suffix' => '', 'label' => ''];
            ?>
                <div class="stats-editor__row">
                    <input class="input" type="number" name="stat_value[]" value="<?= e((string) ($row['value'] ?? '')) ?>" placeholder="Değer" step="0.1">
                    <input class="input" type="text" name="stat_suffix[]" value="<?= e((string) ($row['suffix'] ?? '')) ?>" placeholder="Son ek (%, +, yıl)">
                    <input class="input" type="text" name="stat_label[]" value="<?= e((string) ($row['label'] ?? '')) ?>" placeholder="Etiket">
                </div>
            <?php endfor; ?>
        </div>
        <span class="field__hint">Etiketi boş bırakılan satırlar kaydedilmez.</span>

        <hr class="divider">

        <div class="admin-card__head"><h2><?= icon('zap', 18) ?> Alt çağrı bandı (CTA)</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('cta_title', ['label' => 'Başlık', 'type' => 'text', 'col' => 2], $g('cta.title'));
            admin_field('cta_button', ['label' => 'Buton metni', 'type' => 'text', 'col' => 2], $g('cta.button'));
            admin_field('cta_subtitle', ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2], $g('cta.subtitle'));
            ?>
        </div>
    </div>

    <!-- ============ HAKKIMIZDA ============ -->
    <div class="admin-card admin-tab-panel" data-tab-panel="hakkinda" id="hakkinda">
        <div class="admin-card__head"><h2><?= icon('users', 18) ?> Hakkımızda bölümü</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('about_subtitle', ['label' => 'Üst etiket', 'type' => 'text', 'col' => 2], $g('about.subtitle'));
            admin_field('about_title', ['label' => 'Başlık', 'type' => 'text', 'col' => 2], $g('about.title'));
            admin_field('about_body', ['label' => 'Metin', 'type' => 'richtext', 'rows' => 10], $g('about.body'));
            admin_field('about_highlights', ['label' => 'Öne çıkan maddeler', 'type' => 'list'], (array) $g('about.highlights', []));
            admin_field('about_image', ['label' => 'Görsel', 'type' => 'image', 'folder' => 'misc'], $g('about.image'));
            ?>
        </div>
    </div>

    <!-- ============ İLETİŞİM ============ -->
    <div class="admin-card admin-tab-panel" data-tab-panel="iletisim" id="iletisim">
        <div class="admin-card__head"><h2><?= icon('phone', 18) ?> İletişim bilgileri</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('contact_email', ['label' => 'E-posta', 'type' => 'text', 'col' => 2], $g('contact.email'));
            admin_field('contact_phone', ['label' => 'Telefon', 'type' => 'text', 'col' => 2], $g('contact.phone'));
            admin_field('contact_whatsapp', ['label' => 'WhatsApp numarası', 'type' => 'text', 'col' => 2, 'hint' => 'Ülke kodu ile, boşluksuz. Örn: 905320000000'], $g('contact.whatsapp'));
            admin_field('contact_work_hours', ['label' => 'Çalışma saatleri', 'type' => 'text', 'col' => 2], $g('contact.work_hours'));
            admin_field('contact_address', ['label' => 'Adres', 'type' => 'text'], $g('contact.address'));
            admin_field('contact_reply_time', ['label' => 'Yanıt süresi metni', 'type' => 'text', 'col' => 2], $g('contact.reply_time'));
            admin_field('contact_map', ['label' => 'Google Haritalar embed adresi', 'type' => 'text', 'hint' => 'Haritadan "Paylaş → Harita yerleştir" ile alınan src bağlantısı.'], $g('contact.map'));
            ?>
        </div>
    </div>

    <!-- ============ SOSYAL ============ -->
    <div class="admin-card admin-tab-panel" data-tab-panel="sosyal" id="sosyal">
        <div class="admin-card__head"><h2><?= icon('share', 18) ?> Sosyal medya hesapları</h2></div>
        <div class="admin-form-grid">
            <?php
            foreach ([
                'github' => 'GitHub', 'linkedin' => 'LinkedIn', 'x' => 'X (Twitter)', 'instagram' => 'Instagram',
                'youtube' => 'YouTube', 'dribbble' => 'Dribbble', 'behance' => 'Behance',
            ] as $key => $label) {
                admin_field('social_' . $key, ['label' => $label, 'type' => 'text', 'col' => 2, 'placeholder' => 'https://'], $g('social.' . $key));
            }
            ?>
        </div>
        <span class="field__hint">Boş bırakılan hesaplar sitede gösterilmez.</span>
    </div>

    <!-- ============ TEMA ============ -->
    <div class="admin-card admin-tab-panel" data-tab-panel="tema" id="tema">
        <div class="admin-card__head"><h2><?= icon('palette', 18) ?> Renkler</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('theme_accent', ['label' => 'Ana renk', 'type' => 'color', 'col' => 2], $g('theme.accent'));
            admin_field('theme_accent2', ['label' => 'İkincil renk', 'type' => 'color', 'col' => 2], $g('theme.accent2'));
            admin_field('theme_accent3', ['label' => 'Üçüncül renk', 'type' => 'color', 'col' => 2], $g('theme.accent3'));
            admin_field('theme_radius', ['label' => 'Köşe yuvarlaklığı (px)', 'type' => 'number', 'col' => 2, 'min' => 0, 'max' => 40], $g('theme.radius'));
            admin_field('theme_mode', ['label' => 'Varsayılan tema', 'type' => 'select', 'col' => 2,
                'options' => ['dark' => 'Koyu', 'light' => 'Açık']], $g('theme.mode'));
            ?>
        </div>

        <hr class="divider">

        <div class="admin-card__head"><h2><?= icon('zap', 18) ?> Görsel efektler</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('theme_preloader', ['label' => 'Ön yükleme ekranı', 'type' => 'bool', 'col' => 2], $g('theme.preloader'));
            admin_field('theme_cursor', ['label' => 'Özel fare imleci', 'type' => 'bool', 'col' => 2], $g('theme.cursor'));
            admin_field('theme_particles', ['label' => 'Partikül arka planı', 'type' => 'bool', 'col' => 2], $g('theme.particles'));
            admin_field('theme_noise', ['label' => 'Film grenli doku', 'type' => 'bool', 'col' => 2], $g('theme.noise'));
            ?>
        </div>
        <div class="form-note mt-4">
            <?= icon('info', 17) ?>
            <span>Efektler, hareket azaltma tercihi açık olan ziyaretçilerde otomatik devre dışı kalır ve dokunmatik cihazlarda özel imleç gösterilmez.</span>
        </div>
    </div>

    <!-- ============ SEO ============ -->
    <div class="admin-card admin-tab-panel" data-tab-panel="seo" id="seo">
        <div class="admin-card__head"><h2><?= icon('search', 18) ?> Arama motoru ayarları</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('seo_ga_id', ['label' => 'Google Analytics kimliği', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: G-XXXXXXXXXX'], $g('seo.ga_id'));
            admin_field('seo_search_console', ['label' => 'Search Console doğrulama kodu', 'type' => 'text', 'col' => 2], $g('seo.search_console'));
            admin_field('seo_robots', ['label' => 'Robots yönergesi', 'type' => 'select', 'col' => 2,
                'options' => ['index, follow' => 'index, follow (önerilen)', 'noindex, nofollow' => 'noindex, nofollow (gizle)']], $g('seo.robots'));
            admin_field('seo_canonical', ['label' => 'Kanonik alan adı', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: https://siteniz.com — boş bırakılırsa otomatik algılanır.'], $g('seo.canonical'));
            ?>
        </div>

        <div class="form-note mt-4">
            <?= icon('info', 17) ?>
            <span>Site haritanız otomatik oluşturulur: <a href="<?= e(base_url('sitemap.php')) ?>" target="_blank" style="color:var(--accent)"><?= e(base_url('sitemap.php')) ?></a></span>
        </div>
    </div>

    <!-- ============ MODÜLLER ============ -->
    <div class="admin-card admin-tab-panel" data-tab-panel="moduller" id="moduller">
        <div class="admin-card__head"><h2><?= icon('grid', 18) ?> Görünecek bölümler</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('feat_blog', ['label' => 'Blog bölümü', 'type' => 'bool', 'col' => 2], $g('features.blog'));
            admin_field('feat_testimonials', ['label' => 'Müşteri görüşleri', 'type' => 'bool', 'col' => 2], $g('features.testimonials'));
            admin_field('feat_packages', ['label' => 'Fiyat paketleri', 'type' => 'bool', 'col' => 2], $g('features.packages'));
            admin_field('feat_faq', ['label' => 'SSS bölümü', 'type' => 'bool', 'col' => 2], $g('features.faq'));
            admin_field('feat_stats', ['label' => 'İstatistik sayaçları', 'type' => 'bool', 'col' => 2], $g('features.stats'));
            admin_field('feat_clients', ['label' => 'Müşteri logo şeridi', 'type' => 'bool', 'col' => 2], $g('features.clients'));
            ?>
        </div>

        <hr class="divider">

        <div class="admin-card__head"><h2><?= icon('alert', 18) ?> Bakım modu</h2></div>
        <div class="admin-form-grid">
            <?php
            admin_field('maint_enabled', ['label' => 'Bakım modunu etkinleştir', 'type' => 'bool'], $g('maintenance.enabled'));
            admin_field('maint_message', ['label' => 'Bakım mesajı', 'type' => 'textarea', 'rows' => 2], $g('maintenance.message'));
            ?>
        </div>
        <div class="form-note mt-4">
            <?= icon('info', 17) ?>
            <span>Bakım modunda ziyaretçiler bilgilendirme sayfası görür; siz oturum açtığınız için siteyi normal görüntülemeye devam edersiniz.</span>
        </div>
    </div>

    <div class="admin-sticky-save">
        <button class="btn btn--primary" type="submit"><?= icon('check', 17) ?><span>Ayarları kaydet</span></button>
        <a class="btn btn--ghost" href="<?= e(base_url()) ?>" target="_blank"><?= icon('external', 16) ?><span>Siteyi önizle</span></a>
    </div>
</form>

<?php admin_footer(); ?>
