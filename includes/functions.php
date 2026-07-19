<?php
declare(strict_types=1);

/* =========================================================
 *  JSON dosya depolama katmanı
 * ========================================================= */

function data_file(string $name): string
{
    return DATA_PATH . '/' . $name . '.json';
}

function json_load(string $name): array
{
    $file = data_file($name);
    if (!is_file($file)) {
        return [];
    }
    $fp = fopen($file, 'rb');
    if ($fp === false) {
        return [];
    }
    flock($fp, LOCK_SH);
    $raw = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $decoded = json_decode((string)$raw, true);
    return is_array($decoded) ? $decoded : [];
}

function json_save(string $name, array $data): bool
{
    $file = data_file($name);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($file, $json, LOCK_EX) !== false;
}

function generate_id(): string
{
    return bin2hex(random_bytes(8));
}

/* =========================================================
 *  İlk kurulum: veri klasörü ve varsayılan dosyalar
 * ========================================================= */

function ensure_storage(): void
{
    if (!is_dir(DATA_PATH)) {
        mkdir(DATA_PATH, 0775, true);
    }
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0775, true);
    }

    $htaccess = DATA_PATH . '/.htaccess';
    if (!is_file($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n");
    }
    $uploadsHt = UPLOAD_PATH . '/.htaccess';
    if (!is_file($uploadsHt)) {
        file_put_contents($uploadsHt, "php_flag engine off\n<FilesMatch \"\\.(php|phtml|php5|phar|pl|py|cgi|sh)$\">\n  Require all denied\n</FilesMatch>\n");
    }

    if (!is_file(data_file('users'))) {
        json_save('users', []);
    }
    if (!is_file(data_file('products'))) {
        json_save('products', []);
    }
    if (!is_file(data_file('orders'))) {
        json_save('orders', []);
    }
    if (!is_file(data_file('categories'))) {
        json_save('categories', default_categories());
    }
    if (!is_file(data_file('brands'))) {
        json_save('brands', default_brands());
    }
    if (!is_file(data_file('settings'))) {
        json_save('settings', default_settings());
    }
    if (!is_file(data_file('faq'))) {
        json_save('faq', default_faq());
    }
}

function default_settings(): array
{
    return [
        'site_title'  => 'Araç Yedek Parça',
        'slogan'      => 'Tüm marka ve modeller için orijinal ve muadil yedek parça',
        'phone'       => '',
        'whatsapp'    => '',
        'email'       => '',
        'address'     => '',
        'about'       => default_about_text(),
        'footer_text' => '',
        'currency'    => '₺',
        'logo'        => '',
        'bank_accounts' => [],
    ];
}

function default_about_text(): string
{
    return "Yolculuğumuz, 1998 yılında İstanbul'un arka sokaklarındaki 20 metrekarelik küçük bir dükkânda, tek bir tezgâh ve büyük bir tutkuyla başladı. O günlerde amacımız basitti: aracı arızalanan bir ustanın, bir esnafın ya da yolda kalan bir ailenin aradığı parçayı, doğru fiyata ve en hızlı şekilde bulmak. Aradan geçen çeyrek asırda bu ilke hiç değişmedi; değişen tek şey ölçeğimiz oldu.\n\n"
        . "Bugün binlerce kalem orijinal ve eş değer yedek parçayı aynı çatı altında buluşturuyor; binek araçlardan hafif ticarilere kadar onlarca markanın yüzlerce modeline hitap eden geniş bir ürün yelpazesi sunuyoruz. Motor aksamından fren sistemlerine, süspansiyondan elektrik ve aydınlatmaya kadar her kategoride, tedarik zincirimizi titizlikle seçtiğimiz üretici ve distribütörlerle kuruyoruz. Raflarımıza giren her parça; kalite denetiminden geçmeden, kutusuna güvenlik etiketi vurulmadan satışa çıkmaz.\n\n"
        . "Bizi farklı kılan şey yalnızca ürün çeşitliliğimiz değil, parçayı 'bilerek' satmamızdır. Ekibimiz; yılların ustalık tecrübesine sahip, şasi numarasından parça doğrulaması yapabilen, aracınıza uymayan ürünü size hiç göndermeyen uzmanlardan oluşur. Sipariş öncesi uyumluluk kontrolü, sipariş sonrası ise hızlı kargo ve düzenli bilgilendirme standart hizmetimizdir.\n\n"
        . "Müşterilerimizin bir kısmı bize ilk günden beri gelen sanayi esnafı, bir kısmı ise aracının bakımını kendi yapmayı seven tutkulu sürücüler. Kim olursanız olun ilkemiz aynı: doğru parça, dürüst fiyat, zamanında teslimat. Çünkü biliyoruz ki bir yedek parça sadece bir metal ya da plastik değildir; sizi yola çıkaran güvenin ta kendisidir.\n\n"
        . "Yarına bakarken hedefimiz; dijital altyapımızı sürekli geliştirerek Türkiye'nin her köşesine aynı gün kargo hizmetini ulaştırmak ve 'aradığınız parça bizde yoksa, sizin için buluruz' sözümüzü her geçen gün daha fazla müşterimize verebilmek. Bize güvenen herkese teşekkür ederiz — iyi yolculuklar dileriz.";
}

function default_faq(): array
{
    $items = [
        ['Siparişimi nasıl oluşturabilirim?', "Beğendiğiniz ürünü sepetinize ekleyin, ardından sepet sayfasından \"Ödeme Adımına Geç\" düğmesiyle teslimat bilgilerinizi girin. Ödeme yönteminizi seçip siparişi tamamladığınızda size özel bir sipariş numarası oluşturulur."],
        ['Hangi ödeme yöntemlerini kullanabilirim?', "Şu an için Havale/EFT ile ödeme kabul ediyoruz. Siparişinizi tamamladıktan sonra banka hesap bilgilerimiz ve sipariş numaranız ekranda görüntülenir. Kredi kartı ile ödeme seçeneği için çalışmalarımız devam etmektedir."],
        ['Havale açıklamasına ne yazmalıyım?', "Ödeme açıklamasına mutlaka sipariş numaranızı yazın. Bazı hesaplarımızda açıklama zorunludur; bu hesaplara açıklamasız veya hatalı açıklamayla gönderilen ödemeler iade edilir ve sipariş işleme alınmaz."],
        ['Ödememi yaptım, siparişim ne zaman onaylanır?', "Ödemeniz hesabımıza ulaşıp doğrulandığında siparişiniz onaylanır ve hazırlanmaya başlar. Dekontunuzu WhatsApp üzerinden iletirseniz doğrulama süreci hızlanır. Ödemesi doğrulanmayan siparişler onaylanmaz."],
        ['Siparişim ne zaman kargoya verilir?', "Ödemesi onaylanan siparişler stok durumuna göre en kısa sürede kargoya teslim edilir. Kargo firması ve gönderi ücreti, sipariş onayı sırasında size bildirilir. Siparişinizin durumunu onay sayfanızdaki bağlantıdan takip edebilirsiniz."],
        ['Parçanın aracıma uyup uymayacağından emin değilim, ne yapmalıyım?', "Sipariş vermeden önce telefon veya WhatsApp üzerinden bize ulaşın; aracınızın marka, model, yıl ve şasi numarası ile parça uyumluluğunu ücretsiz kontrol edelim. Sipariş notu alanına araç bilgilerinizi yazmanız da doğrulama yapmamızı sağlar."],
        ['Sattığınız parçalar orijinal mi?', "Ürünlerimiz orijinal (OEM) ve kalitesi belgeli eş değer (muadil) parçalardan oluşur. Her ürünün açıklamasında bu bilgi yer alır; emin olamadığınız durumlarda parça kodu ile bize danışabilirsiniz."],
        ['İade ve değişim koşullarınız nelerdir?', "Kutusu açılmamış ve montaj yapılmamış ürünlerde, teslimattan itibaren 14 gün içinde iade veya değişim talep edebilirsiniz. Elektronik parçalarda iade, parçanın takılmamış olması şartıyla kabul edilir. Süreç için sipariş numaranızla bize ulaşmanız yeterlidir."],
        ['Fiyatı "Fiyat Sorunuz" görünen ürünleri nasıl satın alabilirim?', "Bu ürünlerin fiyatı tedarik durumuna göre değişkenlik gösterdiği için güncel fiyatı telefon veya WhatsApp üzerinden öğrenebilirsiniz. Talebiniz üzerine ürün, fiyatı güncellenerek sipariş verebileceğiniz duruma getirilir."],
    ];
    $out = [];
    foreach ($items as [$q, $a]) {
        $out[] = ['id' => generate_id(), 'question' => $q, 'answer' => $a];
    }
    return $out;
}

function get_faq(): array
{
    return json_load('faq');
}

function default_categories(): array
{
    $names = [
        'Motor & Motor Parçaları', 'Fren Sistemi', 'Süspansiyon & Yürüyen Aksam',
        'Şanzıman & Vites', 'Debriyaj', 'Direksiyon Sistemi', 'Elektrik & Elektronik',
        'Aydınlatma & Farlar', 'Kaporta & Karoser', 'Cam & Ayna', 'Soğutma Sistemi',
        'Klima & Isıtma', 'Yakıt Sistemi', 'Egzoz Sistemi', 'Filtreler',
        'Yağlar & Sıvılar', 'Triger & Kayışlar', 'Rulman & Aks', 'İç Donanım & Aksesuar',
        'Lastik & Jant', 'Akü & Şarj Sistemi', 'Sensörler', 'Turbo & Emme Sistemi', 'Diğer',
    ];
    $out = [];
    foreach ($names as $n) {
        $out[] = ['id' => generate_id(), 'name' => $n];
    }
    return $out;
}

function default_brands(): array
{
    $list = [
        'Alfa Romeo' => ['Giulietta', 'Giulia', 'MiTo', 'Stelvio', '147', '156', '159'],
        'Audi'       => ['A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8', 'TT'],
        'BMW'        => ['1 Serisi', '2 Serisi', '3 Serisi', '4 Serisi', '5 Serisi', '6 Serisi', '7 Serisi', 'X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'i3', 'i4'],
        'BYD'        => ['Atto 3', 'Dolphin', 'Seal', 'Han', 'Tang'],
        'Chery'      => ['Tiggo 4 Pro', 'Tiggo 7 Pro', 'Tiggo 8 Pro', 'Omoda 5', 'Arrizo'],
        'Chevrolet'  => ['Aveo', 'Cruze', 'Captiva', 'Spark', 'Lacetti', 'Kalos'],
        'Citroën'    => ['C1', 'C2', 'C3', 'C3 Aircross', 'C4', 'C4 Cactus', 'C5', 'C5 Aircross', 'C-Elysée', 'Berlingo', 'Jumper', 'Jumpy', 'Nemo'],
        'Cupra'      => ['Formentor', 'Leon', 'Ateca', 'Born'],
        'Dacia'      => ['Sandero', 'Sandero Stepway', 'Duster', 'Logan', 'Lodgy', 'Dokker', 'Jogger', 'Spring'],
        'DS'         => ['DS 3', 'DS 4', 'DS 5', 'DS 7'],
        'Fiat'       => ['Egea', 'Egea Cross', 'Linea', 'Punto', 'Grande Punto', 'Panda', '500', '500L', '500X', 'Doblo', 'Fiorino', 'Ducato', 'Albea', 'Palio', 'Uno', 'Tempra', 'Tipo', 'Marea', 'Brava', 'Bravo', 'Stilo'],
        'Ford'       => ['Fiesta', 'Focus', 'Mondeo', 'Kuga', 'Puma', 'EcoSport', 'C-Max', 'S-Max', 'Galaxy', 'Ka', 'Mustang', 'Ranger', 'Transit', 'Transit Custom', 'Transit Courier', 'Transit Connect', 'Tourneo Courier', 'Tourneo Custom', 'Escort', 'Sierra', 'Taunus'],
        'Honda'      => ['Civic', 'Accord', 'City', 'Jazz', 'CR-V', 'HR-V', 'e:Ny1'],
        'Hyundai'    => ['i10', 'i20', 'i30', 'Elantra', 'Accent', 'Accent Era', 'Accent Blue', 'Getz', 'Bayon', 'Kona', 'Tucson', 'Santa Fe', 'ix35', 'H100', 'Starex', 'Staria'],
        'Isuzu'      => ['D-Max', 'NPR', 'NLR', 'NKR'],
        'Iveco'      => ['Daily', 'Eurocargo', 'Stralis'],
        'Jaguar'     => ['XE', 'XF', 'F-Pace', 'E-Pace'],
        'Jeep'       => ['Renegade', 'Compass', 'Cherokee', 'Grand Cherokee', 'Wrangler', 'Avenger'],
        'Kia'        => ['Picanto', 'Rio', 'Ceed', 'Cerato', 'Stonic', 'Sportage', 'Sorento', 'Niro', 'EV6', 'Venga', 'Soul'],
        'Lada'       => ['Vega', 'Samara', 'Niva', 'Kalina', 'Granta'],
        'Land Rover' => ['Defender', 'Discovery', 'Discovery Sport', 'Freelander', 'Range Rover', 'Range Rover Evoque', 'Range Rover Sport'],
        'Lexus'      => ['CT', 'ES', 'IS', 'NX', 'RX', 'UX'],
        'Mazda'      => ['2', '3', '6', 'CX-3', 'CX-30', 'CX-5', 'MX-5', '323', '626'],
        'Mercedes-Benz' => ['A Serisi', 'B Serisi', 'C Serisi', 'CLA', 'CLS', 'E Serisi', 'S Serisi', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'Vito', 'Sprinter', 'Citan', 'Atego', 'Axor', 'Actros'],
        'MG'         => ['ZS', 'HS', 'MG4', 'MG5', 'Marvel R'],
        'Mini'       => ['Cooper', 'Countryman', 'Clubman', 'One'],
        'Mitsubishi' => ['Lancer', 'Colt', 'ASX', 'Eclipse Cross', 'Outlander', 'L200', 'Space Star', 'Carisma', 'Canter'],
        'Nissan'     => ['Micra', 'Note', 'Juke', 'Qashqai', 'X-Trail', 'Navara', 'Primera', 'Almera', 'Sunny', 'Skystar', 'Pathfinder', 'Leaf'],
        'Opel'       => ['Corsa', 'Astra', 'Insignia', 'Vectra', 'Meriva', 'Zafira', 'Mokka', 'Crossland', 'Grandland', 'Combo', 'Vivaro', 'Movano', 'Kadett', 'Omega', 'Tigra', 'Frontera'],
        'Peugeot'    => ['106', '107', '108', '206', '207', '208', '2008', '301', '306', '307', '308', '3008', '406', '407', '408', '5008', '508', 'Partner', 'Rifter', 'Expert', 'Boxer', 'Bipper', 'RCZ'],
        'Porsche'    => ['911', 'Cayenne', 'Macan', 'Panamera', 'Taycan', 'Boxster', 'Cayman'],
        'Renault'    => ['Clio', 'Symbol', 'Megane', 'Megane E-Tech', 'Fluence', 'Laguna', 'Latitude', 'Talisman', 'Captur', 'Kadjar', 'Austral', 'Koleos', 'Kangoo', 'Express', 'Trafic', 'Master', 'Scenic', 'Espace', 'Twingo', 'Taliant', 'R9', 'R11', 'R12', 'R19', 'R21', 'Toros', 'Zoe'],
        'Rover'      => ['214', '216', '414', '416', '620', '75'],
        'Saab'       => ['9-3', '9-5', '900'],
        'Seat'       => ['Ibiza', 'Leon', 'Toledo', 'Cordoba', 'Altea', 'Arona', 'Ateca', 'Tarraco', 'Alhambra'],
        'Skoda'      => ['Fabia', 'Octavia', 'Superb', 'Rapid', 'Scala', 'Kamiq', 'Karoq', 'Kodiaq', 'Roomster', 'Yeti', 'Felicia', 'Favorit', 'Enyaq'],
        'SsangYong'  => ['Tivoli', 'Korando', 'Rexton', 'Musso', 'Actyon', 'Kyron'],
        'Subaru'     => ['Impreza', 'Forester', 'XV', 'Outback', 'Legacy', 'BRZ'],
        'Suzuki'     => ['Swift', 'Vitara', 'S-Cross', 'Jimny', 'SX4', 'Baleno', 'Alto', 'Grand Vitara'],
        'Tesla'      => ['Model 3', 'Model S', 'Model X', 'Model Y'],
        'Tofaş'      => ['Şahin', 'Doğan', 'Kartal', 'Serçe', 'Murat 124', 'Murat 131'],
        'Togg'       => ['T10X', 'T10F'],
        'Toyota'     => ['Corolla', 'Corolla Cross', 'Yaris', 'Yaris Cross', 'Auris', 'Avensis', 'C-HR', 'RAV4', 'Camry', 'Hilux', 'Land Cruiser', 'Proace', 'Proace City', 'Aygo', 'Verso', 'Carina', 'Starlet', 'Prius'],
        'Volkswagen' => ['Polo', 'Golf', 'Jetta', 'Bora', 'Passat', 'Passat Variant', 'Arteon', 'T-Cross', 'T-Roc', 'Taigo', 'Tiguan', 'Touareg', 'Touran', 'Sharan', 'Caddy', 'Transporter', 'Caravelle', 'Crafter', 'Amarok', 'Beetle', 'Scirocco', 'Vento', 'ID.3', 'ID.4'],
        'Volvo'      => ['S40', 'S60', 'S80', 'S90', 'V40', 'V60', 'V90', 'XC40', 'XC60', 'XC90', 'FH', 'FM'],
    ];

    $out = [];
    foreach ($list as $brand => $models) {
        $m = [];
        foreach ($models as $model) {
            $m[] = ['id' => generate_id(), 'name' => $model];
        }
        $out[] = ['id' => generate_id(), 'name' => $brand, 'models' => $m];
    }
    return $out;
}

/* =========================================================
 *  Ayarlar / Kategoriler / Markalar / Ürünler
 * ========================================================= */

function get_settings(): array
{
    return array_merge(default_settings(), json_load('settings'));
}

function get_categories(): array
{
    $cats = json_load('categories');
    usort($cats, fn($a, $b) => strcoll_tr($a['name'], $b['name']));
    return $cats;
}

function get_brands(): array
{
    $brands = json_load('brands');
    usort($brands, fn($a, $b) => strcoll_tr($a['name'], $b['name']));
    return $brands;
}

function get_products(): array
{
    return json_load('products');
}

function find_by_id(array $items, ?string $id): ?array
{
    if ($id === null || $id === '') {
        return null;
    }
    foreach ($items as $item) {
        if (($item['id'] ?? null) === $id) {
            return $item;
        }
    }
    return null;
}

function find_brand_model(array $brands, ?string $brandId, ?string $modelId): array
{
    $brand = find_by_id($brands, $brandId);
    $model = $brand ? find_by_id($brand['models'] ?? [], $modelId) : null;
    return [$brand, $model];
}

function strcoll_tr(string $a, string $b): int
{
    // Türkçe karakterleri de kabul edilebilir sıralayan basit karşılaştırma
    $map = ['ç' => 'cz', 'Ç' => 'cz', 'ğ' => 'gz', 'Ğ' => 'gz', 'ı' => 'iy', 'İ' => 'i',
            'ö' => 'oz', 'Ö' => 'oz', 'ş' => 'sz', 'Ş' => 'sz', 'ü' => 'uz', 'Ü' => 'uz'];
    $ka = mb_strtolower(strtr($a, $map));
    $kb = mb_strtolower(strtr($b, $map));
    return strcmp($ka, $kb);
}

/* =========================================================
 *  Sepet
 * ========================================================= */

function get_cart(): array
{
    $cart = $_SESSION['cart'] ?? [];
    return is_array($cart) ? $cart : [];
}

function save_cart(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function cart_count(): int
{
    return (int)array_sum(get_cart());
}

/**
 * Sepetteki ürünleri güncel ürün verisiyle birleştirir; satıştan
 * kalkan, pasifleşen veya stoğu tükenen ürünleri sepetten düşürür.
 */
function cart_items(): array
{
    $products = get_products();
    $cart     = get_cart();
    $items    = [];
    $changed  = false;

    foreach ($cart as $pid => $qty) {
        $p = find_by_id($products, (string)$pid);
        if (!$p || !($p['active'] ?? true) || (float)($p['price'] ?? 0) <= 0) {
            unset($cart[$pid]);
            $changed = true;
            continue;
        }
        $stock = $p['stock'] ?? '';
        if ($stock !== '' && (int)$stock <= 0) {
            unset($cart[$pid]);
            $changed = true;
            continue;
        }
        $qty = max(1, min(99, (int)$qty));
        if ($stock !== '') {
            $qty = min($qty, (int)$stock);
        }
        if ($qty !== (int)$cart[$pid]) {
            $changed = true;
        }
        $cart[$pid] = $qty;
        $items[] = [
            'product'    => $p,
            'qty'        => $qty,
            'line_total' => round((float)$p['price'] * $qty, 2),
        ];
    }
    if ($changed) {
        save_cart($cart);
    }
    return $items;
}

function cart_total(array $items): float
{
    return round((float)array_sum(array_column($items, 'line_total')), 2);
}

/** Ürün sepete eklenebilir mi? (yayında, fiyatlı ve stokta) */
function product_buyable(array $p): bool
{
    if (!($p['active'] ?? true) || (float)($p['price'] ?? 0) <= 0) {
        return false;
    }
    $stock = $p['stock'] ?? '';
    return $stock === '' || (int)$stock > 0;
}

/* =========================================================
 *  Siparişler
 * ========================================================= */

function get_orders(): array
{
    return json_load('orders');
}

function order_statuses(): array
{
    return [
        'odeme-bekliyor' => 'Ödeme Bekleniyor',
        'onaylandi'      => 'Ödeme Alındı / Onaylandı',
        'kargoda'        => 'Kargoya Verildi',
        'tamamlandi'     => 'Tamamlandı',
        'iptal'          => 'İptal Edildi',
    ];
}

function order_status_label(?string $status): string
{
    return order_statuses()[$status ?? ''] ?? 'Ödeme Bekleniyor';
}

/**
 * Aktif ödeme yöntemleri. Yeni bir yöntem (ör. kredi kartı)
 * eklemek için buraya yeni bir anahtar eklemek yeterlidir.
 */
function payment_methods(): array
{
    return [
        'havale' => [
            'label' => 'Havale / EFT',
            'note'  => 'Siparişi tamamladığınızda banka hesap bilgilerimiz görüntülenir. Siparişiniz, ödemeniz hesabımıza ulaşıp doğrulandıktan sonra onaylanır ve hazırlanmaya başlar.',
        ],
    ];
}

function generate_order_no(): string
{
    return 'SP' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function find_order_by_no(string $no): ?array
{
    foreach (get_orders() as $o) {
        if (($o['no'] ?? '') === $no) {
            return $o;
        }
    }
    return null;
}

/* =========================================================
 *  Kullanıcılar & Kimlik Doğrulama
 * ========================================================= */

function get_users(): array
{
    return json_load('users');
}

function setup_completed(): bool
{
    return count(get_users()) > 0;
}

function find_user_by_username(string $username): ?array
{
    foreach (get_users() as $u) {
        if (mb_strtolower($u['username']) === mb_strtolower($username)) {
            return $u;
        }
    }
    return null;
}

function current_user(): ?array
{
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) {
        return null;
    }
    $user = find_by_id(get_users(), (string)$id);
    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}

function is_admin(?array $user): bool
{
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    unset($_SESSION['login_attempts'], $_SESSION['login_blocked_until']);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function login_blocked(): int
{
    $until = (int)($_SESSION['login_blocked_until'] ?? 0);
    return $until > time() ? $until - time() : 0;
}

function register_login_failure(): void
{
    $attempts = (int)($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['login_attempts'] = $attempts;
    if ($attempts >= 5) {
        $_SESSION['login_blocked_until'] = time() + 300; // 5 dk bekleme
        $_SESSION['login_attempts'] = 0;
    }
}

/* =========================================================
 *  CSRF & Flash mesajları
 * ========================================================= */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}

function flash_set(string $type, string $message, ?string $link = null, ?string $linkText = null): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message, 'link' => $link, 'link_text' => $linkText];
}

function flash_get(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

/** Vitrin sayfalarında bilgi/hata mesajlarını üstte kayan bildirim olarak basar. */
function public_flashes(): void
{
    $flashes = flash_get();
    if (empty($flashes)) {
        return;
    }
    echo '<div class="toast-stack" role="status" aria-live="polite">';
    foreach ($flashes as $f) {
        $cls = ($f['type'] ?? '') === 'success' ? 'toast-success' : 'toast-error';
        echo '<div class="toast ' . $cls . '"><span>' . e($f['message'] ?? '') . '</span>';
        if (!empty($f['link'])) {
            echo '<a class="toast-link" href="' . e($f['link']) . '">' . e($f['link_text'] ?? 'Görüntüle') . '</a>';
        }
        echo '<button type="button" class="toast-close" aria-label="Kapat">&times;</button></div>';
    }
    echo '</div>';
}

/* =========================================================
 *  Yardımcılar
 * ========================================================= */

/**
 * Statik dosya adresine sürüm damgası ekler; tarayıcı önbelleği
 * dosya her değiştiğinde otomatik olarak yenilenir.
 */
function asset(string $path): string
{
    $file = BASE_PATH . '/' . ltrim($path, '/');
    $version = is_file($file) ? (string)filemtime($file) : '1';
    return $path . '?v=' . $version;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function format_price($price, string $currency = '₺'): string
{
    $price = (float)$price;
    if ($price <= 0) {
        return 'Fiyat Sorunuz';
    }
    return number_format($price, 2, ',', '.') . ' ' . $currency;
}

/**
 * T.C. Kimlik Numarası doğrulaması (resmi kontrol algoritması):
 * - 11 hane, ilk hane 0 olamaz
 * - 10. hane = ((1,3,5,7,9. haneler toplamı × 7) − (2,4,6,8. haneler toplamı)) mod 10
 * - 11. hane = ilk 10 hanenin toplamı mod 10
 */
function valid_tckn(string $tc): bool
{
    if (!preg_match('/^[1-9][0-9]{10}$/', $tc)) {
        return false;
    }
    $d = array_map('intval', str_split($tc));
    $odd  = $d[0] + $d[2] + $d[4] + $d[6] + $d[8];
    $even = $d[1] + $d[3] + $d[5] + $d[7];
    $d10  = (($odd * 7) - $even) % 10;
    if ($d10 < 0) {
        $d10 += 10;
    }
    if ($d10 !== $d[9]) {
        return false;
    }
    return (array_sum(array_slice($d, 0, 10)) % 10) === $d[10];
}

function mask_tckn(string $tc): string
{
    if (strlen($tc) !== 11) {
        return $tc;
    }
    return substr($tc, 0, 2) . '*******' . substr($tc, 9);
}

/**
 * Türkiye telefon numarası doğrulaması. +90 / 90 / 0 önekleri ayıklanır;
 * geriye kalan 10 hane GSM (5xx), sabit hat (2xx/3xx/4xx) veya kurumsal
 * (850) numara kalıbına uymalıdır. Geçerliyse 10 haneli sade biçim döner.
 */
function normalize_phone_tr(string $raw): ?string
{
    $digits = (string)preg_replace('/\D+/', '', $raw);
    if (str_starts_with($digits, '0090')) {
        $digits = substr($digits, 4);
    } elseif (str_starts_with($digits, '90') && strlen($digits) === 12) {
        $digits = substr($digits, 2);
    }
    if (str_starts_with($digits, '0')) {
        $digits = substr($digits, 1);
    }
    if (strlen($digits) !== 10) {
        return null;
    }
    if (!preg_match('/^(5[0-9]{9}|[234][0-9]{9}|850[0-9]{7})$/', $digits)) {
        return null;
    }
    return $digits;
}

function format_phone_tr(string $digits): string
{
    if (strlen($digits) !== 10) {
        return $digits;
    }
    return '0' . substr($digits, 0, 3) . ' ' . substr($digits, 3, 3) . ' ' . substr($digits, 6, 2) . ' ' . substr($digits, 8, 2);
}

function normalize_iban(string $raw): string
{
    return strtoupper((string)preg_replace('/\s+/', '', $raw));
}

function valid_iban(string $iban): bool
{
    return (bool)preg_match('/^[A-Z]{2}[0-9]{2}[0-9A-Z]{11,30}$/', $iban);
}

function format_iban(string $iban): string
{
    return trim(chunk_split(normalize_iban($iban), 4, ' '));
}

function site_logo_url(array $settings): ?string
{
    $logo = (string)($settings['logo'] ?? '');
    if ($logo === '' || !is_file(UPLOAD_PATH . '/' . basename($logo))) {
        return null;
    }
    return UPLOAD_URL . '/' . rawurlencode(basename($logo));
}

function get_bank_accounts(array $settings): array
{
    $accounts = $settings['bank_accounts'] ?? [];
    return is_array($accounts) ? array_values(array_filter($accounts, fn($a) => is_array($a) && ($a['iban'] ?? '') !== '')) : [];
}

function parse_price(string $raw): float
{
    $raw = trim($raw);
    if ($raw === '') {
        return 0.0;
    }
    // "1.250,50" ve "1250.50" biçimlerinin ikisini de kabul et
    if (str_contains($raw, ',')) {
        $raw = str_replace('.', '', $raw);
        $raw = str_replace(',', '.', $raw);
    }
    return max(0.0, (float)$raw);
}

/* =========================================================
 *  Görsel yükleme
 * ========================================================= */

function allowed_image_extensions(): array
{
    return ['jpg', 'jpeg', 'png', 'webp', 'gif'];
}

/**
 * $_FILES['images'] biçimindeki çoklu dosya girdisini işler,
 * kaydedilen dosya adlarının listesini döndürür.
 */
function handle_image_uploads(array $files, int $limit, array &$errors): array
{
    $saved = [];
    if (empty($files['name']) || !is_array($files['name'])) {
        return $saved;
    }

    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if (count($saved) >= $limit) {
            $errors[] = 'En fazla ' . MAX_IMAGES_PER_PRODUCT . ' görsel yüklenebilir; fazlası atlandı.';
            break;
        }
        $error = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $name = (string)$files['name'][$i];
        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = '"' . $name . '" yüklenemedi (hata kodu: ' . $error . ').';
            continue;
        }
        $tmp  = (string)$files['tmp_name'][$i];
        $size = (int)$files['size'][$i];

        if ($size > MAX_IMAGE_SIZE) {
            $errors[] = '"' . $name . '" 5 MB sınırını aşıyor.';
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, allowed_image_extensions(), true)) {
            $errors[] = '"' . $name . '" desteklenmeyen dosya türü. (jpg, jpeg, png, webp, gif)';
            continue;
        }
        $info = @getimagesize($tmp);
        if ($info === false) {
            $errors[] = '"' . $name . '" geçerli bir görsel dosyası değil.';
            continue;
        }

        $newName = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $target  = UPLOAD_PATH . '/' . $newName;
        if (is_uploaded_file($tmp) ? move_uploaded_file($tmp, $target) : rename($tmp, $target)) {
            $saved[] = $newName;
        } else {
            $errors[] = '"' . $name . '" sunucuya kaydedilemedi.';
        }
    }
    return $saved;
}

/**
 * Yüklenen logoyu ortadan kare olarak kırpar, boyutlandırır ve
 * şeffaflığı koruyarak PNG olarak kaydeder. Başarıda dosya adını,
 * hatada null döndürür.
 */
function process_square_logo(string $tmpPath, int $size = 256): ?string
{
    $raw = @file_get_contents($tmpPath);
    if ($raw === false) {
        return null;
    }
    $src = @imagecreatefromstring($raw);
    if ($src === false) {
        return null;
    }

    $w = imagesx($src);
    $h = imagesy($src);
    $edge = min($w, $h);
    $srcX = (int)(($w - $edge) / 2);
    $srcY = (int)(($h - $edge) / 2);

    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefill($out, 0, 0, $transparent);

    imagecopyresampled($out, $src, 0, 0, $srcX, $srcY, $size, $size, $edge, $edge);
    imagedestroy($src);

    $name   = 'logo_' . bin2hex(random_bytes(6)) . '.png';
    $target = UPLOAD_PATH . '/' . $name;
    $ok = imagepng($out, $target, 6);
    imagedestroy($out);

    return $ok ? $name : null;
}

function delete_image_file(string $filename): void
{
    $filename = basename($filename);
    if ($filename === '' || $filename === '.htaccess') {
        return;
    }
    $path = UPLOAD_PATH . '/' . $filename;
    if (is_file($path)) {
        @unlink($path);
    }
}

function product_image_url(array $product): ?string
{
    $images = $product['images'] ?? [];
    if (empty($images)) {
        return null;
    }
    return UPLOAD_URL . '/' . rawurlencode($images[0]);
}
