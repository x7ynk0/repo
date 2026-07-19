<?php
require_once __DIR__ . '/includes/config.php';

$settings = get_settings();
$items    = cart_items();
$total    = cart_total($items);
$methods  = payment_methods();

if (empty($items)) {
    flash_set('error', 'Ödeme adımına geçmek için sepetinizde ürün bulunmalıdır.');
    redirect('sepet.php');
}

$errors = [];
$old    = [
    'name'    => '',
    'phone'   => '',
    'email'   => '',
    'address' => '',
    'note'    => '',
    'payment' => array_key_first($methods),
];

/* ---- Sipariş oluşturma ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
        redirect('odeme.php');
    }

    foreach (array_keys($old) as $key) {
        $old[$key] = trim((string)($_POST[$key] ?? ''));
    }

    if (mb_strlen($old['name']) < 3) {
        $errors[] = 'Ad Soyad alanı zorunludur.';
    }
    if (mb_strlen(preg_replace('/\D+/', '', $old['phone'])) < 10) {
        $errors[] = 'Geçerli bir telefon numarası girin.';
    }
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi girin.';
    }
    if (mb_strlen($old['address']) < 10) {
        $errors[] = 'Teslimat adresinizi eksiksiz girin.';
    }
    if (!isset($methods[$old['payment']])) {
        $errors[] = 'Geçerli bir ödeme yöntemi seçin.';
    }

    if (empty($errors)) {
        $orderItems = [];
        foreach ($items as $item) {
            $p = $item['product'];
            $orderItems[] = [
                'product_id' => $p['id'],
                'name'       => $p['name'],
                'code'       => $p['code'] ?? '',
                'price'      => (float)$p['price'],
                'qty'        => (int)$item['qty'],
                'total'      => (float)$item['line_total'],
            ];
        }

        $order = [
            'id'             => generate_id(),
            'no'             => generate_order_no(),
            'token'          => bin2hex(random_bytes(16)),
            'customer'       => [
                'name'    => $old['name'],
                'phone'   => $old['phone'],
                'email'   => $old['email'],
                'address' => $old['address'],
                'note'    => $old['note'],
            ],
            'items'          => $orderItems,
            'total'          => $total,
            'payment_method' => $old['payment'],
            'status'         => 'yeni',
            'created_at'     => date('c'),
            'updated_at'     => date('c'),
        ];

        $orders   = get_orders();
        $orders[] = $order;
        json_save('orders', $orders);

        // Stok düşümü (stok takibi yapılan ürünlerde)
        $products = get_products();
        foreach ($products as &$p) {
            foreach ($orderItems as $oi) {
                if ($p['id'] === $oi['product_id'] && ($p['stock'] ?? '') !== '') {
                    $p['stock'] = max(0, (int)$p['stock'] - $oi['qty']);
                }
            }
        }
        unset($p);
        json_save('products', $products);

        save_cart([]);
        redirect('siparis.php?no=' . urlencode($order['no']) . '&t=' . urlencode($order['token']));
    }
}

$pageTitle = 'Teslimat & Ödeme';
require __DIR__ . '/includes/header.php';
?>

<section class="products-section">
    <div class="container">
        <ol class="checkout-steps">
            <li class="done"><span>✓</span> Sepet</li>
            <li class="active"><span>2</span> Teslimat &amp; Ödeme</li>
            <li><span>3</span> Sipariş Onayı</li>
        </ol>

        <?php public_flashes(); ?>
        <?php foreach ($errors as $err): ?>
            <div class="notice notice-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" action="odeme.php" class="checkout-layout">
            <?= csrf_field() ?>
            <div class="checkout-main">
                <div class="checkout-card">
                    <h3>Teslimat Bilgileri</h3>
                    <div class="form-grid-public">
                        <div class="form-field">
                            <label for="co-name">Ad Soyad *</label>
                            <input type="text" id="co-name" name="name" required value="<?= e($old['name']) ?>" autocomplete="name">
                        </div>
                        <div class="form-field">
                            <label for="co-phone">Telefon *</label>
                            <input type="tel" id="co-phone" name="phone" required value="<?= e($old['phone']) ?>" placeholder="05xx xxx xx xx" autocomplete="tel">
                        </div>
                        <div class="form-field span-2">
                            <label for="co-email">E-posta</label>
                            <input type="email" id="co-email" name="email" value="<?= e($old['email']) ?>" autocomplete="email">
                        </div>
                        <div class="form-field span-2">
                            <label for="co-address">Teslimat Adresi *</label>
                            <textarea id="co-address" name="address" rows="3" required placeholder="Mahalle, cadde/sokak, bina ve daire no, ilçe / il" autocomplete="street-address"><?= e($old['address']) ?></textarea>
                        </div>
                        <div class="form-field span-2">
                            <label for="co-note">Sipariş Notu</label>
                            <textarea id="co-note" name="note" rows="2" placeholder="Araç bilgisi, şasi no veya eklemek istedikleriniz (isteğe bağlı)"><?= e($old['note']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="checkout-card">
                    <h3>Ödeme Yöntemi</h3>
                    <?php foreach ($methods as $key => $method): ?>
                        <label class="payment-option <?= $old['payment'] === $key ? 'selected' : '' ?>">
                            <input type="radio" name="payment" value="<?= e($key) ?>" <?= $old['payment'] === $key ? 'checked' : '' ?> required>
                            <span class="payment-option-body">
                                <strong><?= e($method['label']) ?></strong>
                                <small><?= e($method['note']) ?></small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="cart-summary">
                <h3>Sipariş Özeti</h3>
                <ul class="summary-items">
                    <?php foreach ($items as $item): ?>
                        <li>
                            <span><?= e($item['product']['name']) ?> <em>× <?= (int)$item['qty'] ?></em></span>
                            <span><?= e(format_price($item['line_total'], $settings['currency'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="summary-row"><span>Ara Toplam</span><span><?= e(format_price($total, $settings['currency'])) ?></span></div>
                <div class="summary-row muted small"><span>Kargo</span><span>Sipariş onayında bildirilir</span></div>
                <div class="summary-row summary-total"><span>Toplam</span><span><?= e(format_price($total, $settings['currency'])) ?></span></div>
                <button type="submit" class="btn btn-primary btn-block">Siparişi Tamamla</button>
                <p class="summary-note">Siparişi tamamladığınızda ödeme talimatları ve sipariş numaranız görüntülenir.</p>
                <a class="back-link" href="sepet.php">&larr; Sepete geri dön</a>
            </aside>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
