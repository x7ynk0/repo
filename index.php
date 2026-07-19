<?php
require_once __DIR__ . '/includes/config.php';

$settings   = get_settings();
$brands     = get_brands();
$categories = get_categories();
$products   = array_values(array_filter(get_products(), fn($p) => ($p['active'] ?? true)));

/* ---- Filtreler ---- */
$q          = trim((string)($_GET['q'] ?? ''));
$brandId    = (string)($_GET['marka'] ?? '');
$modelId    = (string)($_GET['model'] ?? '');
$categoryId = (string)($_GET['kategori'] ?? '');
$sort       = (string)($_GET['sirala'] ?? 'yeni');

$filtered = array_filter($products, function (array $p) use ($q, $brandId, $modelId, $categoryId) {
    if ($brandId !== '' && ($p['brand_id'] ?? '') !== $brandId) {
        return false;
    }
    if ($modelId !== '' && ($p['model_id'] ?? '') !== $modelId) {
        return false;
    }
    if ($categoryId !== '' && ($p['category_id'] ?? '') !== $categoryId) {
        return false;
    }
    if ($q !== '') {
        $haystack = mb_strtolower(($p['name'] ?? '') . ' ' . ($p['code'] ?? '') . ' ' . ($p['description'] ?? ''));
        if (!str_contains($haystack, mb_strtolower($q))) {
            return false;
        }
    }
    return true;
});

usort($filtered, function (array $a, array $b) use ($sort) {
    return match ($sort) {
        'fiyat-artan' => ((float)($a['price'] ?? 0)) <=> ((float)($b['price'] ?? 0)),
        'fiyat-azalan' => ((float)($b['price'] ?? 0)) <=> ((float)($a['price'] ?? 0)),
        'ad' => strcoll_tr((string)($a['name'] ?? ''), (string)($b['name'] ?? '')),
        default => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')),
    };
});

/* ---- Sayfalama ---- */
$perPage = 12;
$total   = count($filtered);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min($pages, max(1, (int)($_GET['sayfa'] ?? 1)));
$items   = array_slice(array_values($filtered), ($page - 1) * $perPage, $perPage);

$hasFilter = $q !== '' || $brandId !== '' || $modelId !== '' || $categoryId !== '';

/* ---- Öne çıkanlar (filtresiz ilk sayfada) ---- */
$featuredItems = [];
if (!$hasFilter && $page === 1) {
    $featuredItems = array_slice(array_values(array_filter($products, fn($p) => !empty($p['featured']))), 0, 4);
}

/* Sepete ekledikten sonra bu sayfaya geri dönülür */
$returnUrl = 'index.php' . ($_GET ? '?' . http_build_query($_GET) : '') . '#urunler';

function page_url(int $p): string
{
    $params = $_GET;
    $params['sayfa'] = $p;
    return 'index.php?' . http_build_query($params) . '#urunler';
}

function render_product_card(array $p, array $brands, array $categories, array $settings, string $returnUrl): void
{
    [$pb, $pm] = find_brand_model($brands, $p['brand_id'] ?? null, $p['model_id'] ?? null);
    $pc  = find_by_id($categories, $p['category_id'] ?? null);
    $img = product_image_url($p);
    ?>
    <article class="product-card">
        <a class="product-thumb" href="urun.php?id=<?= e($p['id']) ?>">
            <?php if ($img): ?>
                <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
            <?php else: ?>
                <div class="no-image">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"></path></svg>
                    <span>Görsel Yok</span>
                </div>
            <?php endif; ?>
            <?php if (($p['stock'] ?? '') !== '' && (int)$p['stock'] <= 0): ?>
                <span class="badge badge-danger thumb-badge">Stokta Yok</span>
            <?php elseif (!empty($p['featured'])): ?>
                <span class="badge badge-accent thumb-badge">Öne Çıkan</span>
            <?php endif; ?>
        </a>
        <div class="product-body">
            <div class="product-tags">
                <?php if ($pb): ?><span class="tag"><?= e($pb['name'] . ($pm ? ' ' . $pm['name'] : '')) ?></span><?php endif; ?>
                <?php if ($pc): ?><span class="tag tag-soft"><?= e($pc['name']) ?></span><?php endif; ?>
            </div>
            <h3><a href="urun.php?id=<?= e($p['id']) ?>"><?= e($p['name']) ?></a></h3>
            <?php if (($p['code'] ?? '') !== ''): ?><p class="product-code"><?= e($p['code']) ?></p><?php endif; ?>
            <div class="product-foot">
                <span class="product-price"><?= e(format_price($p['price'] ?? 0, $settings['currency'])) ?></span>
                <?php if (product_buyable($p)): ?>
                    <form method="post" action="sepet.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                        <input type="hidden" name="qty" value="1">
                        <input type="hidden" name="return" value="<?= e($returnUrl) ?>">
                        <button type="submit" class="btn-add" title="Sepete Ekle" aria-label="<?= e($p['name']) ?> ürününü sepete ekle">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1.4"></circle><circle cx="19" cy="21" r="1.4"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}

$pageTitle = 'Anasayfa';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-text">
            <h1>Aracınıza Uygun Yedek Parçayı Bulun</h1>
            <p><?= e($settings['slogan']) ?></p>
        </div>

        <form method="get" action="index.php#urunler" class="search-card">
            <div class="search-row">
                <div class="filter-field grow">
                    <label for="f-q">Parça Adı / OEM Kodu</label>
                    <input type="text" id="f-q" name="q" value="<?= e($q) ?>" placeholder="ör. fren balatası, 7701208265...">
                </div>
                <div class="filter-field">
                    <label for="f-marka">Marka</label>
                    <select id="f-marka" name="marka" data-model-target="f-model">
                        <option value="">Tüm Markalar</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= e($b['id']) ?>" <?= $b['id'] === $brandId ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="f-model">Model</label>
                    <select id="f-model" name="model" data-selected="<?= e($modelId) ?>">
                        <option value="">Tüm Modeller</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="f-kategori">Kategori</label>
                    <select id="f-kategori" name="kategori">
                        <option value="">Tüm Kategoriler</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c['id']) ?>" <?= $c['id'] === $categoryId ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="f-sirala">Sıralama</label>
                    <select id="f-sirala" name="sirala">
                        <option value="yeni" <?= $sort === 'yeni' ? 'selected' : '' ?>>En Yeni</option>
                        <option value="fiyat-artan" <?= $sort === 'fiyat-artan' ? 'selected' : '' ?>>Fiyat (Artan)</option>
                        <option value="fiyat-azalan" <?= $sort === 'fiyat-azalan' ? 'selected' : '' ?>>Fiyat (Azalan)</option>
                        <option value="ad" <?= $sort === 'ad' ? 'selected' : '' ?>>Ada Göre (A-Z)</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        Parça Ara
                    </button>
                    <?php if ($hasFilter): ?><a href="index.php#urunler" class="btn btn-ghost">Temizle</a><?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="features-strip">
    <div class="container features-grid">
        <div class="feature-item">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 18H3a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v9"></path><path d="M15 9h4l3 4v4a1 1 0 0 1-1 1h-1"></path><circle cx="7.5" cy="18" r="2"></circle><circle cx="17.5" cy="18" r="2"></circle></svg>
            <div><strong>Hızlı Kargo</strong><span>Onaylanan siparişler aynı gün hazırlanır</span></div>
        </div>
        <div class="feature-item">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path><path d="m9 12 2 2 4-4"></path></svg>
            <div><strong>Güvenli Ödeme</strong><span>Sipariş numaralı takipli ödeme sistemi</span></div>
        </div>
        <div class="feature-item">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><circle cx="12" cy="12" r="3"></circle><path d="M12 3v3M12 18v3M3 12h3M18 12h3"></path></svg>
            <div><strong>Orijinal &amp; Muadil</strong><span>Kalite denetiminden geçmiş parçalar</span></div>
        </div>
        <div class="feature-item">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.5 8.5 8.5 0 0 1-3.9-.95L3 21l1.95-5.6A8.5 8.5 0 0 1 4 11.5a8.38 8.38 0 0 1 8.5-8.5 8.38 8.38 0 0 1 8.5 8.5"></path></svg>
            <div><strong>Uzman Destek</strong><span>Şasi no ile ücretsiz uyum kontrolü</span></div>
        </div>
    </div>
</section>

<?php if (!empty($featuredItems)): ?>
<section class="products-section featured-section">
    <div class="container">
        <div class="section-head">
            <h2>Öne Çıkan Ürünler</h2>
        </div>
        <div class="product-grid">
            <?php foreach ($featuredItems as $p) {
                render_product_card($p, $brands, $categories, $settings, $returnUrl);
            } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="products-section" id="urunler">
    <div class="container">
        <div class="section-head">
            <h2><?= $hasFilter ? 'Arama Sonuçları' : 'Tüm Ürünler' ?></h2>
            <span class="muted"><?= (int)$total ?> ürün bulundu</span>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                <?php if ($hasFilter): ?>
                    <h3>Sonuç bulunamadı</h3>
                    <p>Arama kriterlerinize uygun ürün bulunamadı. Filtreleri değiştirerek tekrar deneyin veya aradığınız parçayı bize sorun.</p>
                    <a class="btn btn-primary" href="iletisim.php">Parçayı Bize Sorun</a>
                <?php else: ?>
                    <h3>Ürünlerimiz çok yakında burada</h3>
                    <p>Kataloğumuz güncelleniyor. Aradığınız parça için bizimle iletişime geçebilirsiniz.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($items as $p) {
                    render_product_card($p, $brands, $categories, $settings, $returnUrl);
                } ?>
            </div>

            <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Sayfalama">
                <?php if ($page > 1): ?><a href="<?= e(page_url($page - 1)) ?>">&laquo; Önceki</a><?php endif; ?>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= e(page_url($i)) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $pages): ?><a href="<?= e(page_url($page + 1)) ?>">Sonraki &raquo;</a><?php endif; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<script>
window.BRAND_DATA = <?= json_encode(
    array_map(fn($b) => [
        'id' => $b['id'],
        'models' => array_map(fn($m) => ['id' => $m['id'], 'name' => $m['name']], $b['models'] ?? []),
    ], $brands),
    JSON_UNESCAPED_UNICODE
) ?>;
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
