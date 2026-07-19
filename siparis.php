<?php
require_once __DIR__ . '/includes/config.php';

$settings = get_settings();
$no       = (string)($_GET['no'] ?? '');
$token    = (string)($_GET['t'] ?? '');

$order = find_order_by_no($no);
if (!$order || $token === '' || !hash_equals((string)($order['token'] ?? ''), $token)) {
    http_response_code(404);
    $pageTitle = 'Sipariş Bulunamadı';
    require __DIR__ . '/includes/header.php';
    echo '<section class="products-section"><div class="container"><div class="empty-state"><h3>Sipariş bulunamadı</h3><p>Sipariş bağlantınız hatalı veya süresi dolmuş olabilir. Sorularınız için bizimle iletişime geçebilirsiniz.</p><a class="btn btn-primary" href="iletisim.php">İletişime Geç</a></div></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$statuses     = order_statuses();
$methods      = payment_methods();
$bankAccounts = get_bank_accounts($settings);
$isHavale     = ($order['payment_method'] ?? '') === 'havale';
$methodLabel  = $methods[$order['payment_method'] ?? '']['label'] ?? 'Bilinmiyor';

$pageTitle = 'Sipariş Onayı';
require __DIR__ . '/includes/header.php';
?>

<section class="products-section">
    <div class="container narrow">
        <ol class="checkout-steps">
            <li class="done"><span>✓</span> Sepet</li>
            <li class="done"><span>✓</span> Teslimat &amp; Ödeme</li>
            <li class="active"><span>3</span> Sipariş Onayı</li>
        </ol>

        <div class="order-success">
            <div class="order-success-icon">
                <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"></path></svg>
            </div>
            <h1>Siparişiniz Alındı</h1>
            <p>Sipariş numaranız: <strong class="order-no"><?= e($order['no']) ?></strong></p>
            <p class="muted">Bu sayfayı yer imlerinize ekleyerek siparişinizin durumunu takip edebilirsiniz.</p>
        </div>

        <?php if ($isHavale): ?>
        <div class="checkout-card">
            <h3>Ödeme Talimatları — <?= e($methodLabel) ?></h3>
            <?php if (!empty($bankAccounts)): ?>
                <p class="payment-note">Sipariş tutarını aşağıdaki hesaba gönderin. Ödeme açıklamasına <strong>sipariş numaranızı (<?= e($order['no']) ?>)</strong> yazmanız yeterlidir. Ödemeniz onaylandığında siparişiniz hazırlanmaya başlar.</p>
                <?php foreach ($bankAccounts as $acc): ?>
                    <div class="iban-row">
                        <div class="iban-meta">
                            <?php if (($acc['bank'] ?? '') !== ''): ?><span class="iban-bank"><?= e($acc['bank']) ?></span><?php endif; ?>
                            <span class="iban-holder"><?= e($acc['holder'] ?? '') ?></span>
                            <span class="iban-number"><?= e(format_iban($acc['iban'])) ?></span>
                        </div>
                        <button type="button" class="btn btn-ghost btn-copy" data-copy="<?= e(normalize_iban($acc['iban'])) ?>">Kopyala</button>
                    </div>
                <?php endforeach; ?>
                <?php if ($settings['whatsapp'] !== ''): ?>
                    <a class="btn btn-whatsapp" href="https://wa.me/<?= e(preg_replace('/\D+/', '', $settings['whatsapp'])) ?>?text=<?= rawurlencode('Merhaba, ' . $order['no'] . ' numaralı siparişimin ödemesini yaptım. Dekontu iletmek istiyorum.') ?>" target="_blank" rel="noopener">WhatsApp ile Dekont Gönder</a>
                <?php endif; ?>
            <?php else: ?>
                <p class="payment-note">Ödeme bilgileri için sizinle en kısa sürede iletişime geçeceğiz.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="checkout-card">
            <div class="order-head-row">
                <h3>Sipariş Detayı</h3>
                <span class="badge badge-status"><?= e($statuses[$order['status'] ?? 'yeni'] ?? 'Yeni') ?></span>
            </div>
            <table class="order-table">
                <thead><tr><th>Ürün</th><th>Adet</th><th class="ta-right">Tutar</th></tr></thead>
                <tbody>
                <?php foreach ($order['items'] ?? [] as $item): ?>
                    <tr>
                        <td>
                            <?= e($item['name']) ?>
                            <?php if (($item['code'] ?? '') !== ''): ?><br><small class="muted"><?= e($item['code']) ?></small><?php endif; ?>
                        </td>
                        <td><?= (int)$item['qty'] ?></td>
                        <td class="ta-right"><?= e(format_price($item['total'], $settings['currency'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="2" class="ta-right">Toplam</th><th class="ta-right"><?= e(format_price($order['total'] ?? 0, $settings['currency'])) ?></th></tr>
                </tfoot>
            </table>

            <div class="order-info-grid">
                <div>
                    <h4>Teslimat Bilgileri</h4>
                    <p>
                        <strong><?= e($order['customer']['name'] ?? '') ?></strong><br>
                        <?= e($order['customer']['phone'] ?? '') ?><br>
                        <?php if (($order['customer']['email'] ?? '') !== ''): ?><?= e($order['customer']['email']) ?><br><?php endif; ?>
                        <?= nl2br(e($order['customer']['address'] ?? '')) ?>
                    </p>
                </div>
                <div>
                    <h4>Sipariş Bilgileri</h4>
                    <p>
                        Sipariş No: <strong><?= e($order['no']) ?></strong><br>
                        Tarih: <?= e(date('d.m.Y H:i', strtotime($order['created_at'] ?? 'now'))) ?><br>
                        Ödeme: <?= e($methodLabel) ?>
                    </p>
                    <?php if (($order['customer']['note'] ?? '') !== ''): ?>
                        <h4>Sipariş Notu</h4>
                        <p><?= nl2br(e($order['customer']['note'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="order-footer-actions">
            <a class="btn btn-ghost" href="index.php">Alışverişe Devam Et</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
