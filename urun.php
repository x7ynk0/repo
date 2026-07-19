<?php
require_once __DIR__ . '/includes/config.php';

$settings   = get_settings();
$brands     = get_brands();
$categories = get_categories();

$product = find_by_id(get_products(), (string)($_GET['id'] ?? ''));
if (!$product || !($product['active'] ?? true)) {
    http_response_code(404);
    $pageTitle = 'Ürün Bulunamadı';
    require __DIR__ . '/includes/header.php';
    echo '<section class="products-section"><div class="container"><div class="empty-state"><h3>Ürün bulunamadı</h3><p>Aradığınız ürün kaldırılmış veya hiç eklenmemiş olabilir.</p><a class="btn btn-primary" href="index.php">Anasayfaya Dön</a></div></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

[$brand, $model] = find_brand_model($brands, $product['brand_id'] ?? null, $product['model_id'] ?? null);
$category = find_by_id($categories, $product['category_id'] ?? null);
$images   = $product['images'] ?? [];
$stock    = $product['stock'] ?? '';

$pageTitle = (string)$product['name'];
require __DIR__ . '/includes/header.php';
?>

<section class="products-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Anasayfa</a>
            <span>/</span>
            <?php if ($category): ?>
                <a href="index.php?kategori=<?= e($category['id']) ?>#urunler"><?= e($category['name']) ?></a>
                <span>/</span>
            <?php endif; ?>
            <strong><?= e($product['name']) ?></strong>
        </nav>

        <div class="product-detail">
            <div class="detail-gallery">
                <?php if (!empty($images)): ?>
                    <div class="gallery-main">
                        <img id="gallery-main-img" src="<?= e(UPLOAD_URL . '/' . rawurlencode($images[0])) ?>" alt="<?= e($product['name']) ?>">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <button type="button" class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>" data-src="<?= e(UPLOAD_URL . '/' . rawurlencode($img)) ?>">
                                <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($img)) ?>" alt="<?= e($product['name']) ?> görsel <?= $i + 1 ?>" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="gallery-main no-image">
                        <svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"></path></svg>
                        <span>Görsel Yok</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <div class="product-tags">
                    <?php if ($brand): ?><span class="tag"><?= e($brand['name'] . ($model ? ' ' . $model['name'] : '')) ?></span><?php endif; ?>
                    <?php if ($category): ?><span class="tag tag-soft"><?= e($category['name']) ?></span><?php endif; ?>
                </div>
                <h1><?= e($product['name']) ?></h1>
                <div class="detail-price"><?= e(format_price($product['price'] ?? 0, $settings['currency'])) ?></div>

                <table class="spec-table">
                    <?php if (($product['code'] ?? '') !== ''): ?>
                        <tr><th>Parça / OEM Kodu</th><td><?= e($product['code']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($brand): ?>
                        <tr><th>Marka</th><td><?= e($brand['name']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($model): ?>
                        <tr><th>Model</th><td><?= e($model['name']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($category): ?>
                        <tr><th>Kategori</th><td><?= e($category['name']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($stock !== ''): ?>
                        <tr><th>Stok Durumu</th>
                            <td>
                                <?php if ((int)$stock > 0): ?>
                                    <span class="badge badge-success">Stokta (<?= (int)$stock ?> adet)</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Stokta Yok</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>

                <?php if (($product['description'] ?? '') !== ''): ?>
                    <div class="detail-desc">
                        <h3>Ürün Açıklaması</h3>
                        <p><?= nl2br(e($product['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="detail-contact">
                    <?php if ($settings['phone'] !== ''): ?>
                        <a class="btn btn-primary" href="tel:<?= e(preg_replace('/\s+/', '', $settings['phone'])) ?>">Hemen Ara: <?= e($settings['phone']) ?></a>
                    <?php endif; ?>
                    <?php if ($settings['whatsapp'] !== ''): ?>
                        <a class="btn btn-whatsapp" href="https://wa.me/<?= e(preg_replace('/\D+/', '', $settings['whatsapp'])) ?>?text=<?= rawurlencode('Merhaba, "' . $product['name'] . '" ürünü hakkında bilgi almak istiyorum.' . (($product['code'] ?? '') !== '' ? ' (Parça Kodu: ' . $product['code'] . ')' : '')) ?>" target="_blank" rel="noopener">WhatsApp ile Sor</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
