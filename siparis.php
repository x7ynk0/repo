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
            <?php if (($order['status'] ?? 'odeme-bekliyor') === 'odeme-bekliyor'): ?>
                <p class="muted">Siparişiniz, ödemeniz hesabımıza ulaşıp doğrulandıktan sonra onaylanacaktır. Bu sayfayı yer imlerinize ekleyerek sipariş durumunuzu takip edebilirsiniz.</p>
            <?php else: ?>
                <p class="muted">Bu sayfayı yer imlerinize ekleyerek siparişinizin durumunu takip edebilirsiniz.</p>
            <?php endif; ?>
        </div>

        <?php if ($isHavale): ?>
        <div class="checkout-card">
            <h3>Ödeme Talimatları — <?= e($methodLabel) ?></h3>
            <?php if (!empty($bankAccounts)): ?>
                <?php $anyDescRequired = (bool)array_filter($bankAccounts, fn($a) => !empty($a['desc_required'])); ?>
                <p class="payment-note">Sipariş tutarını aşağıdaki hesaba gönderin ve ödeme açıklamasına <strong>sipariş numaranızı (<?= e($order['no']) ?>)</strong> yazın. Ödemeniz hesabımıza ulaşıp doğrulandığında siparişiniz onaylanır ve hazırlanmaya başlar.</p>
                <?php if ($anyDescRequired): ?>
                    <div class="notice notice-warning">
                        <strong>Önemli:</strong> "Açıklama Zorunlu" işaretli hesaplara yapacağınız ödemelerde, açıklama alanına sipariş numaranızı (<strong><?= e($order['no']) ?></strong>) yazmanız <strong>zorunludur</strong>. Açıklaması boş bırakılan veya hatalı yazılan ödemeler <strong>iade edilir</strong> ve siparişiniz işleme alınmaz.
                    </div>
                <?php endif; ?>
                <?php foreach ($bankAccounts as $acc): ?>
                    <div class="iban-row">
                        <div class="iban-meta">
                            <span class="iban-bank-line">
                                <?php if (($acc['bank'] ?? '') !== ''): ?><span class="iban-bank"><?= e($acc['bank']) ?></span><?php endif; ?>
                                <?php if (!empty($acc['desc_required'])): ?><span class="badge badge-warning">Açıklama Zorunlu</span><?php endif; ?>
                            </span>
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
                <span class="badge badge-status"><?= e(order_status_label($order['status'] ?? null)) ?></span>
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
