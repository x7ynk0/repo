<?php
/**
 * Yönetici paneli kaynak (koleksiyon) tanımları.
 *
 * Her kaynak; liste sütunlarını ve form alanlarını tanımlar.
 * CRUD motoru (crud.php) bu tanımlara bakarak listeyi, formu,
 * doğrulamayı ve kaydetmeyi otomatik üretir.
 *
 * Alan tipleri:
 *   text, textarea, richtext, number, price, select, icon, bool,
 *   image, gallery, list, pairs, date, color, range, hidden
 */

declare(strict_types=1);

function admin_resources(): array
{
    return [

        /* ---------------------------------------------------------- */
        'services' => [
            'label'        => 'Hizmetler',
            'label_single' => 'Hizmet',
            'icon'         => 'layers',
            'desc'         => 'Sunduğunuz hizmetler. Ana sayfada ve hizmetler sayfasında listelenir.',
            'slug_from'    => 'title',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'icon',       'label' => '',        'type' => 'icon'],
                ['key' => 'title',      'label' => 'Başlık',  'type' => 'title'],
                ['key' => 'price_from', 'label' => 'Fiyat',   'type' => 'price'],
                ['key' => 'duration',   'label' => 'Süre',    'type' => 'text'],
                ['key' => 'featured',   'label' => 'Öne Çıkan', 'type' => 'bool'],
            ],
            'fields' => [
                'title'      => ['label' => 'Hizmet adı', 'type' => 'text', 'required' => true, 'hint' => 'Örn: Kurumsal Web Sitesi Geliştirme'],
                'icon'       => ['label' => 'İkon', 'type' => 'icon', 'default' => 'code'],
                'excerpt'    => ['label' => 'Kısa açıklama', 'type' => 'textarea', 'rows' => 3, 'hint' => 'Kartlarda görünen 1-2 cümlelik özet.'],
                'body'       => ['label' => 'Detaylı içerik', 'type' => 'richtext', 'rows' => 14, 'hint' => 'Markdown benzeri: ## Başlık, **kalın**, *italik*, - liste'],
                'features'   => ['label' => 'Hizmet kapsamı', 'type' => 'list', 'hint' => 'Her satıra bir madde yazın.'],
                'price_from' => ['label' => 'Başlangıç fiyatı (₺)', 'type' => 'price', 'col' => 2],
                'duration'   => ['label' => 'Tahmini süre', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: 2 – 4 hafta'],
                'image'      => ['label' => 'Görsel', 'type' => 'image', 'folder' => 'misc'],
                'featured'   => ['label' => 'Ana sayfada öne çıkar', 'type' => 'bool', 'col' => 2, 'default' => true],
                'active'     => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'      => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'projects' => [
            'label'        => 'Referanslar',
            'label_single' => 'Referans / Proje',
            'icon'         => 'folder',
            'desc'         => 'Tamamladığınız projeler. Vaka çalışması olarak yayınlanır.',
            'slug_from'    => 'title',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'image',    'label' => '',          'type' => 'thumb'],
                ['key' => 'title',    'label' => 'Proje',     'type' => 'title'],
                ['key' => 'client',   'label' => 'Müşteri',   'type' => 'text'],
                ['key' => 'category', 'label' => 'Kategori',  'type' => 'badge'],
                ['key' => 'year',     'label' => 'Yıl',       'type' => 'text'],
                ['key' => 'featured', 'label' => 'Öne Çıkan', 'type' => 'bool'],
            ],
            'fields' => [
                'title'    => ['label' => 'Proje adı', 'type' => 'text', 'required' => true],
                'client'   => ['label' => 'Müşteri / Marka', 'type' => 'text', 'col' => 2],
                'category' => ['label' => 'Kategori', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: E-Ticaret, Mobil Uygulama, SEO'],
                'year'     => ['label' => 'Yıl', 'type' => 'text', 'col' => 2],
                'url'      => ['label' => 'Canlı site adresi', 'type' => 'text', 'col' => 2, 'hint' => 'https:// ile başlamalı'],
                'excerpt'  => ['label' => 'Kısa açıklama', 'type' => 'textarea', 'rows' => 3],
                'body'     => ['label' => 'Vaka çalışması', 'type' => 'richtext', 'rows' => 16, 'hint' => 'Problem → Çözüm → Sonuç yapısı en etkili sonucu verir.'],
                'metrics'  => ['label' => 'Sonuç metrikleri', 'type' => 'pairs', 'pair_keys' => ['label' => 'Etiket', 'value' => 'Değer'], 'hint' => 'Örn: "Dönüşüm artışı" → "%64"'],
                'tags'     => ['label' => 'Kullanılan teknolojiler', 'type' => 'list', 'hint' => 'Her satıra bir teknoloji.'],
                'image'    => ['label' => 'Kapak görseli', 'type' => 'image', 'folder' => 'projects'],
                'gallery'  => ['label' => 'Galeri görselleri', 'type' => 'gallery', 'folder' => 'projects'],
                'featured' => ['label' => 'Ana sayfada öne çıkar', 'type' => 'bool', 'col' => 2, 'default' => true],
                'active'   => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'    => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'testimonials' => [
            'label'        => 'Müşteri Görüşleri',
            'label_single' => 'Görüş',
            'icon'         => 'quote',
            'desc'         => 'Müşterilerinizin yorumları. Ana sayfadaki slider bölümünde görünür.',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'avatar',  'label' => '',        'type' => 'avatar'],
                ['key' => 'name',    'label' => 'Ad',      'type' => 'title'],
                ['key' => 'company', 'label' => 'Şirket',  'type' => 'text'],
                ['key' => 'rating',  'label' => 'Puan',    'type' => 'stars'],
            ],
            'fields' => [
                'name'    => ['label' => 'Ad Soyad', 'type' => 'text', 'required' => true, 'col' => 2],
                'role'    => ['label' => 'Ünvan', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: Pazarlama Direktörü'],
                'company' => ['label' => 'Şirket', 'type' => 'text', 'col' => 2],
                'rating'  => ['label' => 'Puan (1-5)', 'type' => 'number', 'col' => 2, 'min' => 1, 'max' => 5, 'default' => 5],
                'body'    => ['label' => 'Yorum', 'type' => 'textarea', 'rows' => 5, 'required' => true],
                'avatar'  => ['label' => 'Fotoğraf', 'type' => 'image', 'folder' => 'misc', 'hint' => 'Boş bırakılırsa baş harfler gösterilir.'],
                'date'    => ['label' => 'Tarih', 'type' => 'date', 'col' => 2],
                'active'  => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'   => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'posts' => [
            'label'        => 'Blog Yazıları',
            'label_single' => 'Yazı',
            'icon'         => 'pen',
            'desc'         => 'Blog içerikleri. SEO için düzenli yazı yayınlamak organik trafiği artırır.',
            'slug_from'    => 'title',
            'sort'         => 'date',
            'dir'          => 'desc',
            'columns'      => [
                ['key' => 'image',    'label' => '',          'type' => 'thumb'],
                ['key' => 'title',    'label' => 'Başlık',    'type' => 'title'],
                ['key' => 'category', 'label' => 'Kategori',  'type' => 'badge'],
                ['key' => 'date',     'label' => 'Tarih',     'type' => 'date'],
                ['key' => 'views',    'label' => 'Okunma',    'type' => 'number'],
            ],
            'fields' => [
                'title'    => ['label' => 'Başlık', 'type' => 'text', 'required' => true],
                'category' => ['label' => 'Kategori', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: SEO, Mobil, E-Ticaret'],
                'author'   => ['label' => 'Yazar', 'type' => 'text', 'col' => 2, 'default' => 'Oxit Studio'],
                'excerpt'  => ['label' => 'Özet', 'type' => 'textarea', 'rows' => 3, 'hint' => 'Arama sonuçlarında ve kartlarda görünür. 140-160 karakter idealdir.'],
                'body'     => ['label' => 'İçerik', 'type' => 'richtext', 'rows' => 20],
                'tags'     => ['label' => 'Etiketler', 'type' => 'list'],
                'image'    => ['label' => 'Kapak görseli', 'type' => 'image', 'folder' => 'blog'],
                'date'     => ['label' => 'Yayın tarihi', 'type' => 'date', 'col' => 2],
                'featured' => ['label' => 'Öne çıkar', 'type' => 'bool', 'col' => 2],
                'active'   => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'    => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'packages' => [
            'label'        => 'Fiyat Paketleri',
            'label_single' => 'Paket',
            'icon'         => 'gift',
            'desc'         => 'Ana sayfadaki fiyatlandırma bölümü.',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'title',    'label' => 'Paket',     'type' => 'title'],
                ['key' => 'price',    'label' => 'Fiyat',     'type' => 'price'],
                ['key' => 'badge',    'label' => 'Rozet',     'type' => 'badge'],
                ['key' => 'featured', 'label' => 'Vurgulu',   'type' => 'bool'],
            ],
            'fields' => [
                'title'    => ['label' => 'Paket adı', 'type' => 'text', 'required' => true, 'col' => 2],
                'subtitle' => ['label' => 'Alt başlık', 'type' => 'text', 'col' => 2],
                'price'    => ['label' => 'Fiyat (₺)', 'type' => 'price', 'col' => 2, 'hint' => '0 yazarsanız "Özel" olarak gösterilir.'],
                'period'   => ['label' => 'Periyot', 'type' => 'text', 'col' => 2, 'default' => 'proje başı'],
                'badge'    => ['label' => 'Rozet metni', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: En Çok Tercih Edilen'],
                'cta'      => ['label' => 'Buton metni', 'type' => 'text', 'col' => 2, 'default' => 'Teklif Al'],
                'features' => ['label' => 'Dahil olanlar', 'type' => 'list'],
                'excluded' => ['label' => 'Dahil olmayanlar', 'type' => 'list'],
                'featured' => ['label' => 'Vurgulu paket', 'type' => 'bool', 'col' => 2],
                'active'   => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'    => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'process' => [
            'label'        => 'Çalışma Süreci',
            'label_single' => 'Süreç Adımı',
            'icon'         => 'refresh',
            'desc'         => 'Projelerinizi nasıl yürüttüğünüzü anlatan adımlar.',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'icon',     'label' => '',       'type' => 'icon'],
                ['key' => 'title',    'label' => 'Adım',   'type' => 'title'],
                ['key' => 'duration', 'label' => 'Süre',   'type' => 'text'],
                ['key' => 'step',     'label' => 'No',     'type' => 'number'],
            ],
            'fields' => [
                'title'    => ['label' => 'Adım başlığı', 'type' => 'text', 'required' => true, 'col' => 2],
                'icon'     => ['label' => 'İkon', 'type' => 'icon', 'col' => 2, 'default' => 'zap'],
                'duration' => ['label' => 'Zaman aralığı', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: 1 – 2. Hafta'],
                'step'     => ['label' => 'Adım numarası', 'type' => 'number', 'col' => 2, 'default' => 1],
                'body'     => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 4],
                'active'   => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'    => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'skills' => [
            'label'        => 'Yetenekler',
            'label_single' => 'Yetenek',
            'icon'         => 'cpu',
            'desc'         => 'Teknoloji ve uzmanlık seviyeleri (yüzde çubukları).',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'name',  'label' => 'Yetenek', 'type' => 'title'],
                ['key' => 'group', 'label' => 'Grup',    'type' => 'badge'],
                ['key' => 'level', 'label' => 'Seviye',  'type' => 'progress'],
            ],
            'fields' => [
                'name'   => ['label' => 'Yetenek adı', 'type' => 'text', 'required' => true, 'col' => 2],
                'group'  => ['label' => 'Grup', 'type' => 'text', 'col' => 2, 'hint' => 'Örn: Frontend, Backend, Mobil'],
                'level'  => ['label' => 'Seviye (%)', 'type' => 'range', 'min' => 0, 'max' => 100, 'default' => 80],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'  => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'faq' => [
            'label'        => 'Sıkça Sorulan Sorular',
            'label_single' => 'Soru',
            'icon'         => 'help',
            'desc'         => 'SSS sayfası ve ana sayfadaki akordiyon bölümü.',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'q',     'label' => 'Soru',  'type' => 'title'],
                ['key' => 'order', 'label' => 'Sıra',  'type' => 'number'],
            ],
            'fields' => [
                'q'      => ['label' => 'Soru', 'type' => 'text', 'required' => true],
                'a'      => ['label' => 'Cevap', 'type' => 'textarea', 'rows' => 6, 'required' => true],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'  => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],

        /* ---------------------------------------------------------- */
        'clients' => [
            'label'        => 'Müşteri Logoları',
            'label_single' => 'Müşteri',
            'icon'         => 'users',
            'desc'         => 'Ana sayfadaki kayan logo şeridi.',
            'sort'         => 'order',
            'columns'      => [
                ['key' => 'logo',  'label' => '',        'type' => 'thumb'],
                ['key' => 'name',  'label' => 'Müşteri', 'type' => 'title'],
                ['key' => 'order', 'label' => 'Sıra',    'type' => 'number'],
            ],
            'fields' => [
                'name'   => ['label' => 'Müşteri adı', 'type' => 'text', 'required' => true, 'col' => 2],
                'url'    => ['label' => 'Web sitesi', 'type' => 'text', 'col' => 2],
                'logo'   => ['label' => 'Logo', 'type' => 'image', 'folder' => 'misc', 'hint' => 'Boş bırakılırsa metin olarak gösterilir. SVG veya PNG önerilir.'],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'col' => 2, 'default' => true],
                'order'  => ['label' => 'Sıra', 'type' => 'number', 'col' => 2, 'default' => 99],
            ],
        ],
    ];
}

/** Tek bir kaynağın tanımını döndürür. */
function admin_resource(string $key): ?array
{
    $all = admin_resources();
    return $all[$key] ?? null;
}
