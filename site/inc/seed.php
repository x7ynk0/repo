<?php
/**
 * İlk çalıştırmada veri dosyalarını örnek içerikle oluşturur.
 * Var olan dosyalara asla dokunmaz.
 */

declare(strict_types=1);

function seed_data_files(): void
{
    foreach (seed_definitions() as $name => $factory) {
        if (!Store::exists($name)) {
            Store::write($name, $factory());
        }
    }
}

/** @return array<string, callable():array> */
function seed_definitions(): array
{
    return [
        'users'        => static fn(): array => [],
        'logs'         => static fn(): array => [],
        'messages'     => static fn(): array => [],
        'stats'        => 'seed_stats',
        'settings'     => 'seed_settings',
        'services'     => 'seed_services',
        'packages'     => 'seed_packages',
        'process'      => 'seed_process',
        'projects'     => 'seed_projects',
        'testimonials' => 'seed_testimonials',
        'skills'       => 'seed_skills',
        'faq'          => 'seed_faq',
        'posts'        => 'seed_posts',
        'clients'      => 'seed_clients',
    ];
}

function seed_stats(): array
{
    return [
        'total_visits' => 0,
        'daily'        => [],
    ];
}

function seed_settings(): array
{
    return [
        'site' => [
            'name'        => 'Oxit Studio',
            'short_name'  => 'OXIT',
            'tagline'     => 'Dijital ürünlerinizi kod ile büyütüyoruz',
            'description' => 'Web ve mobil uygulama geliştirme, SEO, sosyal medya yönetimi ve dijital dönüşüm hizmetleri. Ölçülebilir sonuç üreten yazılım çözümleri.',
            'keywords'    => 'web tasarım, yazılım, mobil uygulama, SEO, e-ticaret, sosyal medya yönetimi, yapay zeka entegrasyonu',
            'logo'        => '',
            'favicon'     => '',
            'og_image'    => '',
            'author'      => 'Oxit Studio',
            'copyright'   => '© ' . date('Y') . ' Oxit Studio. Tüm hakları saklıdır.',
            'lang'        => 'tr',
        ],
        'theme' => [
            'accent'     => '#7c5cff',
            'accent2'    => '#22d3ee',
            'accent3'    => '#f472b6',
            'radius'     => '18',
            'mode'       => 'dark',
            'cursor'     => true,
            'preloader'  => true,
            'particles'  => true,
            'noise'      => true,
        ],
        'hero' => [
            'badge'        => 'Yeni projeler için müsait',
            'title_lines'  => ["Fikirlerinizi", "üretime hazır", "yazılıma çeviriyoruz"],
            'rotating'     => ['Web Uygulamaları', 'Mobil Uygulamalar', 'E-Ticaret Sistemleri', 'SEO & Büyüme', 'Yapay Zekâ Entegrasyonu'],
            'subtitle'     => 'Kurumsal web sitelerinden ölçeklenebilir SaaS platformlarına kadar; tasarımı, kodu, altyapıyı ve büyüme stratejisini tek elden yönetiyoruz.',
            'cta_primary'  => 'Ücretsiz Keşif Görüşmesi',
            'cta_secondary'=> 'Çalışmaları İncele',
            'code_snippet' => "class Project {\n  constructor(idea) {\n    this.idea = idea;\n    this.stack = ['PHP', 'React', 'Node'];\n  }\n\n  ship() {\n    return deploy(this.idea, {\n      speed: '99/100',\n      seo: 'optimized',\n      uptime: '99.9%'\n    });\n  }\n}",
        ],
        'stats' => [
            ['value' => 180, 'suffix' => '+', 'label' => 'Tamamlanan Proje'],
            ['value' => 96,  'suffix' => '%', 'label' => 'Müşteri Memnuniyeti'],
            ['value' => 9,   'suffix' => ' yıl', 'label' => 'Sektör Deneyimi'],
            ['value' => 24,  'suffix' => '/7', 'label' => 'Teknik Destek'],
        ],
        'about' => [
            'title'    => 'Kod yazmıyoruz; işinizi büyüten sistemler kuruyoruz',
            'subtitle' => 'Hakkımızda',
            'image'    => '',
            'body'     => "9 yıldır start-up'lardan kurumsal firmalara kadar 180'den fazla dijital ürün geliştirdik. Her projeye \"nasıl yaparız\" sorusundan önce \"neden yapıyoruz\" sorusuyla başlıyoruz.\n\nBizim için bir proje, tasarım dosyası teslim edildiğinde değil; ölçülebilir iş sonucu ürettiğinde biter. Bu yüzden analiz, geliştirme, test, yayına alma ve büyüme aşamalarının hepsinde yanınızdayız.",
            'highlights' => [
                'Sabit fiyat, sürprizsiz teklif',
                'Kaynak kodun tamamı size ait',
                'Yayın sonrası 3 ay ücretsiz destek',
                'Haftalık ilerleme raporu',
            ],
        ],
        'contact' => [
            'email'     => 'merhaba@oxitstudio.com',
            'phone'     => '+90 (532) 000 00 00',
            'whatsapp'  => '905320000000',
            'address'   => 'Levent, İstanbul, Türkiye',
            'work_hours'=> 'Pazartesi – Cuma, 09:00 – 19:00',
            'map'       => '',
            'reply_time'=> 'Ortalama yanıt süresi: 2 saat',
        ],
        'social' => [
            'github'    => 'https://github.com/',
            'linkedin'  => 'https://linkedin.com/',
            'x'         => 'https://x.com/',
            'instagram' => 'https://instagram.com/',
            'youtube'   => '',
            'dribbble'  => '',
            'behance'   => '',
        ],
        'cta' => [
            'title'    => 'Projenizi konuşalım',
            'subtitle' => 'İlk görüşme ücretsizdir. 30 dakikada ihtiyacınızı netleştirir, size yol haritası ve net bir bütçe aralığı sunarız.',
            'button'   => 'Teklif Al',
        ],
        'seo' => [
            'ga_id'          => '',
            'search_console' => '',
            'robots'         => 'index, follow',
            'canonical'      => '',
        ],
        'features' => [
            'blog'         => true,
            'testimonials' => true,
            'packages'     => true,
            'faq'          => true,
            'stats'        => true,
            'clients'      => true,
        ],
        'maintenance' => [
            'enabled' => false,
            'message' => 'Sitemiz kısa süreli bakımda. Kısa süre içinde geri döneceğiz.',
        ],
    ];
}

function seed_services(): array
{
    $rows = [
        [
            'title'   => 'Kurumsal Web Sitesi Geliştirme',
            'icon'    => 'globe',
            'excerpt' => 'Markanızı ilk saniyede anlatan, hızlı ve arama motoru dostu kurumsal web siteleri.',
            'body'    => "Kurumsal web siteniz dijital vitrininizdir. Ziyaretçinin ilk 3 saniyede aldığı izlenim, satın alma kararının büyük bölümünü belirler.\n\n## Neler yapıyoruz\nMarka analizi ve rakip incelemesiyle başlıyor; bilgi mimarisi, wireframe ve UI tasarımının ardından piksel hassasiyetinde kodlama yapıyoruz. Tüm sayfalar Core Web Vitals metriklerine göre optimize edilir.\n\n## Teknik yaklaşım\n- Sunucu tarafı render ile 90+ PageSpeed skoru\n- Yönetilebilir içerik paneli (blog, referans, ekip, hizmet)\n- Çok dilli altyapı ve hreflang desteği\n- WCAG 2.1 AA erişilebilirlik uyumu",
            'features'=> ['Özel UI/UX tasarımı', 'Yönetim paneli', 'Çok dil desteği', 'Core Web Vitals optimizasyonu', 'Erişilebilirlik (a11y)', 'Analitik entegrasyonu'],
            'price_from' => 24000,
            'duration'   => '2 – 4 hafta',
            'featured'   => true,
        ],
        [
            'title'   => 'Mobil Uygulama Geliştirme',
            'icon'    => 'mobile',
            'excerpt' => 'iOS ve Android için tek kod tabanıyla yüksek performanslı, mağaza onaylı uygulamalar.',
            'body'    => "React Native ve Flutter ile tek kod tabanından iki platforma da native hissiyatında uygulamalar üretiyoruz.\n\n## Kapsam\nFikir doğrulama atölyesi, kullanıcı akış haritası, tasarım sistemi, geliştirme, mağaza yayını ve yayın sonrası analitik kurulumu dahildir.\n\n## Öne çıkanlar\n- Push bildirim ve derin bağlantı (deep link)\n- Çevrimdışı çalışma ve yerel önbellek\n- Biyometrik giriş, güvenli token saklama\n- App Store & Google Play yayın yönetimi\n- Crash raporlama ve kullanıcı davranış analitiği",
            'features'=> ['React Native / Flutter', 'Push bildirim', 'Çevrimdışı mod', 'Mağaza yayın süreci', 'Analitik & crash raporu', 'CI/CD ile otomatik sürüm'],
            'price_from' => 65000,
            'duration'   => '6 – 12 hafta',
            'featured'   => true,
        ],
        [
            'title'   => 'E-Ticaret Sistemleri',
            'icon'    => 'cart',
            'excerpt' => 'Satışa odaklı, hızlı ödeme akışlı, entegrasyonu tam e-ticaret altyapıları.',
            'body'    => "Ürün listeleme hızından ödeme adımına kadar her ekranı dönüşüm oranı için tasarlıyoruz.\n\n## Entegrasyonlar\nSanal POS (iyzico, PayTR, Stripe), kargo firmaları, e-fatura, muhasebe ve ERP sistemleriyle çift yönlü entegrasyon kuruyoruz.\n\n## Dönüşüm odaklı detaylar\n- Tek sayfa ödeme (one-page checkout)\n- Terk edilmiş sepet hatırlatması\n- Akıllı arama ve filtreleme\n- Pazaryeri (Trendyol, Hepsiburada) senkronizasyonu",
            'features'=> ['Sanal POS entegrasyonu', 'Kargo & e-fatura', 'Pazaryeri senkronizasyonu', 'Terk edilmiş sepet', 'Kampanya motoru', 'Çok depolu stok yönetimi'],
            'price_from' => 48000,
            'duration'   => '4 – 8 hafta',
            'featured'   => true,
        ],
        [
            'title'   => 'SEO & Arama Motoru Optimizasyonu',
            'icon'    => 'search',
            'excerpt' => 'Teknik SEO, içerik stratejisi ve otorite inşasıyla organik trafiğinizi kalıcı biçimde büyütün.',
            'body'    => "SEO bir kez yapılan iş değil, sürekli işleyen bir sistemdir. Biz bu sistemi kuruyor ve ölçüyoruz.\n\n## Teknik SEO\nTarama bütçesi, indeksleme, site hızı, yapısal veri (schema.org), sayfalama, canonical ve hreflang yapılandırması.\n\n## İçerik ve otorite\nAnahtar kelime kümeleri, arama niyeti analizi, içerik takvimi, iç bağlantı mimarisi ve kaliteli backlink stratejisi.\n\n## Raporlama\nAylık; sıralama, tıklama, gösterim ve dönüşüm bazlı şeffaf rapor. Sadece trafik değil, gelir etkisini raporluyoruz.",
            'features'=> ['Teknik SEO denetimi', 'Anahtar kelime araştırması', 'İçerik stratejisi', 'Schema.org işaretleme', 'Backlink analizi', 'Aylık performans raporu'],
            'price_from' => 12000,
            'duration'   => 'Aylık sürekli',
            'featured'   => true,
        ],
        [
            'title'   => 'Sosyal Medya Yönetimi',
            'icon'    => 'share',
            'excerpt' => 'İçerik üretimi, topluluk yönetimi ve reklam optimizasyonuyla markanızı büyütüyoruz.',
            'body'    => "Sosyal medyada düzenli paylaşım yetmez; doğru kanalda, doğru formatta, doğru mesaj gerekir.\n\n## Hizmet kapsamı\nAylık içerik takvimi, görsel ve video tasarımı, metin yazarlığı, yayın planlaması, topluluk yönetimi ve reklam yönetimi.\n\n## Reklam yönetimi\nMeta Ads, Google Ads, TikTok Ads ve LinkedIn Ads hesaplarınızı ROAS hedefiyle yönetiyoruz. A/B testleriyle sürekli optimize ediyoruz.",
            'features'=> ['İçerik takvimi', 'Görsel & video tasarım', 'Reklam yönetimi (Meta/Google)', 'Topluluk yönetimi', 'Influencer iş birlikleri', 'Aylık analiz raporu'],
            'price_from' => 15000,
            'duration'   => 'Aylık sürekli',
            'featured'   => true,
        ],
        [
            'title'   => 'UI/UX Tasarım & Tasarım Sistemi',
            'icon'    => 'palette',
            'excerpt' => 'Kullanıcı araştırmasından ölçeklenebilir tasarım sistemine uzanan uçtan uca tasarım.',
            'body'    => "Güzel görünen değil, işe yarayan arayüzler tasarlıyoruz. Her tasarım kararının arkasında bir veri veya kullanıcı içgörüsü var.\n\n## Süreç\nKullanıcı görüşmeleri → persona → kullanıcı akışı → wireframe → yüksek çözünürlüklü tasarım → prototip → kullanılabilirlik testi.\n\n## Tasarım sistemi\nRenk, tipografi, boşluk ölçeği, bileşen kütüphanesi ve kullanım kuralları içeren, ekibinizin yıllarca kullanabileceği bir sistem teslim ediyoruz.",
            'features'=> ['Kullanıcı araştırması', 'Wireframe & prototip', 'Figma tasarım sistemi', 'Kullanılabilirlik testi', 'Mikro etkileşim tasarımı', 'Geliştirici teslim dosyaları'],
            'price_from' => 28000,
            'duration'   => '3 – 6 hafta',
            'featured'   => false,
        ],
        [
            'title'   => 'Özel Yazılım & SaaS Geliştirme',
            'icon'    => 'cpu',
            'excerpt' => 'İş süreçlerinize birebir uyan, ölçeklenebilir kurumsal yazılım ve SaaS platformları.',
            'body'    => "Hazır çözümlerin yetmediği yerde devreye giriyoruz. CRM, ERP, rezervasyon, saha yönetimi, abonelik tabanlı SaaS ürünleri.\n\n## Mimari\nAlan odaklı tasarım (DDD), servis katmanı ayrımı, olay tabanlı iletişim ve yatay ölçeklenebilir altyapı.\n\n## Kalite güvencesi\nBirim testleri, entegrasyon testleri, statik analiz ve kod incelemesi standart süreçlerimizdir. Test kapsamı %80'in altına düşmez.",
            'features'=> ['Çok kiracılı (multi-tenant) mimari', 'Rol bazlı yetkilendirme', 'Abonelik & faturalama', 'REST/GraphQL API', 'Otomatik test altyapısı', 'Teknik dokümantasyon'],
            'price_from' => 90000,
            'duration'   => '8 – 20 hafta',
            'featured'   => true,
        ],
        [
            'title'   => 'Yapay Zekâ Entegrasyonu',
            'icon'    => 'bot',
            'excerpt' => 'Ürününüze akıllı asistan, öneri motoru ve otomatik içerik üretimi ekleyin.',
            'body'    => "Büyük dil modellerini iş süreçlerinize güvenli biçimde entegre ediyoruz.\n\n## Kullanım senaryoları\nMüşteri destek asistanı, kendi dokümanlarınız üzerinde soru-cevap (RAG), otomatik içerik üretimi, akıllı arama, öneri sistemleri ve belge işleme otomasyonu.\n\n## Güvenlik\nVeri sızıntısını önleyen istem (prompt) izolasyonu, maliyet limitleri, denetim kayıtları ve kişisel veri maskeleme uygularız.",
            'features'=> ['LLM tabanlı sohbet asistanı', 'RAG (kendi verinizle soru-cevap)', 'Öneri motoru', 'Belge/OCR otomasyonu', 'Maliyet ve kota kontrolü', 'KVKK uyumlu veri işleme'],
            'price_from' => 45000,
            'duration'   => '3 – 8 hafta',
            'featured'   => true,
        ],
        [
            'title'   => 'Performans & Hız Optimizasyonu',
            'icon'    => 'zap',
            'excerpt' => 'Yavaş site satış kaybettirir. Sayfa hızınızı ölçüp somut biçimde iyileştiriyoruz.',
            'body'    => "100 ms'lik gecikme dönüşüm oranında %1'e varan kayıp demek. Mevcut sisteminizi baştan yazmadan hızlandırıyoruz.\n\n## Denetim kapsamı\nLighthouse ve WebPageTest analizleri, sunucu yanıt süresi (TTFB), veritabanı sorgu profillemesi, görsel optimizasyonu, önbellek stratejisi ve CDN yapılandırması.\n\n## Sonuç\nÇalışmalarımız sonunda önce/sonra karşılaştırmalı rapor sunuyor, elde edilen kazanımı sayısal olarak gösteriyoruz.",
            'features'=> ['Lighthouse denetimi', 'TTFB & sorgu optimizasyonu', 'Görsel/CDN stratejisi', 'Önbellek katmanı', 'Kod bölme (code splitting)', 'Önce/sonra raporu'],
            'price_from' => 9000,
            'duration'   => '1 – 2 hafta',
            'featured'   => false,
        ],
        [
            'title'   => 'DevOps, Bulut & Sunucu Yönetimi',
            'icon'    => 'cloud',
            'excerpt' => 'Kesintisiz çalışan, otomatik ölçeklenen ve izlenen bir altyapı kuruyoruz.',
            'body'    => "Uygulamanız kadar altyapınız da kritiktir. Yayına alma sürecini otomatikleştiriyor, gece yarısı alarmlarına son veriyoruz.\n\n## Kapsam\nDocker ile konteynerleştirme, CI/CD boru hattı, otomatik yedekleme, log toplama, uptime izleme, güvenlik duvarı ve SSL yönetimi.\n\n## Bulut sağlayıcılar\nAWS, Google Cloud, DigitalOcean, Hetzner ve yerli sağlayıcılarda çalışıyoruz.",
            'features'=> ['Docker & konteyner', 'CI/CD boru hattı', 'Otomatik yedekleme', 'Uptime & log izleme', 'SSL + WAF yapılandırması', 'Felaket kurtarma planı'],
            'price_from' => 18000,
            'duration'   => '1 – 3 hafta',
            'featured'   => false,
        ],
        [
            'title'   => 'Siber Güvenlik & Sızma Testi',
            'icon'    => 'shield',
            'excerpt' => 'Uygulamanızdaki açıkları saldırganlardan önce biz bulalım.',
            'body'    => "OWASP Top 10 başta olmak üzere kapsamlı güvenlik denetimi yapıyoruz.\n\n## Denetim başlıkları\nSQL/NoSQL enjeksiyonu, XSS, CSRF, yetki yükseltme, oturum yönetimi, dosya yükleme zafiyetleri, bağımlılık taraması ve yapılandırma hataları.\n\n## Teslimat\nRiskleri kritiklik seviyesine göre sıralayan teknik rapor, kanıt ekran görüntüleri ve her bulgu için uygulanabilir çözüm önerisi.",
            'features'=> ['OWASP Top 10 denetimi', 'Bağımlılık zafiyet taraması', 'Yetkilendirme testleri', 'KVKK uyum kontrolü', 'Detaylı teknik rapor', 'Düzeltme sonrası yeniden test'],
            'price_from' => 22000,
            'duration'   => '1 – 3 hafta',
            'featured'   => false,
        ],
        [
            'title'   => 'API Geliştirme & Sistem Entegrasyonu',
            'icon'    => 'link',
            'excerpt' => 'Sistemlerinizin birbiriyle konuşmasını sağlayan sağlam ve belgelenmiş API\'ler.',
            'body'    => "Muhasebe yazılımınız, e-ticaret siteniz ve CRM'iniz birbirinden habersiz çalışıyorsa, veri girişine harcanan her saat kayıptır.\n\n## Neler sunuyoruz\nREST ve GraphQL API tasarımı, OpenAPI dokümantasyonu, sürüm yönetimi, hız sınırlama, kimlik doğrulama (OAuth2/JWT) ve webhook altyapısı.\n\n## Entegrasyon deneyimi\nLogo, Netsis, Mikro, SAP, Salesforce, HubSpot, Trendyol, Hepsiburada, iyzico, PayTR ve daha fazlası.",
            'features'=> ['REST & GraphQL', 'OpenAPI dokümantasyon', 'OAuth2 / JWT güvenlik', 'Webhook altyapısı', 'Hız sınırlama & kota', 'ERP/CRM entegrasyonu'],
            'price_from' => 20000,
            'duration'   => '2 – 6 hafta',
            'featured'   => false,
        ],
        [
            'title'   => 'Veri Analitiği & İş Zekâsı',
            'icon'    => 'chart',
            'excerpt' => 'Verinizi karar destek sistemine dönüştüren panolar ve raporlama altyapısı.',
            'body'    => "Elinizdeki veri, doğru sorularla sorulduğunda en değerli varlığınız.\n\n## Kapsam\nVeri toplama katmanı (ETL), veri ambarı modellemesi, gerçek zamanlı panolar, otomatik e-posta raporları ve kohort/huni analizleri.\n\n## Araçlar\nGoogle Analytics 4, BigQuery, Metabase, Grafana ve özel geliştirdiğimiz panolar.",
            'features'=> ['ETL veri boru hattı', 'Gerçek zamanlı panolar', 'Huni & kohort analizi', 'Otomatik rapor gönderimi', 'GA4 & sunucu taraflı izleme', 'KPI tanımlama atölyesi'],
            'price_from' => 26000,
            'duration'   => '3 – 6 hafta',
            'featured'   => false,
        ],
        [
            'title'   => 'Marka Kimliği & Dijital Danışmanlık',
            'icon'    => 'bulb',
            'excerpt' => 'Logo, kurumsal kimlik ve dijital yol haritası ile markanızı konumlandırıyoruz.',
            'body'    => "Teknoloji kararları pazarlama kararlarından bağımsız alınamaz. İkisini birlikte planlıyoruz.\n\n## Marka kimliği\nLogo tasarımı, renk paleti, tipografi sistemi, kurumsal kimlik kılavuzu ve sosyal medya şablonları.\n\n## Dijital danışmanlık\nMevcut durum analizi, rakip kıyaslaması, 12 aylık dijital yol haritası ve bütçe planı.",
            'features'=> ['Logo & kurumsal kimlik', 'Marka kılavuzu', 'Rakip analizi', '12 aylık yol haritası', 'Bütçe planlaması', 'Aylık danışmanlık görüşmesi'],
            'price_from' => 16000,
            'duration'   => '2 – 4 hafta',
            'featured'   => false,
        ],
        [
            'title'   => 'Bakım, Destek & Sürekli Geliştirme',
            'icon'    => 'headset',
            'excerpt' => 'Yayına aldıktan sonra da yanınızdayız: güncelleme, yedekleme ve öncelikli destek.',
            'body'    => "Yazılım canlı bir organizmadır; bakılmazsa yaşlanır.\n\n## Aylık bakım paketi\nGüvenlik güncellemeleri, yedek doğrulama, uptime izleme, küçük geliştirme talepleri ve öncelikli destek kanalı.\n\n## Hizmet seviyesi (SLA)\nKritik arızalarda 2 saat içinde müdahale, 24 saat içinde çözüm taahhüdü.",
            'features'=> ['Aylık güvenlik güncellemesi', 'Yedek doğrulama', '7/24 uptime izleme', 'Öncelikli destek kanalı', 'Aylık geliştirme kotası', 'SLA taahhüdü'],
            'price_from' => 6000,
            'duration'   => 'Aylık sürekli',
            'featured'   => false,
        ],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, [
            'id'         => Store::uid(),
            'slug'       => slugify($r['title']),
            'order'      => $i + 1,
            'active'     => true,
            'image'      => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
    return $out;
}

function seed_packages(): array
{
    $rows = [
        [
            'title'    => 'Başlangıç',
            'subtitle' => 'Yeni markalar ve kişisel projeler için',
            'price'    => 24000,
            'period'   => 'proje başı',
            'features' => [
                '5 sayfaya kadar kurumsal site',
                'Mobil uyumlu özel tasarım',
                'Temel SEO kurulumu',
                'İletişim formu + WhatsApp',
                'Yönetim paneli',
                '1 ay ücretsiz destek',
            ],
            'excluded' => ['Çok dilli yapı', 'E-ticaret modülü', 'Aylık içerik üretimi'],
            'featured' => false,
            'badge'    => '',
            'cta'      => 'Teklif Al',
        ],
        [
            'title'    => 'Profesyonel',
            'subtitle' => 'Büyümeye odaklanan işletmeler için',
            'price'    => 58000,
            'period'   => 'proje başı',
            'features' => [
                '15 sayfaya kadar site + blog',
                'Özel UI/UX tasarım sistemi',
                'Gelişmiş teknik SEO paketi',
                'Çok dilli altyapı',
                'CRM & analitik entegrasyonu',
                'Performans optimizasyonu (90+ skor)',
                '3 ay ücretsiz destek',
            ],
            'excluded' => ['Mobil uygulama'],
            'featured' => true,
            'badge'    => 'En Çok Tercih Edilen',
            'cta'      => 'Hemen Başlayalım',
        ],
        [
            'title'    => 'Kurumsal',
            'subtitle' => 'Özel yazılım ve ölçekli ihtiyaçlar için',
            'price'    => 0,
            'period'   => 'ihtiyaca göre',
            'features' => [
                'Sınırsız sayfa & özel modüller',
                'Özel yazılım / SaaS geliştirme',
                'Mobil uygulama (iOS + Android)',
                'DevOps & bulut altyapı kurulumu',
                'Yapay zekâ entegrasyonu',
                'Adanmış proje yöneticisi',
                'SLA taahhütlü 12 ay destek',
            ],
            'excluded' => [],
            'featured' => false,
            'badge'    => 'Kurumsal',
            'cta'      => 'Görüşme Planla',
        ],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, [
            'id'     => Store::uid(),
            'order'  => $i + 1,
            'active' => true,
        ]);
    }
    return $out;
}

function seed_process(): array
{
    $rows = [
        ['title' => 'Keşif & Analiz', 'icon' => 'search', 'duration' => '1. Hafta',
            'body' => 'İş hedeflerinizi, hedef kitlenizi ve rakiplerinizi analiz ediyoruz. Çıktı: kapsam dokümanı ve başarı metrikleri.'],
        ['title' => 'Strateji & Yol Haritası', 'icon' => 'target', 'duration' => '1 – 2. Hafta',
            'body' => 'Teknoloji seçimi, bilgi mimarisi ve sprint planı belirlenir. Net teslim tarihleri ve sabit bütçe onaylanır.'],
        ['title' => 'Tasarım & Prototip', 'icon' => 'palette', 'duration' => '2 – 4. Hafta',
            'body' => 'Wireframe’den yüksek çözünürlüklü tasarıma; tıklanabilir prototiple ürünü kod yazılmadan önce deneyimlersiniz.'],
        ['title' => 'Geliştirme', 'icon' => 'terminal', 'duration' => '4 – 10. Hafta',
            'body' => 'İki haftalık sprintlerle geliştirme. Her sprint sonunda çalışan sürümü canlı test ortamında görürsünüz.'],
        ['title' => 'Test & Kalite Kontrol', 'icon' => 'shield', 'duration' => 'Son 2 Hafta',
            'body' => 'Fonksiyonel testler, tarayıcı/cihaz uyumluluğu, performans, güvenlik ve erişilebilirlik denetimi.'],
        ['title' => 'Yayına Alma & Büyüme', 'icon' => 'rocket', 'duration' => 'Yayın + Sonrası',
            'body' => 'Sıfır kesintili yayın, analitik kurulumu, ekip eğitimi ve yayın sonrası büyüme desteği.'],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, [
            'id'     => Store::uid(),
            'step'   => $i + 1,
            'order'  => $i + 1,
            'active' => true,
        ]);
    }
    return $out;
}

function seed_projects(): array
{
    $rows = [
        [
            'title'    => 'Nexora — B2B SaaS Panosu',
            'client'   => 'Nexora Teknoloji',
            'category' => 'SaaS / Web Uygulaması',
            'excerpt'  => 'Çok kiracılı abonelik platformu; 40.000+ aktif kullanıcı, %38 daha hızlı raporlama.',
            'body'     => "Nexora, saha ekiplerini yöneten firmalar için abonelik tabanlı bir platform istiyordu.\n\n## Problem\nMevcut sistem tek kiracılıydı; her yeni müşteri için ayrı kurulum gerekiyordu ve raporlar 30 saniyeye varan sürelerde açılıyordu.\n\n## Çözüm\nÇok kiracılı mimariye geçtik, raporlama katmanını okuma replikası ve önbellek üzerine taşıdık.\n\n## Sonuç\n- Rapor açılış süresi 30 sn → 1,8 sn\n- Yeni müşteri kurulumu 3 gün → 4 dakika\n- Altyapı maliyeti %27 azaldı",
            'tags'     => ['React', 'Node.js', 'PostgreSQL', 'Redis', 'AWS'],
            'metrics'  => [['label' => 'Rapor hızı', 'value' => '16x'], ['label' => 'Aktif kullanıcı', 'value' => '40B+'], ['label' => 'Maliyet düşüşü', 'value' => '%27']],
            'url'      => '',
            'year'     => '2025',
            'featured' => true,
        ],
        [
            'title'    => 'Marla — Moda E-Ticaret',
            'client'   => 'Marla Studio',
            'category' => 'E-Ticaret',
            'excerpt'  => 'Yeniden tasarlanan ödeme akışıyla dönüşüm oranında %64 artış.',
            'body'     => "Sepete eklenen ürünlerin %78'i satın alınmadan terk ediliyordu.\n\n## Problem\nÖdeme akışı 5 adımdı ve mobilde form alanları çok uzundu.\n\n## Çözüm\nTek sayfa ödeme, misafir alışverişi, adres otomatik tamamlama ve cüzdan ödemeleri eklendi.\n\n## Sonuç\n- Dönüşüm oranı %1,4 → %2,3\n- Terk edilen sepet %78 → %52\n- Mobil ciro payı %41 → %63",
            'tags'     => ['PHP', 'Laravel', 'Alpine.js', 'iyzico', 'Elasticsearch'],
            'metrics'  => [['label' => 'Dönüşüm artışı', 'value' => '%64'], ['label' => 'Sepet terki', 'value' => '-26p'], ['label' => 'Mobil ciro', 'value' => '%63']],
            'url'      => '',
            'year'     => '2025',
            'featured' => true,
        ],
        [
            'title'    => 'Kardio — Sağlık Takip Uygulaması',
            'client'   => 'Kardio Health',
            'category' => 'Mobil Uygulama',
            'excerpt'  => 'iOS & Android; App Store Sağlık kategorisinde 4,8 puan, 120.000 indirme.',
            'body'     => "Kalp hastalarının ilaç ve ölçüm takibini kolaylaştıran bir uygulama.\n\n## Öne çıkanlar\n- Apple Health & Google Fit senkronizasyonu\n- Akıllı ilaç hatırlatıcıları\n- Doktorla paylaşılabilir PDF rapor\n- Çevrimdışı veri girişi ve otomatik senkron\n\n## Sonuç\nİlk 6 ayda 120.000 indirme, %71 30 günlük kullanıcı tutma oranı.",
            'tags'     => ['React Native', 'TypeScript', 'Firebase', 'HealthKit'],
            'metrics'  => [['label' => 'İndirme', 'value' => '120B'], ['label' => 'Mağaza puanı', 'value' => '4.8'], ['label' => 'Tutma oranı', 'value' => '%71']],
            'url'      => '',
            'year'     => '2024',
            'featured' => true,
        ],
        [
            'title'    => 'Voltra — Kurumsal Web Sitesi',
            'client'   => 'Voltra Enerji',
            'category' => 'Kurumsal Web',
            'excerpt'  => 'Çok dilli kurumsal site; organik trafikte 6 ayda %210 artış.',
            'body'     => "Enerji sektöründe faaliyet gösteren Voltra için 4 dilli kurumsal site.\n\n## Kapsam\nBilgi mimarisi, tasarım sistemi, headless CMS, teknik SEO ve yatırımcı ilişkileri modülü.\n\n## Sonuç\n- Organik trafik +%210\n- Ortalama oturum süresi 1:12 → 3:04\n- Lighthouse performans skoru 98",
            'tags'     => ['Next.js', 'Headless CMS', 'SEO', 'i18n'],
            'metrics'  => [['label' => 'Organik trafik', 'value' => '+%210'], ['label' => 'PageSpeed', 'value' => '98'], ['label' => 'Dil', 'value' => '4']],
            'url'      => '',
            'year'     => '2025',
            'featured' => true,
        ],
        [
            'title'    => 'Bloom — Yapay Zekâ Destek Asistanı',
            'client'   => 'Bloom CRM',
            'category' => 'Yapay Zekâ',
            'excerpt'  => 'Kendi dokümanları üzerinde çalışan asistan; destek taleplerinin %62\'sini otomatik çözüyor.',
            'body'     => "Bloom'un destek ekibi günde 900+ tekrar eden soruya cevap veriyordu.\n\n## Çözüm\nRAG mimarisiyle şirket dokümanları vektör veritabanına aktarıldı; asistan yalnızca doğrulanmış kaynaklardan yanıt üretiyor ve emin olmadığında insana devrediyor.\n\n## Sonuç\n- Taleplerin %62'si otomatik çözüldü\n- İlk yanıt süresi 4 saat → 8 saniye\n- Destek maliyeti %35 azaldı",
            'tags'     => ['Python', 'LLM', 'RAG', 'pgvector', 'FastAPI'],
            'metrics'  => [['label' => 'Otomatik çözüm', 'value' => '%62'], ['label' => 'İlk yanıt', 'value' => '8 sn'], ['label' => 'Maliyet', 'value' => '-%35']],
            'url'      => '',
            'year'     => '2026',
            'featured' => true,
        ],
        [
            'title'    => 'Rota — Lojistik Yönetim Sistemi',
            'client'   => 'Rota Lojistik',
            'category' => 'Özel Yazılım',
            'excerpt'  => 'Araç rotalama ve sevkiyat optimizasyonuyla yakıt maliyetinde %19 tasarruf.',
            'body'     => "300 araçlık filoyu yöneten özel bir sistem geliştirdik.\n\n## Kapsam\nCanlı araç takibi, rota optimizasyonu, sürücü mobil uygulaması, e-irsaliye entegrasyonu ve yönetici panosu.\n\n## Sonuç\n- Yakıt maliyeti -%19\n- Günlük teslimat kapasitesi +%24\n- Manuel planlama süresi 4 saat → 20 dakika",
            'tags'     => ['Vue.js', 'Go', 'PostGIS', 'Docker'],
            'metrics'  => [['label' => 'Yakıt tasarrufu', 'value' => '%19'], ['label' => 'Kapasite', 'value' => '+%24'], ['label' => 'Araç', 'value' => '300']],
            'url'      => '',
            'year'     => '2024',
            'featured' => false,
        ],
        [
            'title'    => 'Fusio — Sosyal Medya Büyüme Kampanyası',
            'client'   => 'Fusio Kozmetik',
            'category' => 'Sosyal Medya',
            'excerpt'  => '6 ayda 12.000 → 189.000 takipçi ve 4,6x reklam getirisi.',
            'body'     => "Yeni kurulan bir kozmetik markası için sıfırdan sosyal medya stratejisi.\n\n## Yapılanlar\nİçerik serileri, kısa video üretimi, mikro-influencer iş birlikleri ve performans reklamları.\n\n## Sonuç\n- Takipçi 12B → 189B\n- ROAS 4,6x\n- Etkileşim oranı %7,8 (sektör ort. %1,9)",
            'tags'     => ['Meta Ads', 'İçerik Üretimi', 'Influencer', 'Analitik'],
            'metrics'  => [['label' => 'Takipçi', 'value' => '189B'], ['label' => 'ROAS', 'value' => '4.6x'], ['label' => 'Etkileşim', 'value' => '%7.8']],
            'url'      => '',
            'year'     => '2025',
            'featured' => false,
        ],
        [
            'title'    => 'Atlas — Emlak Portalı',
            'client'   => 'Atlas Gayrimenkul',
            'category' => 'Web Uygulaması',
            'excerpt'  => 'Harita tabanlı arama ve 3D tur entegrasyonu; ilan başına görüntülenme %88 arttı.',
            'body'     => "Klasik ilan listeleme yerine harita odaklı bir keşif deneyimi tasarladık.\n\n## Öne çıkanlar\n- Harita üzerinde kümeleme ve anlık filtreleme\n- 360° sanal tur entegrasyonu\n- Kaydedilen arama + e-posta bildirimi\n- Danışman performans panosu",
            'tags'     => ['Next.js', 'Mapbox', 'Node.js', 'MongoDB'],
            'metrics'  => [['label' => 'Görüntülenme', 'value' => '+%88'], ['label' => 'İlan', 'value' => '14B'], ['label' => 'Yanıt süresi', 'value' => '120ms']],
            'url'      => '',
            'year'     => '2024',
            'featured' => false,
        ],
        [
            'title'    => 'Sena — Restoran Zinciri SEO',
            'client'   => 'Sena Restoran',
            'category' => 'SEO',
            'excerpt'  => '18 şube için yerel SEO; "yakınımdaki" aramalarında 1. sıra, rezervasyonda %147 artış.',
            'body'     => "18 şubeli bir restoran zinciri için yerel SEO çalışması.\n\n## Yapılanlar\nGoogle İşletme Profili optimizasyonu, şube bazlı açılış sayfaları, yapısal veri işaretlemesi, yorum yönetimi ve yerel bağlantı inşası.\n\n## Sonuç\n- Yerel aramalarda ortalama sıra 8,4 → 1,6\n- Online rezervasyon +%147\n- Yol tarifi isteği +%92",
            'tags'     => ['Yerel SEO', 'Schema.org', 'GBP', 'İçerik'],
            'metrics'  => [['label' => 'Rezervasyon', 'value' => '+%147'], ['label' => 'Ortalama sıra', 'value' => '1.6'], ['label' => 'Şube', 'value' => '18']],
            'url'      => '',
            'year'     => '2025',
            'featured' => false,
        ],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, [
            'id'         => Store::uid(),
            'slug'       => slugify($r['title']),
            'order'      => $i + 1,
            'active'     => true,
            'image'      => '',
            'gallery'    => [],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
    return $out;
}

function seed_testimonials(): array
{
    $rows = [
        ['name' => 'Elif Karaca', 'role' => 'Kurucu Ortak', 'company' => 'Nexora Teknoloji', 'rating' => 5,
            'body' => 'Teknik derinlik kadar iş anlayışları da etkileyici. Karmaşık bir mimariyi sadeleştirip 4 ayda yayına aldılar. Süreç boyunca tek bir sürprizle karşılaşmadık.'],
        ['name' => 'Murat Şen', 'role' => 'Pazarlama Direktörü', 'company' => 'Marla Studio', 'rating' => 5,
            'body' => 'Ödeme akışını yeniden tasarladıktan sonra dönüşüm oranımız neredeyse ikiye katlandı. Sadece kod yazmıyorlar; satışa etkisini ölçüyorlar.'],
        ['name' => 'Dr. Ayşe Yıldız', 'role' => 'Genel Müdür', 'company' => 'Kardio Health', 'rating' => 5,
            'body' => 'Sağlık alanında çalışmak hassasiyet ister. KVKK uyumundan mağaza onay süreçlerine kadar her adımı profesyonelce yönettiler.'],
        ['name' => 'Kaan Demirtaş', 'role' => 'CTO', 'company' => 'Bloom CRM', 'rating' => 5,
            'body' => 'Yapay zekâ entegrasyonunda gördüğüm en temiz mimari. Halüsinasyon riskini kaynak doğrulamayla çözmeleri bizim için kritikti.'],
        ['name' => 'Seda Aksoy', 'role' => 'Kurumsal İletişim Müdürü', 'company' => 'Voltra Enerji', 'rating' => 5,
            'body' => '4 dilli sitemizi zamanında ve bütçesinde teslim ettiler. Organik trafiğimiz 6 ayda üç katına çıktı. Raporlamaları çok şeffaf.'],
        ['name' => 'Emre Baştürk', 'role' => 'Operasyon Müdürü', 'company' => 'Rota Lojistik', 'rating' => 5,
            'body' => 'Sahadaki gerçek problemi anlamak için bizimle birlikte araca bindiler. Bu yaklaşım, sonucu doğrudan etkiledi.'],
        ['name' => 'Zeynep Arslan', 'role' => 'Marka Yöneticisi', 'company' => 'Fusio Kozmetik', 'rating' => 5,
            'body' => 'Sosyal medyada sıfırdan başladık. 6 ay sonra sektörde konuşulan bir marka olduk. Ekip gerçekten veriye bakarak karar veriyor.'],
        ['name' => 'Onur Çelik', 'role' => 'Genel Koordinatör', 'company' => 'Atlas Gayrimenkul', 'rating' => 4,
            'body' => 'Harita tabanlı arama fikri bizden değil onlardan geldi ve bugün en çok kullanılan özelliğimiz. İyi ki dinlemişiz.'],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, [
            'id'     => Store::uid(),
            'avatar' => '',
            'order'  => $i + 1,
            'active' => true,
            'date'   => date('Y-m-d', strtotime('-' . ($i * 37 + 12) . ' days')),
        ]);
    }
    return $out;
}

function seed_skills(): array
{
    $rows = [
        ['name' => 'PHP / Laravel',       'level' => 96, 'group' => 'Backend'],
        ['name' => 'JavaScript / TypeScript', 'level' => 95, 'group' => 'Frontend'],
        ['name' => 'React & Next.js',     'level' => 93, 'group' => 'Frontend'],
        ['name' => 'Vue & Nuxt',          'level' => 88, 'group' => 'Frontend'],
        ['name' => 'Node.js',             'level' => 91, 'group' => 'Backend'],
        ['name' => 'Python',              'level' => 86, 'group' => 'Backend'],
        ['name' => 'React Native',        'level' => 90, 'group' => 'Mobil'],
        ['name' => 'Flutter',             'level' => 82, 'group' => 'Mobil'],
        ['name' => 'PostgreSQL / MySQL',  'level' => 93, 'group' => 'Veri'],
        ['name' => 'Redis / Elasticsearch', 'level' => 85, 'group' => 'Veri'],
        ['name' => 'Docker & CI/CD',      'level' => 89, 'group' => 'DevOps'],
        ['name' => 'AWS / Google Cloud',  'level' => 84, 'group' => 'DevOps'],
        ['name' => 'Teknik SEO',          'level' => 94, 'group' => 'Büyüme'],
        ['name' => 'Figma / UI Sistemleri', 'level' => 88, 'group' => 'Tasarım'],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, ['id' => Store::uid(), 'order' => $i + 1, 'active' => true]);
    }
    return $out;
}

function seed_faq(): array
{
    $rows = [
        ['q' => 'Bir proje ne kadar sürede tamamlanır?',
         'a' => 'Kurumsal web siteleri ortalama 2–4 hafta, e-ticaret projeleri 4–8 hafta, mobil uygulamalar 6–12 hafta, özel yazılım projeleri ise kapsamına göre 8–20 hafta sürer. Keşif görüşmesinin ardından size gün bazında net bir takvim sunuyoruz.'],
        ['q' => 'Fiyatlandırma nasıl belirleniyor?',
         'a' => 'Saatlik ücret yerine sabit proje fiyatı ile çalışıyoruz. Kapsam netleştikten sonra verdiğimiz teklif bağlayıcıdır; kapsam değişmediği sürece ek ücret talep etmeyiz. Ödemeyi genellikle 3 taksite bölüyoruz: başlangıç, ara teslim ve yayın.'],
        ['q' => 'Kaynak kodun sahibi kim olacak?',
         'a' => 'Proje bedeli tamamlandığında kaynak kodun tüm fikri mülkiyet hakları size geçer. Kodu kendi deponuza teslim eder, teknik dokümantasyonu da birlikte veririz. Hiçbir kilitleme (vendor lock-in) uygulamıyoruz.'],
        ['q' => 'Yayına aldıktan sonra destek veriyor musunuz?',
         'a' => 'Evet. Tüm projelerde yayın sonrası 3 ay ücretsiz hata giderme desteği vardır. Sonrasında dilerseniz aylık bakım paketimize geçebilirsiniz; kritik arızalarda 2 saat içinde müdahale ediyoruz.'],
        ['q' => 'Mevcut sitemi/uygulamamı devralabilir misiniz?',
         'a' => 'Evet. Önce ücretsiz bir teknik denetim yapıyor, kod kalitesi ve riskler hakkında rapor sunuyoruz. Ardından devralma, iyileştirme veya yeniden yazma seçeneklerinden hangisinin daha ekonomik olduğunu birlikte kararlaştırıyoruz.'],
        ['q' => 'SEO çalışmasının sonuçlarını ne zaman görürüm?',
         'a' => 'Teknik SEO iyileştirmelerinin etkisi genellikle 3–6 hafta içinde görülmeye başlar. İçerik ve otorite çalışmalarının kalıcı etkisi ise 3–6 ay içinde belirginleşir. Her ay ölçülebilir rapor paylaşırız.'],
        ['q' => 'Uzaktan mı çalışıyorsunuz, yüz yüze görüşebilir miyiz?',
         'a' => 'Her iki şekilde de çalışıyoruz. Türkiye genelindeki müşterilerimizle çevrim içi görüşüyor, İstanbul içinde talep edilirse yüz yüze toplantı yapıyoruz. Süreç boyunca haftalık ilerleme toplantıları standarttır.'],
        ['q' => 'Verilerimin güvenliği nasıl sağlanıyor?',
         'a' => 'Tüm projelerde gizlilik sözleşmesi (NDA) imzalıyoruz. Sunucularda şifreli yedekleme, en az yetki prensibi, KVKK uyumlu veri işleme ve düzenli güvenlik taraması uyguluyoruz.'],
        ['q' => 'Sadece tasarım veya sadece kodlama hizmeti alabilir miyim?',
         'a' => 'Evet. Tasarımınız hazırsa yalnızca geliştirme, kod tarafınız hazırsa yalnızca tasarım veya SEO hizmeti verebiliriz. Hizmetlerimiz modülerdir.'],
        ['q' => 'Proje sürecinde nasıl iletişim kuracağız?',
         'a' => 'Her proje için özel bir iletişim kanalı (Slack/WhatsApp grubu) açıyoruz. Ayrıca canlı bir proje panosu üzerinden görevlerin durumunu anlık takip edebilirsiniz.'],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, ['id' => Store::uid(), 'order' => $i + 1, 'active' => true]);
    }
    return $out;
}

function seed_posts(): array
{
    $rows = [
        [
            'title'    => 'Core Web Vitals 2026: Sıralamanızı Etkileyen 5 Metrik',
            'category' => 'SEO',
            'excerpt'  => 'Google\'ın kullanıcı deneyimi metrikleri değişti. INP, LCP ve CLS için pratik iyileştirme reçeteleri.',
            'body'     => "Google, sayfa deneyimini ölçen metriklerini güncelledi. Artık FID yerine INP (Interaction to Next Paint) kullanılıyor.\n\n## LCP — En Büyük İçerikli Boyama\nHedef: 2,5 saniyenin altı. En sık karşılaştığımız sorun, hero görselinin optimize edilmemiş olması.\n\n- Hero görselini `fetchpriority=\"high\"` ile önceliklendirin\n- WebP/AVIF formatına geçin\n- Kritik CSS'i satır içine alın\n\n## INP — Etkileşimden Sonraki Boyama\nHedef: 200 ms altı. Uzun süren JavaScript görevleri en büyük düşman.\n\n- Uzun görevleri parçalayın\n- Üçüncü parti scriptleri geciktirin\n- Ağır hesaplamaları Web Worker'a taşıyın\n\n## CLS — Kümülatif Düzen Kayması\nHedef: 0,1 altı. Görsellere ve reklam alanlarına mutlaka boyut verin.\n\n## Ölçüm\nLaboratuvar verisi (Lighthouse) ile saha verisi (CrUX) farklıdır. Kararlarınızı saha verisine göre alın.",
            'tags'     => ['SEO', 'Performans', 'Core Web Vitals'],
        ],
        [
            'title'    => 'Mobil Uygulama mı, PWA mı? Karar Ağacı',
            'category' => 'Mobil',
            'excerpt'  => 'Bütçenizi doğru yere harcayın: hangi durumda native, hangi durumda progressive web app mantıklı?',
            'body'     => "Her fikir mağazada uygulama olmayı hak etmiyor. Yanlış karar 6 aylık bütçe kaybı demek.\n\n## PWA yeterlidir, eğer:\n- Kullanıcı uygulamayı haftada birkaç kez açacaksa\n- Donanım erişimi (Bluetooth, arka plan konum) gerekmiyorsa\n- Hızlı doğrulama yapmak istiyorsanız\n\n## Native/React Native gereklidir, eğer:\n- Push bildirim dönüşüm stratejinizin merkezindeyse\n- Kamera, biyometri, arka plan senkronu kritikse\n- Mağaza görünürlüğü bir edinim kanalıysa\n\n## Melez yaklaşım\nÇoğu projede en verimli yol: önce PWA ile doğrula, kullanıcı sayısı belirli eşiği geçince React Native'e taşı.",
            'tags'     => ['Mobil', 'PWA', 'Strateji'],
        ],
        [
            'title'    => 'E-Ticarette Dönüşümü Artıran 12 Mikro Detay',
            'category' => 'E-Ticaret',
            'excerpt'  => 'Büyük yeniden tasarımlara gerek yok. Bu küçük değişiklikler ciroyu ölçülebilir şekilde artırıyor.',
            'body'     => "Dönüşüm optimizasyonu genellikle büyük değişikliklerle değil, küçük sürtünmeleri kaldırmakla olur.\n\n## Ürün sayfası\n1. Kargo süresini fiyatın hemen altında gösterin\n2. Stok adedini 5'in altındayken belirtin\n3. Beden tablosunu modal yerine satır içi açın\n4. İade koşullarını tek cümleyle özetleyin\n\n## Sepet\n5. Ücretsiz kargo eşiğine kalan tutarı gösterin\n6. Sepeti oturum kapansa da saklayın\n7. Kupon alanını varsayılan olarak kapalı tutun\n\n## Ödeme\n8. Misafir alışverişi zorunlu olsun\n9. Kart numarası alanında otomatik biçimlendirme yapın\n10. Adres için otomatik tamamlama kullanın\n11. Güvenlik rozetlerini ödeme butonunun yanına koyun\n12. Hata mesajlarını alanın hemen altında gösterin\n\nBu 12 maddeyi uyguladığımız bir projede dönüşüm oranı %64 arttı.",
            'tags'     => ['E-Ticaret', 'CRO', 'UX'],
        ],
        [
            'title'    => 'Yapay Zekâyı Ürününüze Güvenli Entegre Etmenin 7 Kuralı',
            'category' => 'Yapay Zekâ',
            'excerpt'  => 'LLM eklemek kolay; güvenli, maliyeti kontrollü ve doğru bilgi üreten bir sistem kurmak farklı bir iş.',
            'body'     => "LLM entegrasyonlarında en sık gördüğümüz 7 hata ve çözümleri.\n\n## 1. Kaynak doğrulaması olmadan yanıt üretmek\nRAG mimarisi kullanın; model yalnızca sizin dokümanlarınızdan cevap versin ve kaynağı göstersin.\n\n## 2. Maliyet limiti koymamak\nKullanıcı başına ve toplam günlük token kotası tanımlayın.\n\n## 3. Kişisel veriyi doğrudan göndermek\nİstem öncesi maskeleme katmanı ekleyin.\n\n## 4. Emin olmadığında devretmemek\nGüven skoru eşiğin altındaysa insana yönlendirin.\n\n## 5. Yanıtları loglamamak\nDenetim kaydı hem hata ayıklama hem uyum için gerekli.\n\n## 6. İstem enjeksiyonunu göz ardı etmek\nKullanıcı girdisini sistem talimatından katı biçimde ayırın.\n\n## 7. Ölçmemek\nÇözüm oranı, devretme oranı ve memnuniyet skorunu takip edin.",
            'tags'     => ['Yapay Zekâ', 'LLM', 'Güvenlik'],
        ],
        [
            'title'    => 'Kurumsal Web Sitesi Brief\'i Nasıl Hazırlanır?',
            'category' => 'Süreç',
            'excerpt'  => 'İyi bir brief, projenin süresini %30 kısaltır. İşte doldurmanız gereken başlıklar.',
            'body'     => "Ajansa gitmeden önce hazırlayacağınız brief, hem teklifi netleştirir hem süreci hızlandırır.\n\n## Olması gerekenler\n- İş hedefi: Siteden ne bekliyorsunuz? (talep formu, satış, marka bilinirliği)\n- Hedef kitle: Kim, hangi cihazdan, hangi niyetle geliyor?\n- Başarı metriği: Neyi ölçeceğiz?\n- Beğendiğiniz 3 site ve nedenleri\n- Beğenmediğiniz 2 site ve nedenleri\n- Hazır içerik durumu: metin, görsel, video var mı?\n- Bütçe aralığı ve son tarih\n\n## Sık yapılan hata\n\"Rakibimin sitesi gibi olsun\" demek. Rakibinizin sitesi onun iş modeli için tasarlandı; sizinki için değil.",
            'tags'     => ['Süreç', 'Brief', 'Danışmanlık'],
        ],
        [
            'title'    => 'Sosyal Medyada Organik Erişim Öldü mü?',
            'category' => 'Sosyal Medya',
            'excerpt'  => 'Algoritma değişiklikleri sonrası organik büyüme hâlâ mümkün — ama kuralları değişti.',
            'body'     => "Organik erişim ölmedi; format ve tutarlılık şartı geldi.\n\n## Bugün işe yarayanlar\n- İlk 3 saniyede net bir vaat sunan kısa video\n- Kaydedilebilir (save-worthy) bilgi içerikleri\n- Yorumlara ilk 60 dakika içinde yanıt\n- Tek konuda derinleşen içerik serileri\n\n## İşe yaramayanlar\n- Aynı içeriği tüm platformlara aynı formatta atmak\n- Sadece ürün tanıtımı yapmak\n- Haftada 1 paylaşımla düzen beklemek\n\n## Doğru ölçüm\nTakipçi sayısı gösterge değildir. Kaydetme, paylaşma ve profil ziyaretinden gelen tıklamayı ölçün.",
            'tags'     => ['Sosyal Medya', 'İçerik', 'Büyüme'],
        ],
    ];

    $out = [];
    foreach ($rows as $i => $r) {
        $out[] = array_merge($r, [
            'id'         => Store::uid(),
            'slug'       => slugify($r['title']),
            'image'      => '',
            'author'     => 'Oxit Studio',
            'active'     => true,
            'featured'   => $i < 2,
            'views'      => 0,
            'order'      => $i + 1,
            'date'       => date('Y-m-d', strtotime('-' . ($i * 11 + 3) . ' days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
    return $out;
}

function seed_clients(): array
{
    $names = ['Nexora', 'Marla Studio', 'Kardio Health', 'Voltra Enerji', 'Bloom CRM',
        'Rota Lojistik', 'Fusio', 'Atlas GYO', 'Sena Group', 'Vertex Yazılım', 'Kuzey Bank', 'Delta Medya'];

    $out = [];
    foreach ($names as $i => $n) {
        $out[] = [
            'id'     => Store::uid(),
            'name'   => $n,
            'logo'   => '',
            'url'    => '',
            'order'  => $i + 1,
            'active' => true,
        ];
    }
    return $out;
}
