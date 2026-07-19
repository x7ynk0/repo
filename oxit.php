<?php
/**
 * OXIT — Yönetici Paneli
 * /oxit adresi üzerinden erişilir (.htaccess yönlendirmesi ile).
 */
require_once __DIR__ . '/includes/config.php';

$settings = get_settings();
$user     = current_user();
$page     = (string)($_GET['p'] ?? 'panel');

/* =========================================================
 *  POST işlemleri
 * ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if (!csrf_verify()) {
        flash_set('error', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
        redirect('oxit.php');
    }

    /* ---- İlk kurulum: ana yönetici oluşturma (yalnızca hiç kullanıcı yokken) ---- */
    if ($action === 'setup') {
        if (setup_completed()) {
            flash_set('error', 'Kurulum zaten tamamlanmış. Bu sayfadan yeni kayıt oluşturulamaz.');
            redirect('oxit.php');
        }
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['password_confirm'] ?? '');

        $errors = [];
        if (!preg_match('/^[a-zA-Z0-9._-]{3,32}$/', $username)) {
            $errors[] = 'Kullanıcı adı 3-32 karakter olmalı; yalnızca harf, rakam, nokta, alt çizgi ve tire içerebilir.';
        }
        if (mb_strlen($password) < 8) {
            $errors[] = 'Şifre en az 8 karakter olmalıdır.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Şifreler birbiriyle eşleşmiyor.';
        }
        if ($errors) {
            foreach ($errors as $err) {
                flash_set('error', $err);
            }
            redirect('oxit.php');
        }

        $newUser = [
            'id'         => generate_id(),
            'username'   => $username,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'role'       => 'admin',
            'created_at' => date('c'),
            'created_by' => null,
        ];
        json_save('users', [$newUser]);
        login_user($newUser);
        flash_set('success', 'Ana yönetici hesabı oluşturuldu. Hoş geldiniz!');
        redirect('oxit.php');
    }

    /* ---- Giriş ---- */
    if ($action === 'login') {
        if ($user) {
            redirect('oxit.php');
        }
        if (($wait = login_blocked()) > 0) {
            flash_set('error', 'Çok fazla hatalı deneme yapıldı. Lütfen ' . ceil($wait / 60) . ' dakika sonra tekrar deneyin.');
            redirect('oxit.php');
        }
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $found    = find_user_by_username($username);

        if ($found && password_verify($password, $found['password'])) {
            if (password_needs_rehash($found['password'], PASSWORD_DEFAULT)) {
                $users = get_users();
                foreach ($users as &$u) {
                    if ($u['id'] === $found['id']) {
                        $u['password'] = password_hash($password, PASSWORD_DEFAULT);
                    }
                }
                unset($u);
                json_save('users', $users);
            }
            login_user($found);
            redirect('oxit.php');
        }
        register_login_failure();
        flash_set('error', 'Kullanıcı adı veya şifre hatalı.');
        redirect('oxit.php');
    }

    /* ---- Bundan sonrası oturum gerektirir ---- */
    if (!$user) {
        flash_set('error', 'Bu işlem için giriş yapmalısınız.');
        redirect('oxit.php');
    }

    /* ---- Çıkış ---- */
    if ($action === 'logout') {
        logout_user();
        redirect('oxit.php');
    }

    /* ---- Ürün kaydet (ekle / düzenle) ---- */
    if ($action === 'product_save') {
        $products = get_products();
        $editId   = (string)($_POST['id'] ?? '');
        $existing = $editId !== '' ? find_by_id($products, $editId) : null;

        if ($editId !== '' && !$existing) {
            flash_set('error', 'Düzenlenmek istenen ürün bulunamadı.');
            redirect('oxit.php?p=urunler');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            flash_set('error', 'Ürün adı zorunludur.');
            redirect($editId !== '' ? 'oxit.php?p=urun-duzenle&id=' . urlencode($editId) : 'oxit.php?p=urun-ekle');
        }

        $brandId    = (string)($_POST['brand_id'] ?? '');
        $modelId    = (string)($_POST['model_id'] ?? '');
        $categoryId = (string)($_POST['category_id'] ?? '');
        $brands     = get_brands();
        [$brand, ]  = find_brand_model($brands, $brandId, $modelId);
        if ($brandId !== '' && !$brand) {
            $brandId = '';
        }
        if ($modelId !== '' && (!$brand || !find_by_id($brand['models'] ?? [], $modelId))) {
            $modelId = '';
        }
        if ($categoryId !== '' && !find_by_id(get_categories(), $categoryId)) {
            $categoryId = '';
        }

        $stockRaw = trim((string)($_POST['stock'] ?? ''));

        $record = [
            'id'          => $existing['id'] ?? generate_id(),
            'name'        => $name,
            'code'        => trim((string)($_POST['code'] ?? '')),
            'brand_id'    => $brandId,
            'model_id'    => $modelId,
            'category_id' => $categoryId,
            'price'       => parse_price((string)($_POST['price'] ?? '')),
            'stock'       => $stockRaw === '' ? '' : max(0, (int)$stockRaw),
            'description' => trim((string)($_POST['description'] ?? '')),
            'featured'    => isset($_POST['featured']),
            'active'      => isset($_POST['active']),
            'images'      => $existing['images'] ?? [],
            'created_at'  => $existing['created_at'] ?? date('c'),
            'updated_at'  => date('c'),
            'created_by'  => $existing['created_by'] ?? $user['id'],
        ];

        // Mevcut görsellerden silinmek istenenler
        $removeImages = (array)($_POST['remove_images'] ?? []);
        if ($removeImages) {
            $kept = [];
            foreach ($record['images'] as $img) {
                if (in_array($img, $removeImages, true)) {
                    delete_image_file($img);
                } else {
                    $kept[] = $img;
                }
            }
            $record['images'] = $kept;
        }

        // Yeni görseller
        $uploadErrors = [];
        if (!empty($_FILES['images'])) {
            $slots = MAX_IMAGES_PER_PRODUCT - count($record['images']);
            $new   = handle_image_uploads($_FILES['images'], max(0, $slots), $uploadErrors);
            $record['images'] = array_merge($record['images'], $new);
        }
        foreach ($uploadErrors as $err) {
            flash_set('error', $err);
        }

        // Ana görsel seçimi
        $mainImage = (string)($_POST['main_image'] ?? '');
        if ($mainImage !== '' && in_array($mainImage, $record['images'], true)) {
            $record['images'] = array_values(array_merge([$mainImage], array_diff($record['images'], [$mainImage])));
        }

        if ($existing) {
            foreach ($products as $i => $p) {
                if ($p['id'] === $record['id']) {
                    $products[$i] = $record;
                    break;
                }
            }
            flash_set('success', '"' . $record['name'] . '" güncellendi.');
        } else {
            $products[] = $record;
            flash_set('success', '"' . $record['name'] . '" ürünü eklendi.');
        }
        json_save('products', $products);
        redirect('oxit.php?p=urunler');
    }

    /* ---- Ürün sil ---- */
    if ($action === 'product_delete') {
        $products = get_products();
        $id       = (string)($_POST['id'] ?? '');
        $target   = find_by_id($products, $id);
        if ($target) {
            foreach ($target['images'] ?? [] as $img) {
                delete_image_file($img);
            }
            $products = array_values(array_filter($products, fn($p) => $p['id'] !== $id));
            json_save('products', $products);
            flash_set('success', '"' . $target['name'] . '" ürünü ve görselleri silindi.');
        } else {
            flash_set('error', 'Silinmek istenen ürün bulunamadı.');
        }
        redirect('oxit.php?p=urunler');
    }

    /* ---- Kategori ekle / yeniden adlandır / sil ---- */
    if ($action === 'category_add') {
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            flash_set('error', 'Kategori adı boş olamaz.');
        } else {
            $cats = get_categories();
            foreach ($cats as $c) {
                if (mb_strtolower($c['name']) === mb_strtolower($name)) {
                    flash_set('error', 'Bu isimde bir kategori zaten var.');
                    redirect('oxit.php?p=kategoriler');
                }
            }
            $cats[] = ['id' => generate_id(), 'name' => $name];
            json_save('categories', $cats);
            flash_set('success', '"' . $name . '" kategorisi eklendi.');
        }
        redirect('oxit.php?p=kategoriler');
    }

    if ($action === 'category_rename') {
        $id   = (string)($_POST['id'] ?? '');
        $name = trim((string)($_POST['name'] ?? ''));
        $cats = get_categories();
        if ($name === '') {
            flash_set('error', 'Kategori adı boş olamaz.');
        } else {
            foreach ($cats as &$c) {
                if ($c['id'] === $id) {
                    $c['name'] = $name;
                    flash_set('success', 'Kategori adı güncellendi.');
                }
            }
            unset($c);
            json_save('categories', $cats);
        }
        redirect('oxit.php?p=kategoriler');
    }

    if ($action === 'category_delete') {
        $id   = (string)($_POST['id'] ?? '');
        $used = count(array_filter(get_products(), fn($p) => ($p['category_id'] ?? '') === $id));
        if ($used > 0) {
            flash_set('error', 'Bu kategori ' . $used . ' üründe kullanılıyor. Önce ilgili ürünleri düzenleyin veya silin.');
        } else {
            $cats = array_values(array_filter(get_categories(), fn($c) => $c['id'] !== $id));
            json_save('categories', $cats);
            flash_set('success', 'Kategori silindi.');
        }
        redirect('oxit.php?p=kategoriler');
    }

    /* ---- Marka & model yönetimi ---- */
    if ($action === 'brand_add') {
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            flash_set('error', 'Marka adı boş olamaz.');
        } else {
            $brands = get_brands();
            foreach ($brands as $b) {
                if (mb_strtolower($b['name']) === mb_strtolower($name)) {
                    flash_set('error', 'Bu isimde bir marka zaten var.');
                    redirect('oxit.php?p=markalar');
                }
            }
            $brands[] = ['id' => generate_id(), 'name' => $name, 'models' => []];
            json_save('brands', $brands);
            flash_set('success', '"' . $name . '" markası eklendi.');
        }
        redirect('oxit.php?p=markalar');
    }

    if ($action === 'brand_delete') {
        $id   = (string)($_POST['id'] ?? '');
        $used = count(array_filter(get_products(), fn($p) => ($p['brand_id'] ?? '') === $id));
        if ($used > 0) {
            flash_set('error', 'Bu marka ' . $used . ' üründe kullanılıyor. Önce ilgili ürünleri düzenleyin veya silin.');
        } else {
            $brands = array_values(array_filter(get_brands(), fn($b) => $b['id'] !== $id));
            json_save('brands', $brands);
            flash_set('success', 'Marka silindi.');
        }
        redirect('oxit.php?p=markalar');
    }

    if ($action === 'model_add') {
        $brandId = (string)($_POST['brand_id'] ?? '');
        $name    = trim((string)($_POST['name'] ?? ''));
        $brands  = get_brands();
        if ($name === '') {
            flash_set('error', 'Model adı boş olamaz.');
        } else {
            $done = false;
            foreach ($brands as &$b) {
                if ($b['id'] === $brandId) {
                    foreach ($b['models'] ?? [] as $m) {
                        if (mb_strtolower($m['name']) === mb_strtolower($name)) {
                            flash_set('error', 'Bu model zaten mevcut.');
                            redirect('oxit.php?p=markalar&marka=' . urlencode($brandId));
                        }
                    }
                    $b['models'][] = ['id' => generate_id(), 'name' => $name];
                    $done = true;
                }
            }
            unset($b);
            if ($done) {
                json_save('brands', $brands);
                flash_set('success', '"' . $name . '" modeli eklendi.');
            } else {
                flash_set('error', 'Marka bulunamadı.');
            }
        }
        redirect('oxit.php?p=markalar&marka=' . urlencode($brandId));
    }

    if ($action === 'model_delete') {
        $brandId = (string)($_POST['brand_id'] ?? '');
        $modelId = (string)($_POST['model_id'] ?? '');
        $used    = count(array_filter(get_products(), fn($p) => ($p['model_id'] ?? '') === $modelId));
        if ($used > 0) {
            flash_set('error', 'Bu model ' . $used . ' üründe kullanılıyor. Önce ilgili ürünleri düzenleyin veya silin.');
        } else {
            $brands = get_brands();
            foreach ($brands as &$b) {
                if ($b['id'] === $brandId) {
                    $b['models'] = array_values(array_filter($b['models'] ?? [], fn($m) => $m['id'] !== $modelId));
                }
            }
            unset($b);
            json_save('brands', $brands);
            flash_set('success', 'Model silindi.');
        }
        redirect('oxit.php?p=markalar&marka=' . urlencode($brandId));
    }

    /* ---- Çalışan yönetimi (yalnızca ana yönetici) ---- */
    if ($action === 'employee_add') {
        if (!is_admin($user)) {
            flash_set('error', 'Çalışan eklemeye yalnızca ana yönetici yetkilidir.');
            redirect('oxit.php');
        }
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['password_confirm'] ?? '');

        if (!preg_match('/^[a-zA-Z0-9._-]{3,32}$/', $username)) {
            flash_set('error', 'Kullanıcı adı 3-32 karakter olmalı; yalnızca harf, rakam, nokta, alt çizgi ve tire içerebilir.');
        } elseif (find_user_by_username($username)) {
            flash_set('error', 'Bu kullanıcı adı zaten kullanılıyor.');
        } elseif (mb_strlen($password) < 8) {
            flash_set('error', 'Şifre en az 8 karakter olmalıdır.');
        } elseif ($password !== $confirm) {
            flash_set('error', 'Şifreler birbiriyle eşleşmiyor.');
        } else {
            $users   = get_users();
            $users[] = [
                'id'         => generate_id(),
                'username'   => $username,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role'       => 'calisan',
                'created_at' => date('c'),
                'created_by' => $user['id'],
            ];
            json_save('users', $users);
            flash_set('success', '"' . $username . '" çalışan hesabı oluşturuldu.');
        }
        redirect('oxit.php?p=calisanlar');
    }

    if ($action === 'employee_delete') {
        if (!is_admin($user)) {
            flash_set('error', 'Çalışan silmeye yalnızca ana yönetici yetkilidir.');
            redirect('oxit.php');
        }
        $id     = (string)($_POST['id'] ?? '');
        $target = find_by_id(get_users(), $id);
        if (!$target || $target['role'] === 'admin') {
            flash_set('error', 'Bu hesap silinemez.');
        } else {
            $users = array_values(array_filter(get_users(), fn($u) => $u['id'] !== $id));
            json_save('users', $users);
            flash_set('success', '"' . $target['username'] . '" çalışan hesabı silindi.');
        }
        redirect('oxit.php?p=calisanlar');
    }

    /* ---- Şifre değiştirme (herkes kendi şifresini) ---- */
    if ($action === 'password_change') {
        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['new_password_confirm'] ?? '');

        if (!password_verify($current, $user['password'])) {
            flash_set('error', 'Mevcut şifreniz hatalı.');
        } elseif (mb_strlen($new) < 8) {
            flash_set('error', 'Yeni şifre en az 8 karakter olmalıdır.');
        } elseif ($new !== $confirm) {
            flash_set('error', 'Yeni şifreler birbiriyle eşleşmiyor.');
        } else {
            $users = get_users();
            foreach ($users as &$u) {
                if ($u['id'] === $user['id']) {
                    $u['password'] = password_hash($new, PASSWORD_DEFAULT);
                }
            }
            unset($u);
            json_save('users', $users);
            flash_set('success', 'Şifreniz güncellendi.');
        }
        redirect('oxit.php?p=profil');
    }

    /* ---- Site ayarları ---- */
    if ($action === 'settings_save') {
        $new = get_settings();
        foreach (['site_title', 'slogan', 'phone', 'whatsapp', 'email', 'address', 'about', 'footer_text', 'currency'] as $key) {
            if (isset($_POST[$key])) {
                $new[$key] = trim((string)$_POST[$key]);
            }
        }
        if ($new['site_title'] === '') {
            $new['site_title'] = 'Araç Yedek Parça';
        }
        if ($new['currency'] === '') {
            $new['currency'] = '₺';
        }
        json_save('settings', $new);
        flash_set('success', 'Site ayarları kaydedildi.');
        redirect('oxit.php?p=ayarlar');
    }

    flash_set('error', 'Bilinmeyen işlem.');
    redirect('oxit.php');
}

/* =========================================================
 *  Görünüm yardımcıları
 * ========================================================= */

function admin_head(string $title): void
{
    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<meta name="robots" content="noindex, nofollow">';
    echo '<title>' . e($title) . ' | OXIT Yönetim</title>';
    echo '<link rel="stylesheet" href="assets/admin.css">';
    echo '<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">';
    echo '</head><body>';
}

function admin_flashes(): void
{
    foreach (flash_get() as $f) {
        $cls = $f['type'] === 'success' ? 'flash-success' : 'flash-error';
        echo '<div class="flash ' . $cls . '">' . e($f['message']) . '</div>';
    }
}

/* =========================================================
 *  Kurulum ekranı (hiç kullanıcı yokken, yalnızca bir kez)
 * ========================================================= */
if (!setup_completed()) {
    admin_head('Kurulum');
    ?>
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-logo">OXIT</div>
            <h1>İlk Kurulum</h1>
            <p class="auth-sub">Yönetici panelini kullanmaya başlamak için <strong>ana yönetici</strong> hesabını oluşturun. Bu ekran yalnızca bir kez görüntülenir; hesap oluşturulduktan sonra bu sayfadan yeni kayıt yapılamaz.</p>
            <?php admin_flashes(); ?>
            <form method="post" action="oxit.php" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="setup">
                <label for="su-username">Kullanıcı Adı</label>
                <input type="text" id="su-username" name="username" required minlength="3" maxlength="32" pattern="[a-zA-Z0-9._\-]{3,32}" placeholder="ör. yonetici">
                <label for="su-password">Şifre</label>
                <input type="password" id="su-password" name="password" required minlength="8" placeholder="En az 8 karakter">
                <label for="su-password2">Şifre (Tekrar)</label>
                <input type="password" id="su-password2" name="password_confirm" required minlength="8" placeholder="Şifrenizi tekrar girin">
                <button type="submit" class="btn btn-primary btn-block">Yönetici Hesabını Oluştur</button>
            </form>
            <p class="auth-note">Şifreniz güvenli bir şekilde (bcrypt) şifrelenerek saklanır.</p>
        </div>
    </div>
    </body></html>
    <?php
    exit;
}

/* =========================================================
 *  Giriş ekranı
 * ========================================================= */
if (!$user) {
    admin_head('Giriş');
    ?>
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-logo">OXIT</div>
            <h1>Yönetici Girişi</h1>
            <p class="auth-sub"><?= e($settings['site_title']) ?> yönetim paneline erişmek için giriş yapın.</p>
            <?php admin_flashes(); ?>
            <form method="post" action="oxit.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="login">
                <label for="li-username">Kullanıcı Adı</label>
                <input type="text" id="li-username" name="username" required autofocus autocomplete="username">
                <label for="li-password">Şifre</label>
                <input type="password" id="li-password" name="password" required autocomplete="current-password">
                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
            </form>
            <p class="auth-note"><a href="index.php">&larr; Siteye dön</a></p>
        </div>
    </div>
    </body></html>
    <?php
    exit;
}

/* =========================================================
 *  Panel sayfaları
 * ========================================================= */

$brands     = get_brands();
$categories = get_categories();
$products   = get_products();

// Çalışanlar sayfası yalnızca ana yönetici içindir
if ($page === 'calisanlar' && !is_admin($user)) {
    flash_set('error', 'Bu bölüme yalnızca ana yönetici erişebilir.');
    redirect('oxit.php');
}

$pageTitles = [
    'panel'        => 'Genel Bakış',
    'urunler'      => 'Ürünler',
    'urun-ekle'    => 'Yeni Ürün',
    'urun-duzenle' => 'Ürün Düzenle',
    'kategoriler'  => 'Kategoriler',
    'markalar'     => 'Marka & Modeller',
    'calisanlar'   => 'Çalışanlar',
    'ayarlar'      => 'Site Ayarları',
    'profil'       => 'Profilim',
];
if (!isset($pageTitles[$page])) {
    $page = 'panel';
}

admin_head($pageTitles[$page]);
?>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">OXIT</span>
            <span class="brand-site"><?= e($settings['site_title']) ?></span>
        </div>
        <nav class="sidebar-nav">
            <a href="oxit.php?p=panel" class="<?= $page === 'panel' ? 'active' : '' ?>">Genel Bakış</a>
            <a href="oxit.php?p=urunler" class="<?= in_array($page, ['urunler', 'urun-duzenle'], true) ? 'active' : '' ?>">Ürünler</a>
            <a href="oxit.php?p=urun-ekle" class="<?= $page === 'urun-ekle' ? 'active' : '' ?>">Yeni Ürün Ekle</a>
            <a href="oxit.php?p=kategoriler" class="<?= $page === 'kategoriler' ? 'active' : '' ?>">Kategoriler</a>
            <a href="oxit.php?p=markalar" class="<?= $page === 'markalar' ? 'active' : '' ?>">Marka &amp; Modeller</a>
            <?php if (is_admin($user)): ?>
                <a href="oxit.php?p=calisanlar" class="<?= $page === 'calisanlar' ? 'active' : '' ?>">Çalışanlar</a>
            <?php endif; ?>
            <a href="oxit.php?p=ayarlar" class="<?= $page === 'ayarlar' ? 'active' : '' ?>">Site Ayarları</a>
            <a href="oxit.php?p=profil" class="<?= $page === 'profil' ? 'active' : '' ?>">Profilim</a>
        </nav>
        <div class="sidebar-foot">
            <a href="index.php" target="_blank" rel="noopener">Siteyi Görüntüle ↗</a>
            <form method="post" action="oxit.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="link-btn">Çıkış Yap</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1><?= e($pageTitles[$page]) ?></h1>
            <div class="topbar-user">
                <span class="user-name"><?= e($user['username']) ?></span>
                <span class="user-role <?= is_admin($user) ? 'role-admin' : 'role-staff' ?>"><?= is_admin($user) ? 'Ana Yönetici' : 'Çalışan' ?></span>
            </div>
        </header>

        <div class="admin-content">
            <?php admin_flashes(); ?>

<?php
/* ---------------- Genel Bakış ---------------- */
if ($page === 'panel'):
    $activeCount   = count(array_filter($products, fn($p) => $p['active'] ?? true));
    $noStock       = count(array_filter($products, fn($p) => ($p['stock'] ?? '') !== '' && (int)$p['stock'] <= 0));
    $modelCount    = array_sum(array_map(fn($b) => count($b['models'] ?? []), $brands));
    $latest        = $products;
    usort($latest, fn($a, $b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    $latest = array_slice($latest, 0, 6);
?>
    <div class="stat-grid">
        <div class="stat-card"><span class="stat-value"><?= count($products) ?></span><span class="stat-label">Toplam Ürün</span></div>
        <div class="stat-card"><span class="stat-value"><?= $activeCount ?></span><span class="stat-label">Yayında Olan Ürün</span></div>
        <div class="stat-card"><span class="stat-value"><?= $noStock ?></span><span class="stat-label">Stokta Olmayan</span></div>
        <div class="stat-card"><span class="stat-value"><?= count($categories) ?></span><span class="stat-label">Kategori</span></div>
        <div class="stat-card"><span class="stat-value"><?= count($brands) ?></span><span class="stat-label">Marka</span></div>
        <div class="stat-card"><span class="stat-value"><?= $modelCount ?></span><span class="stat-label">Model</span></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Son Eklenen Ürünler</h2>
            <a class="btn btn-primary btn-sm" href="oxit.php?p=urun-ekle">+ Yeni Ürün</a>
        </div>
        <?php if (empty($latest)): ?>
            <p class="muted pad">Henüz ürün eklenmemiş. "Yeni Ürün Ekle" bölümünden ilk ürününüzü ekleyebilirsiniz.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Ürün</th><th>Marka / Model</th><th>Fiyat</th><th>Durum</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($latest as $p):
                    [$pb, $pm] = find_brand_model($brands, $p['brand_id'] ?? null, $p['model_id'] ?? null); ?>
                    <tr>
                        <td><strong><?= e($p['name']) ?></strong><?php if (($p['code'] ?? '') !== ''): ?><br><small class="muted"><?= e($p['code']) ?></small><?php endif; ?></td>
                        <td><?= $pb ? e($pb['name'] . ($pm ? ' ' . $pm['name'] : '')) : '<span class="muted">—</span>' ?></td>
                        <td><?= e(format_price($p['price'] ?? 0, $settings['currency'])) ?></td>
                        <td><?= ($p['active'] ?? true) ? '<span class="badge badge-success">Yayında</span>' : '<span class="badge badge-muted">Pasif</span>' ?></td>
                        <td class="ta-right"><a class="btn btn-ghost btn-sm" href="oxit.php?p=urun-duzenle&id=<?= e($p['id']) ?>">Düzenle</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php
/* ---------------- Ürün Listesi ---------------- */
elseif ($page === 'urunler'):
    $q = trim((string)($_GET['q'] ?? ''));
    $list = $products;
    if ($q !== '') {
        $list = array_filter($list, function ($p) use ($q) {
            $hay = mb_strtolower(($p['name'] ?? '') . ' ' . ($p['code'] ?? ''));
            return str_contains($hay, mb_strtolower($q));
        });
    }
    usort($list, fn($a, $b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
?>
    <div class="card">
        <div class="card-head">
            <form method="get" action="oxit.php" class="inline-search">
                <input type="hidden" name="p" value="urunler">
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Ürün adı veya kodu ara...">
                <button type="submit" class="btn btn-ghost btn-sm">Ara</button>
            </form>
            <a class="btn btn-primary btn-sm" href="oxit.php?p=urun-ekle">+ Yeni Ürün</a>
        </div>
        <?php if (empty($list)): ?>
            <p class="muted pad"><?= $q !== '' ? 'Aramanızla eşleşen ürün bulunamadı.' : 'Henüz ürün eklenmemiş.' ?></p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Görsel</th><th>Ürün</th><th>Marka / Model</th><th>Kategori</th><th>Fiyat</th><th>Stok</th><th>Durum</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($list as $p):
                    [$pb, $pm] = find_brand_model($brands, $p['brand_id'] ?? null, $p['model_id'] ?? null);
                    $pc  = find_by_id($categories, $p['category_id'] ?? null);
                    $img = product_image_url($p);
                ?>
                    <tr>
                        <td>
                            <?php if ($img): ?>
                                <img class="table-thumb" src="<?= e($img) ?>" alt="">
                            <?php else: ?>
                                <span class="table-thumb table-thumb-empty">—</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= e($p['name']) ?></strong><?php if (($p['code'] ?? '') !== ''): ?><br><small class="muted"><?= e($p['code']) ?></small><?php endif; ?></td>
                        <td><?= $pb ? e($pb['name'] . ($pm ? ' ' . $pm['name'] : '')) : '<span class="muted">—</span>' ?></td>
                        <td><?= $pc ? e($pc['name']) : '<span class="muted">—</span>' ?></td>
                        <td><?= e(format_price($p['price'] ?? 0, $settings['currency'])) ?></td>
                        <td><?= ($p['stock'] ?? '') === '' ? '<span class="muted">—</span>' : (int)$p['stock'] ?></td>
                        <td><?= ($p['active'] ?? true) ? '<span class="badge badge-success">Yayında</span>' : '<span class="badge badge-muted">Pasif</span>' ?></td>
                        <td class="ta-right nowrap">
                            <a class="btn btn-ghost btn-sm" href="urun.php?id=<?= e($p['id']) ?>" target="_blank" rel="noopener">Gör</a>
                            <a class="btn btn-ghost btn-sm" href="oxit.php?p=urun-duzenle&id=<?= e($p['id']) ?>">Düzenle</a>
                            <form method="post" action="oxit.php" class="inline-form" data-confirm='"<?= e($p['name']) ?>" ürünü ve tüm görselleri kalıcı olarak silinecek. Emin misiniz?'>
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="product_delete">
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php
/* ---------------- Ürün Ekle / Düzenle ---------------- */
elseif ($page === 'urun-ekle' || $page === 'urun-duzenle'):
    $editing = null;
    if ($page === 'urun-duzenle') {
        $editing = find_by_id($products, (string)($_GET['id'] ?? ''));
        if (!$editing) {
            echo '<div class="flash flash-error">Ürün bulunamadı.</div>';
            echo '<a class="btn btn-ghost" href="oxit.php?p=urunler">&larr; Ürün listesine dön</a>';
            $editing = false;
        }
    }
    if ($editing !== false):
        $v = fn(string $key, $default = '') => e((string)($editing[$key] ?? $default));
?>
    <form method="post" action="oxit.php" enctype="multipart/form-data" class="card form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="product_save">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($editing['id']) ?>"><?php endif; ?>

        <div class="form-grid">
            <div class="form-field span-2">
                <label for="pf-name">Ürün Adı *</label>
                <input type="text" id="pf-name" name="name" required value="<?= $v('name') ?>" placeholder="ör. Ön Fren Balata Takımı">
            </div>
            <div class="form-field">
                <label for="pf-code">Parça / OEM Kodu</label>
                <input type="text" id="pf-code" name="code" value="<?= $v('code') ?>" placeholder="ör. 7701208265">
            </div>
            <div class="form-field">
                <label for="pf-category">Kategori</label>
                <select id="pf-category" name="category_id">
                    <option value="">Seçiniz</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c['id']) ?>" <?= ($editing['category_id'] ?? '') === $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="pf-brand">Marka</label>
                <select id="pf-brand" name="brand_id" data-model-target="pf-model">
                    <option value="">Seçiniz</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= e($b['id']) ?>" <?= ($editing['brand_id'] ?? '') === $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="pf-model">Model</label>
                <select id="pf-model" name="model_id" data-selected="<?= e((string)($editing['model_id'] ?? '')) ?>">
                    <option value="">Seçiniz</option>
                </select>
            </div>
            <div class="form-field">
                <label for="pf-price">Fiyat (<?= e($settings['currency']) ?>)</label>
                <input type="text" id="pf-price" name="price" inputmode="decimal" value="<?= $editing && (float)($editing['price'] ?? 0) > 0 ? e(number_format((float)$editing['price'], 2, ',', '.')) : '' ?>" placeholder="Boş bırakılırsa 'Fiyat Sorunuz' görünür">
            </div>
            <div class="form-field">
                <label for="pf-stock">Stok Adedi</label>
                <input type="number" id="pf-stock" name="stock" min="0" value="<?= $v('stock') ?>" placeholder="Boş bırakılabilir">
            </div>
            <div class="form-field span-2">
                <label for="pf-desc">Ürün Açıklaması</label>
                <textarea id="pf-desc" name="description" rows="5" placeholder="Uyumlu araçlar, teknik özellikler, garanti bilgisi..."><?= $v('description') ?></textarea>
            </div>
            <div class="form-field span-2 checks">
                <label class="check">
                    <input type="checkbox" name="active" <?= !$editing || ($editing['active'] ?? true) ? 'checked' : '' ?>>
                    <span>Yayında (sitede görüntülensin)</span>
                </label>
                <label class="check">
                    <input type="checkbox" name="featured" <?= !empty($editing['featured']) ? 'checked' : '' ?>>
                    <span>Öne çıkan ürün olarak işaretle</span>
                </label>
            </div>
        </div>

        <?php if ($editing && !empty($editing['images'])): ?>
        <div class="form-section">
            <h3>Mevcut Görseller</h3>
            <p class="muted small">Ana görseli seçebilir veya silmek istediklerinizi işaretleyebilirsiniz. İlk görsel vitrinde kapak olarak kullanılır.</p>
            <div class="image-manage-grid">
                <?php foreach ($editing['images'] as $i => $img): ?>
                    <div class="image-manage-item">
                        <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($img)) ?>" alt="Görsel <?= $i + 1 ?>">
                        <label class="check small"><input type="radio" name="main_image" value="<?= e($img) ?>" <?= $i === 0 ? 'checked' : '' ?>> <span>Ana görsel</span></label>
                        <label class="check small danger"><input type="checkbox" name="remove_images[]" value="<?= e($img) ?>"> <span>Sil</span></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-section">
            <h3><?= $editing ? 'Yeni Görsel Ekle' : 'Ürün Görselleri' ?></h3>
            <p class="muted small">En fazla <?= MAX_IMAGES_PER_PRODUCT ?> görsel; jpg, png, webp veya gif; görsel başına en fazla 5 MB.</p>
            <input type="file" name="images[]" id="pf-images" accept=".jpg,.jpeg,.png,.webp,.gif" multiple>
            <div id="image-preview" class="image-preview"></div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $editing ? 'Değişiklikleri Kaydet' : 'Ürünü Ekle' ?></button>
            <a class="btn btn-ghost" href="oxit.php?p=urunler">Vazgeç</a>
        </div>
    </form>

    <script>
    window.BRAND_DATA = <?= json_encode(
        array_map(fn($b) => [
            'id' => $b['id'],
            'models' => array_map(fn($m) => ['id' => $m['id'], 'name' => $m['name']], $b['models'] ?? []),
        ], $brands),
        JSON_UNESCAPED_UNICODE
    ) ?>;
    </script>
<?php
    endif;

/* ---------------- Kategoriler ---------------- */
elseif ($page === 'kategoriler'):
    $usage = [];
    foreach ($products as $p) {
        $cid = $p['category_id'] ?? '';
        if ($cid !== '') {
            $usage[$cid] = ($usage[$cid] ?? 0) + 1;
        }
    }
?>
    <div class="two-col">
        <div class="card">
            <div class="card-head"><h2>Kategoriler (<?= count($categories) ?>)</h2></div>
            <?php if (empty($categories)): ?>
                <p class="muted pad">Henüz kategori yok.</p>
            <?php else: ?>
            <table class="table">
                <thead><tr><th>Kategori Adı</th><th>Ürün</th><th class="ta-right">İşlem</th></tr></thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td>
                            <form method="post" action="oxit.php" class="inline-rename">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="category_rename">
                                <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                                <input type="text" name="name" value="<?= e($c['name']) ?>" required>
                                <button type="submit" class="btn btn-ghost btn-sm" title="Adı güncelle">Kaydet</button>
                            </form>
                        </td>
                        <td><?= (int)($usage[$c['id']] ?? 0) ?></td>
                        <td class="ta-right">
                            <form method="post" action="oxit.php" class="inline-form" data-confirm='"<?= e($c['name']) ?>" kategorisi silinecek. Emin misiniz?'>
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="category_delete">
                                <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm" <?= ($usage[$c['id']] ?? 0) > 0 ? 'disabled title="Bu kategoride ürün var"' : '' ?>>Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="card-head"><h2>Yeni Kategori Ekle</h2></div>
            <form method="post" action="oxit.php" class="pad">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="category_add">
                <div class="form-field">
                    <label for="cat-name">Kategori Adı</label>
                    <input type="text" id="cat-name" name="name" required placeholder="ör. Fren Sistemi">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:12px">Ekle</button>
            </form>
        </div>
    </div>

<?php
/* ---------------- Marka & Modeller ---------------- */
elseif ($page === 'markalar'):
    $selBrandId = (string)($_GET['marka'] ?? '');
    $selBrand   = find_by_id($brands, $selBrandId) ?? ($brands[0] ?? null);
    $brandUsage = [];
    $modelUsage = [];
    foreach ($products as $p) {
        $bid = $p['brand_id'] ?? '';
        $mid = $p['model_id'] ?? '';
        if ($bid !== '') { $brandUsage[$bid] = ($brandUsage[$bid] ?? 0) + 1; }
        if ($mid !== '') { $modelUsage[$mid] = ($modelUsage[$mid] ?? 0) + 1; }
    }
?>
    <div class="two-col">
        <div class="card">
            <div class="card-head">
                <h2>Markalar (<?= count($brands) ?>)</h2>
            </div>
            <form method="post" action="oxit.php" class="pad add-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="brand_add">
                <input type="text" name="name" required placeholder="Yeni marka adı">
                <button type="submit" class="btn btn-primary btn-sm">Marka Ekle</button>
            </form>
            <div class="brand-list">
                <?php foreach ($brands as $b): ?>
                    <div class="brand-row <?= $selBrand && $b['id'] === $selBrand['id'] ? 'active' : '' ?>">
                        <a href="oxit.php?p=markalar&marka=<?= e($b['id']) ?>">
                            <?= e($b['name']) ?>
                            <small class="muted"><?= count($b['models'] ?? []) ?> model<?= isset($brandUsage[$b['id']]) ? ' · ' . $brandUsage[$b['id']] . ' ürün' : '' ?></small>
                        </a>
                        <form method="post" action="oxit.php" class="inline-form" data-confirm='"<?= e($b['name']) ?>" markası ve tüm modelleri silinecek. Emin misiniz?'>
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="brand_delete">
                            <input type="hidden" name="id" value="<?= e($b['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm" <?= ($brandUsage[$b['id']] ?? 0) > 0 ? 'disabled title="Bu markada ürün var"' : '' ?>>Sil</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <?php if ($selBrand): ?>
                <div class="card-head"><h2><?= e($selBrand['name']) ?> Modelleri (<?= count($selBrand['models'] ?? []) ?>)</h2></div>
                <form method="post" action="oxit.php" class="pad add-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="model_add">
                    <input type="hidden" name="brand_id" value="<?= e($selBrand['id']) ?>">
                    <input type="text" name="name" required placeholder="Yeni model adı">
                    <button type="submit" class="btn btn-primary btn-sm">Model Ekle</button>
                </form>
                <?php if (empty($selBrand['models'])): ?>
                    <p class="muted pad">Bu markaya henüz model eklenmemiş.</p>
                <?php else: ?>
                    <table class="table">
                        <thead><tr><th>Model</th><th>Ürün</th><th class="ta-right">İşlem</th></tr></thead>
                        <tbody>
                        <?php foreach ($selBrand['models'] as $m): ?>
                            <tr>
                                <td><?= e($m['name']) ?></td>
                                <td><?= (int)($modelUsage[$m['id']] ?? 0) ?></td>
                                <td class="ta-right">
                                    <form method="post" action="oxit.php" class="inline-form" data-confirm='"<?= e($m['name']) ?>" modeli silinecek. Emin misiniz?'>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="model_delete">
                                        <input type="hidden" name="brand_id" value="<?= e($selBrand['id']) ?>">
                                        <input type="hidden" name="model_id" value="<?= e($m['id']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" <?= ($modelUsage[$m['id']] ?? 0) > 0 ? 'disabled title="Bu modelde ürün var"' : '' ?>>Sil</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php else: ?>
                <p class="muted pad">Model yönetimi için soldan bir marka seçin veya yeni marka ekleyin.</p>
            <?php endif; ?>
        </div>
    </div>

<?php
/* ---------------- Çalışanlar (yalnızca ana yönetici) ---------------- */
elseif ($page === 'calisanlar'):
    $employees = array_values(array_filter(get_users(), fn($u) => $u['role'] !== 'admin'));
?>
    <div class="two-col">
        <div class="card">
            <div class="card-head"><h2>Çalışan Hesapları (<?= count($employees) ?>)</h2></div>
            <?php if (empty($employees)): ?>
                <p class="muted pad">Henüz çalışan hesabı oluşturulmamış. Sağdaki formdan yeni çalışan ekleyebilirsiniz.</p>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Kullanıcı Adı</th><th>Oluşturulma</th><th class="ta-right">İşlem</th></tr></thead>
                    <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><strong><?= e($emp['username']) ?></strong></td>
                            <td><?= e(date('d.m.Y H:i', strtotime($emp['created_at'] ?? 'now'))) ?></td>
                            <td class="ta-right">
                                <form method="post" action="oxit.php" class="inline-form" data-confirm='"<?= e($emp['username']) ?>" çalışan hesabı silinecek ve bir daha giriş yapamayacak. Emin misiniz?'>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="employee_delete">
                                    <input type="hidden" name="id" value="<?= e($emp['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hesabı Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <p class="muted pad small">Çalışan hesapları; ürün, kategori, marka/model ve site ayarları yönetimine tam erişime sahiptir. Çalışanlar bu listeyi göremez, çalışan ekleyemez ve silemez; yalnızca kendi şifrelerini güncelleyebilir.</p>
        </div>
        <div class="card">
            <div class="card-head"><h2>Çalışan Ekle</h2></div>
            <form method="post" action="oxit.php" class="pad" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="employee_add">
                <div class="form-field">
                    <label for="emp-username">Kullanıcı Adı</label>
                    <input type="text" id="emp-username" name="username" required minlength="3" maxlength="32" pattern="[a-zA-Z0-9._\-]{3,32}" placeholder="ör. mehmet">
                </div>
                <div class="form-field" style="margin-top:12px">
                    <label for="emp-password">Şifre</label>
                    <input type="password" id="emp-password" name="password" required minlength="8" placeholder="En az 8 karakter">
                </div>
                <div class="form-field" style="margin-top:12px">
                    <label for="emp-password2">Şifre (Tekrar)</label>
                    <input type="password" id="emp-password2" name="password_confirm" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:14px">Çalışan Hesabı Oluştur</button>
            </form>
        </div>
    </div>

<?php
/* ---------------- Site Ayarları ---------------- */
elseif ($page === 'ayarlar'):
?>
    <form method="post" action="oxit.php" class="card form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="settings_save">
        <div class="form-grid">
            <div class="form-field">
                <label for="st-title">Site Başlığı</label>
                <input type="text" id="st-title" name="site_title" value="<?= e($settings['site_title']) ?>" required>
            </div>
            <div class="form-field">
                <label for="st-currency">Para Birimi Simgesi</label>
                <input type="text" id="st-currency" name="currency" value="<?= e($settings['currency']) ?>" maxlength="6">
            </div>
            <div class="form-field span-2">
                <label for="st-slogan">Slogan</label>
                <input type="text" id="st-slogan" name="slogan" value="<?= e($settings['slogan']) ?>">
            </div>
            <div class="form-field">
                <label for="st-phone">Telefon</label>
                <input type="text" id="st-phone" name="phone" value="<?= e($settings['phone']) ?>" placeholder="ör. 0 (212) 000 00 00">
            </div>
            <div class="form-field">
                <label for="st-whatsapp">WhatsApp Numarası</label>
                <input type="text" id="st-whatsapp" name="whatsapp" value="<?= e($settings['whatsapp']) ?>" placeholder="ör. 90 555 000 00 00">
            </div>
            <div class="form-field span-2">
                <label for="st-email">E-posta</label>
                <input type="email" id="st-email" name="email" value="<?= e($settings['email']) ?>">
            </div>
            <div class="form-field span-2">
                <label for="st-address">Adres</label>
                <textarea id="st-address" name="address" rows="2"><?= e($settings['address']) ?></textarea>
            </div>
            <div class="form-field span-2">
                <label for="st-about">Hakkımızda</label>
                <textarea id="st-about" name="about" rows="4"><?= e($settings['about']) ?></textarea>
            </div>
            <div class="form-field span-2">
                <label for="st-footer">Alt Bilgi Metni</label>
                <input type="text" id="st-footer" name="footer_text" value="<?= e($settings['footer_text']) ?>" placeholder="Boş bırakılırsa otomatik telif metni gösterilir">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Ayarları Kaydet</button>
        </div>
    </form>

<?php
/* ---------------- Profil ---------------- */
elseif ($page === 'profil'):
?>
    <div class="two-col">
        <div class="card">
            <div class="card-head"><h2>Hesap Bilgileri</h2></div>
            <table class="table">
                <tr><th>Kullanıcı Adı</th><td><?= e($user['username']) ?></td></tr>
                <tr><th>Rol</th><td><?= is_admin($user) ? 'Ana Yönetici' : 'Çalışan' ?></td></tr>
                <tr><th>Hesap Oluşturulma</th><td><?= e(date('d.m.Y H:i', strtotime($user['created_at'] ?? 'now'))) ?></td></tr>
            </table>
        </div>
        <div class="card">
            <div class="card-head"><h2>Şifre Değiştir</h2></div>
            <form method="post" action="oxit.php" class="pad" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password_change">
                <div class="form-field">
                    <label for="pw-current">Mevcut Şifre</label>
                    <input type="password" id="pw-current" name="current_password" required autocomplete="current-password">
                </div>
                <div class="form-field" style="margin-top:12px">
                    <label for="pw-new">Yeni Şifre</label>
                    <input type="password" id="pw-new" name="new_password" required minlength="8" autocomplete="new-password" placeholder="En az 8 karakter">
                </div>
                <div class="form-field" style="margin-top:12px">
                    <label for="pw-new2">Yeni Şifre (Tekrar)</label>
                    <input type="password" id="pw-new2" name="new_password_confirm" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:14px">Şifreyi Güncelle</button>
            </form>
        </div>
    </div>
<?php endif; ?>

        </div>
    </div>
</div>
<script src="assets/admin.js"></script>
</body>
</html>
