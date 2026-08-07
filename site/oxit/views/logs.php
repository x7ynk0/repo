<?php
/**
 * Sistem kayıtları (log).
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$baseUrl = admin_url('?p=kayitlar');

if ((string) input('a', '', $_GET) === 'clear') {
    if (!csrf_verify()) {
        flash('error', 'Güvenlik doğrulaması başarısız.');
    } else {
        Store::write('logs', []);
        Store::flush('logs');
        auth_log('clear', 'Sistem kayıtları temizlendi.');
        flash('success', 'Kayıtlar temizlendi.');
    }
    redirect($baseUrl);
}

$types = [
    'login'      => ['Giriş', 'badge--success'],
    'login_fail' => ['Başarısız giriş', 'badge--danger'],
    'logout'     => ['Çıkış', ''],
    'install'    => ['Kurulum', 'badge--accent'],
    'create'     => ['Ekleme', 'badge--success'],
    'update'     => ['Güncelleme', 'badge--accent'],
    'delete'     => ['Silme', 'badge--danger'],
    'settings'   => ['Ayarlar', 'badge--accent'],
    'password'   => ['Parola', 'badge--warn'],
    'message'    => ['Yeni talep', 'badge--warn'],
    'backup'     => ['Yedek', ''],
    'restore'    => ['Geri yükleme', 'badge--warn'],
    'reset'      => ['Sıfırlama', 'badge--danger'],
    'profile'    => ['Profil', ''],
    'clear'      => ['Temizleme', ''],
];

$filter = (string) input('tur', '', $_GET);
$rows   = array_reverse(Store::read('logs'));

if ($filter !== '') {
    $rows = array_values(array_filter($rows, static fn($l) => ($l['type'] ?? '') === $filter));
}

$pageNo = max(1, input_int('sayfa', 1));
$paged  = paginate($rows, 40, $pageNo);

admin_header('Sistem Kaydı', ['subtitle' => 'Panelde yapılan işlemler ve giriş denemeleri (son 500 kayıt)']);
?>

<div class="admin-toolbar-row">
    <div class="filter-bar" style="margin:0">
        <a class="filter-btn<?= $filter === '' ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>">Tümü</a>
        <?php foreach (['login_fail' => 'Başarısız giriş', 'create' => 'Ekleme', 'update' => 'Güncelleme', 'delete' => 'Silme', 'message' => 'Talep'] as $key => $label): ?>
            <a class="filter-btn<?= $filter === $key ? ' is-active' : '' ?>" href="<?= e($baseUrl . '&tur=' . $key) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>

    <a class="btn btn--ghost btn--sm" href="<?= e($baseUrl . '&a=clear&_token=' . urlencode(csrf_token())) ?>"
       data-confirm="Tüm sistem kayıtları silinecek. Emin misiniz?">
        <?= icon('trash', 15) ?><span>Kayıtları temizle</span>
    </a>
</div>

<div class="admin-card admin-card--flush">
    <?php if ($paged['items']): ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tür</th>
                        <th>Açıklama</th>
                        <th>Ziyaretçi kodu</th>
                        <th>Zaman</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($paged['items'] as $log): ?>
                    <?php $t = $types[$log['type'] ?? ''] ?? [($log['type'] ?? '—'), '']; ?>
                    <tr>
                        <td><span class="badge <?= e($t[1]) ?>"><?= e($t[0]) ?></span></td>
                        <td><?= e($log['message'] ?? '') ?></td>
                        <td><span class="mono text-dim" style="font-size:.76rem"><?= e(str_limit((string) ($log['ip'] ?? ''), 14)) ?></span></td>
                        <td>
                            <span class="text-dim" style="font-size:.82rem"><?= e(tr_date($log['at'] ?? '', true)) ?></span>
                            <span class="admin-table__slug"><?= e(time_ago($log['at'] ?? '')) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= pagination_html($paged, $baseUrl . ($filter !== '' ? '&tur=' . $filter : '')) ?>
    <?php else: ?>
        <div class="empty-state" style="border:0">
            <?= icon('file', 46) ?>
            <h3>Kayıt bulunamadı</h3>
            <p>Panelde işlem yaptıkça buraya kayıtlar düşecek.</p>
        </div>
    <?php endif; ?>
</div>

<?php admin_footer(); ?>
