<?php
/**
 * Yedekleme: tüm JSON verisini dışa/içe aktarma.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$action = (string) input('a', '', $_GET);

/* ---------------- Dışa aktarma ---------------- */
if ($action === 'export') {
    if (!csrf_verify()) {
        flash('error', 'Güvenlik doğrulaması başarısız.');
        redirect(admin_url('?p=yedek'));
    }

    $data = Store::exportAll();
    unset($data['users']); // Parola özetleri yedekte taşınmaz

    $json = json_encode([
        'meta' => [
            'app'        => APP_NAME,
            'version'    => APP_VERSION,
            'exported_at'=> date('c'),
            'site'       => setting('site.name', ''),
        ],
        'data' => $data,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    auth_log('backup', 'Veri yedeği indirildi.');

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="oxit-yedek-' . date('Y-m-d-Hi') . '.json"');
    echo $json;
    exit;
}

/* ---------------- İçe aktarma ---------------- */
if (is_post() && $action === 'import') {
    csrf_guard();

    if (empty($_FILES['backup']['name'])) {
        flash('error', 'Lütfen bir yedek dosyası seçin.');
        redirect(admin_url('?p=yedek'));
    }
    if (($_FILES['backup']['error'] ?? 1) !== UPLOAD_ERR_OK) {
        flash('error', 'Dosya yüklenemedi.');
        redirect(admin_url('?p=yedek'));
    }

    $raw     = (string) file_get_contents($_FILES['backup']['tmp_name']);
    $payload = json_decode($raw, true);

    if (!is_array($payload) || !isset($payload['data']) || !is_array($payload['data'])) {
        flash('error', 'Geçersiz yedek dosyası. Bu dosya bu sistem tarafından oluşturulmuş bir yedek değil.');
        redirect(admin_url('?p=yedek'));
    }

    $allowed  = array_keys(seed_definitions());
    $imported = 0;

    foreach ($payload['data'] as $key => $rows) {
        if ($key === 'users' || !in_array($key, $allowed, true) || !is_array($rows)) {
            continue;
        }
        Store::write((string) $key, $rows);
        $imported++;
    }

    Store::flush();
    auth_log('restore', 'Yedekten geri yükleme yapıldı (' . $imported . ' bölüm).');
    flash('success', $imported . ' veri bölümü geri yüklendi.');
    redirect(admin_url('?p=yedek'));
}

/* ---------------- Tek koleksiyon sıfırlama ---------------- */
if ($action === 'reset' && csrf_verify()) {
    $target = (string) input('c', '', $_GET);
    $defs   = seed_definitions();

    if (isset($defs[$target]) && !in_array($target, ['users', 'logs'], true)) {
        Store::write($target, ($defs[$target])());
        Store::flush($target);
        auth_log('reset', $target . ' verisi varsayılana döndürüldü.');
        flash('success', 'Veri varsayılan içeriğe döndürüldü.');
    } else {
        flash('error', 'Bu bölüm sıfırlanamaz.');
    }
    redirect(admin_url('?p=yedek'));
}

/* ---------------- Görünüm ---------------- */
$files = [];
foreach (glob(DATA_PATH . '/*.json') ?: [] as $file) {
    $key = basename($file, '.json');
    $files[] = [
        'key'   => $key,
        'size'  => filesize($file) ?: 0,
        'count' => count(Store::read($key)),
        'time'  => filemtime($file) ?: 0,
    ];
}
usort($files, static fn($a, $b) => strcmp($a['key'], $b['key']));

$totalSize = array_sum(array_column($files, 'size'));

admin_header('Yedekleme', ['subtitle' => 'Verilerinizi indirin, geri yükleyin veya varsayılana döndürün']);
?>

<div class="admin-grid admin-grid--1-1">
    <div class="admin-card">
        <div class="admin-card__head"><h2><?= icon('download', 18) ?> Yedek al</h2></div>
        <p class="text-dim" style="font-size:.92rem">
            Tüm site içeriğinizi (hizmetler, projeler, yazılar, ayarlar, talepler) tek bir JSON dosyası olarak indirin.
            Güvenlik nedeniyle yönetici hesabı bilgileri yedeğe dahil edilmez.
        </p>

        <ul class="info-list mt-6">
            <li><span>Veri dosyası sayısı</span><span class="mono"><?= count($files) ?></span></li>
            <li><span>Toplam boyut</span><span class="mono"><?= number_format($totalSize / 1024, 1, ',', '.') ?> KB</span></li>
            <li><span>Son değişiklik</span><span><?= $files ? e(time_ago(max(array_column($files, 'time')))) : '—' ?></span></li>
        </ul>

        <a class="btn btn--primary btn--block mt-6" href="<?= e(admin_url('?p=yedek&a=export&_token=' . urlencode(csrf_token()))) ?>">
            <?= icon('download', 17) ?><span>Yedeği indir (.json)</span>
        </a>
    </div>

    <div class="admin-card">
        <div class="admin-card__head"><h2><?= icon('upload', 18) ?> Yedeği geri yükle</h2></div>
        <p class="text-dim" style="font-size:.92rem">
            Daha önce indirdiğiniz yedek dosyasını yükleyerek içeriği geri getirin.
        </p>

        <div class="form-note mt-4" style="background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.28)">
            <?= icon('alert', 17) ?>
            <span><strong>Dikkat:</strong> Geri yükleme, yedekteki bölümlerin mevcut içeriğinin üzerine yazar. Bu işlem geri alınamaz.</span>
        </div>

        <form class="mt-6" method="post" enctype="multipart/form-data"
              action="<?= e(admin_url('?p=yedek&a=import')) ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label class="field__label" for="b-file">Yedek dosyası (.json)</label>
                <input class="input" type="file" id="b-file" name="backup" accept="application/json,.json" required>
            </div>
            <button class="btn btn--outline btn--block mt-4" type="submit"
                    data-confirm="Mevcut verilerin üzerine yazılacak. Devam etmek istediğinize emin misiniz?">
                <?= icon('upload', 17) ?><span>Geri yükle</span>
            </button>
        </form>
    </div>
</div>

<div class="admin-card admin-card--flush">
    <div class="admin-card__head" style="padding:20px 22px 0">
        <h2><?= icon('db', 18) ?> Veri dosyaları</h2>
        <span class="text-dim" style="font-size:.82rem">/data klasöründeki JSON dosyaları</span>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Dosya</th>
                    <th>Kayıt</th>
                    <th>Boyut</th>
                    <th>Son değişiklik</th>
                    <th style="text-align:right">İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($files as $f): ?>
                <tr>
                    <td><span class="mono"><?= e($f['key']) ?>.json</span></td>
                    <td><span class="mono"><?= (int) $f['count'] ?></span></td>
                    <td><span class="text-dim"><?= number_format($f['size'] / 1024, 1, ',', '.') ?> KB</span></td>
                    <td><span class="text-dim" style="font-size:.82rem"><?= e(time_ago($f['time'])) ?></span></td>
                    <td class="admin-table__actions">
                        <?php if (!in_array($f['key'], ['users', 'logs', 'messages', 'stats'], true)): ?>
                            <a class="admin-table__action admin-table__action--danger"
                               href="<?= e(admin_url('?p=yedek&a=reset&c=' . urlencode($f['key']) . '&_token=' . urlencode(csrf_token()))) ?>"
                               data-confirm="Bu bölüm varsayılan örnek içeriğe döndürülecek. Mevcut kayıtlar silinecek. Emin misiniz?"
                               title="Varsayılana döndür"><?= icon('refresh', 16) ?></a>
                        <?php else: ?>
                            <span class="text-dim" style="font-size:.76rem">korumalı</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card__head"><h2><?= icon('bulb', 18) ?> Yedekleme önerileri</h2></div>
    <ul class="check-list">
        <li><?= icon('check', 17) ?> Büyük içerik değişikliklerinden <strong>önce</strong> yedek alın.</li>
        <li><?= icon('check', 17) ?> Ayda en az bir kez yedek indirip kendi bilgisayarınızda saklayın.</li>
        <li><?= icon('check', 17) ?> Yüklenen görseller <code>/uploads</code> klasöründedir; onları FTP ile ayrıca yedekleyin.</li>
        <li><?= icon('check', 17) ?> Yedek dosyasında müşteri iletişim bilgileri bulunur; güvenli bir yerde saklayın.</li>
    </ul>
</div>

<?php admin_footer(); ?>
