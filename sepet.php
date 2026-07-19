<?php
require_once __DIR__ . '/includes/config.php';

$settings = get_settings();

/* ---- Sepet işlemleri ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
        redirect('sepet.php');
    }
    $action = (string)($_POST['action'] ?? '');
    $cart   = get_cart();

    if ($action === 'add') {
        $id  = (string)($_POST['id'] ?? '');
        $qty = max(1, min(99, (int)($_POST['qty'] ?? 1)));
        $p   = find_by_id(get_products(), $id);
        if (!$p || !product_buyable($p)) {
            flash_set('error', 'Bu ürün şu anda satın alınamıyor.');
            redirect('sepet.php');
        }
        $newQty = ($cart[$id] ?? 0) + $qty;
        $stock  = $p['stock'] ?? '';
        if ($stock !== '' && $newQty > (int)$stock) {
            $newQty = (int)$stock;
            flash_set('error', 'Stok sınırına ulaşıldı: bu üründen en fazla ' . (int)$stock . ' adet ekleyebilirsiniz.');
        } else {
            flash_set('success', '"' . $p['name'] . '" sepetinize eklendi.');
        }
        $cart[$id] = min(99, $newQty);
        save_cart($cart);
        redirect('sepet.php');
    }

    if ($action === 'update') {
        $quantities = (array)($_POST['qty'] ?? []);
        foreach ($quantities as $id => $qty) {
            $id  = (string)$id;
            $qty = (int)$qty;
            if (!isset($cart[$id])) {
                continue;
            }
            if ($qty <= 0) {
                unset($cart[$id]);
            } else {
                $cart[$id] = min(99, $qty);
            }
        }
        save_cart($cart);
        flash_set('success', 'Sepetiniz güncellendi.');
        redirect('sepet.php');
    }

    if ($action === 'remove') {
        $id = (string)($_POST['id'] ?? '');
        unset($cart[$id]);
        save_cart($cart);
        flash_set('success', 'Ürün sepetinizden çıkarıldı.');
        redirect('sepet.php');
    }

    if ($action === 'clear') {
        save_cart([]);
        flash_set('success', 'Sepetiniz boşaltıldı.');
        redirect('sepet.php');
    }

    redirect('sepet.php');
}

$items = cart_items();
$total = cart_total($items);

$pageTitle = 'Sepetim';
require __DIR__ . '/includes/header.php';
?>

<section class="products-section">
    <div class="container">
        <ol class="checkout-steps">
            <li class="active"><span>1</span> Sepet</li>
            <li><span>2</span> Teslimat &amp; Ödeme</li>
            <li><span>3</span> Sipariş Onayı</li>
        </ol>

        <?php public_flashes(); ?>

        <?php if (empty($items)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1.4"></circle><circle cx="19" cy="21" r="1.4"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                <h3>Sepetiniz boş</h3>
                <p>Ürünlerimize göz atın ve beğendiğiniz parçaları sepetinize ekleyin.</p>
                <a class="btn btn-primary" href="index.php#urunler">Alışverişe Başla</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <form method="post" action="sepet.php" class="cart-card">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <table class="cart-table">
                        <thead>
                            <tr><th colspan="2">Ürün</th><th>Birim Fiyat</th><th>Adet</th><th>Tutar</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item):
                            $p   = $item['product'];
                            $img = product_image_url($p);
                        ?>
                            <tr>
                                <td class="cart-img-cell">
                                    <a href="urun.php?id=<?= e($p['id']) ?>">
                                        <?php if ($img): ?>
                                            <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>">
                                        <?php else: ?>
                                            <span class="cart-noimg"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"></path></svg></span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="cart-name-cell">
                                    <a href="urun.php?id=<?= e($p['id']) ?>"><?= e($p['name']) ?></a>
                                    <?php if (($p['code'] ?? '') !== ''): ?><small class="muted"><?= e($p['code']) ?></small><?php endif; ?>
                                </td>
                                <td data-label="Birim Fiyat"><?= e(format_price($p['price'], $settings['currency'])) ?></td>
                                <td data-label="Adet">
                                    <input class="qty-input" type="number" name="qty[<?= e($p['id']) ?>]" value="<?= (int)$item['qty'] ?>" min="0" max="<?= ($p['stock'] ?? '') !== '' ? (int)$p['stock'] : 99 ?>">
                                </td>
                                <td data-label="Tutar"><strong><?= e(format_price($item['line_total'], $settings['currency'])) ?></strong></td>
                                <td class="cart-remove-cell">
                                    <button type="submit" name="qty[<?= e($p['id']) ?>]" value="0" class="cart-remove" title="Sepetten çıkar" aria-label="<?= e($p['name']) ?> ürününü sepetten çıkar">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="cart-actions">
                        <a class="btn btn-ghost" href="index.php#urunler">&larr; Alışverişe Devam Et</a>
                        <button type="submit" class="btn btn-ghost">Sepeti Güncelle</button>
                    </div>
                </form>

                <aside class="cart-summary">
                    <h3>Sipariş Özeti</h3>
                    <div class="summary-row"><span>Ara Toplam</span><span><?= e(format_price($total, $settings['currency'])) ?></span></div>
                    <div class="summary-row muted small"><span>Kargo</span><span>Sipariş onayında bildirilir</span></div>
                    <div class="summary-row summary-total"><span>Toplam</span><span><?= e(format_price($total, $settings['currency'])) ?></span></div>
                    <a class="btn btn-primary btn-block" href="odeme.php">Ödeme Adımına Geç</a>
                    <p class="summary-note">Ödeme adımında teslimat bilgilerinizi girip ödeme yönteminizi seçebilirsiniz.</p>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
