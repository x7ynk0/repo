<?php
/**
 * Kontrol paneli (dashboard).
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$stats       = Store::read('stats');
$messages    = Store::all('messages', ['sort' => 'created_at', 'dir' => 'desc']);
$newMessages = array_values(array_filter($messages, static fn($m) => ($m['status'] ?? '') === 'new'));
$logs        = array_slice(array_reverse(Store::read('logs')), 0, 8);

$daily = (array) ($stats['daily'] ?? []);
$last14 = array_slice($daily, -14, null, true);
$maxVisit = $last14 ? max(array_map('intval', $last14)) : 1;

$counts = [];
foreach (admin_resources() as $key => $res) {
    $counts[$key] = [
        'label'  => $res['label'],
        'icon'   => $res['icon'],
        'total'  => Store::count($key),
        'active' => Store::count($key, static fn($r) => !empty($r['active'])),
    ];
}

$totalVisits = (int) ($stats['total_visits'] ?? 0);
$todayVisits = (int) ($daily[date('Y-m-d')] ?? 0);

admin_header('Kontrol Paneli', ['subtitle' => 'Sitenizin genel durumu ve son hareketler']);
?>

<!-- ÖZET KARTLARI -->
<div class="admin-stats">
    <div class="admin-stat">
        <span class="admin-stat__ico" style="background:color-mix(in srgb,var(--accent) 15%,transparent);color:var(--accent)"><?= icon('eye', 20) ?></span>
        <div>
            <strong><?= e(human_number($totalVisits)) ?></strong>
            <span>Toplam ziyaret</span>
        </div>
    </div>

    <div class="admin-stat">
        <span class="admin-stat__ico" style="background:rgba(34,197,94,.14);color:#4ade80"><?= icon('trend', 20) ?></span>
        <div>
            <strong><?= e(human_number($todayVisits)) ?></strong>
            <span>Bugünkü ziyaret</span>
        </div>
    </div>

    <div class="admin-stat">
        <span class="admin-stat__ico" style="background:rgba(245,158,11,.14);color:#fbbf24"><?= icon('inbox', 20) ?></span>
        <div>
            <strong><?= count($newMessages) ?></strong>
            <span>Okunmamış talep</span>
        </div>
    </div>

    <div class="admin-stat">
        <span class="admin-stat__ico" style="background:color-mix(in srgb,var(--accent-2) 15%,transparent);color:var(--accent-2)"><?= icon('folder', 20) ?></span>
        <div>
            <strong><?= Store::count('projects') ?></strong>
            <span>Referans proje</span>
        </div>
    </div>
</div>

<div class="admin-grid admin-grid--2-1">

    <!-- ZİYARET GRAFİĞİ -->
    <div class="admin-card">
        <div class="admin-card__head">
            <h2><?= icon('chart', 18) ?> Son 14 gün</h2>
            <span class="text-dim" style="font-size:.82rem">Günlük ziyaret sayısı</span>
        </div>

        <?php if ($last14): ?>
            <div class="admin-chart">
                <?php foreach ($last14 as $day => $count): ?>
                    <div class="admin-chart__col" title="<?= e(tr_date($day)) ?>: <?= (int) $count ?> ziyaret">
                        <span class="admin-chart__bar" style="height:<?= max(4, (int) round(((int) $count / max(1, $maxVisit)) * 100)) ?>%"></span>
                        <span class="admin-chart__label"><?= e(date('d.m', strtotime((string) $day))) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state" style="border:0;padding:40px 20px">
                <?= icon('chart', 40) ?>
                <p>Henüz ziyaret verisi yok. Site ziyaret edildikçe burada grafik oluşacak.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- HIZLI İŞLEMLER -->
    <div class="admin-card">
        <div class="admin-card__head">
            <h2><?= icon('zap', 18) ?> Hızlı işlemler</h2>
        </div>

        <div class="quick-actions">
            <a class="quick-action" href="<?= e(admin_url('?p=kaynak&r=projects&a=form')) ?>">
                <?= icon('plus', 17) ?><span>Yeni referans ekle</span>
            </a>
            <a class="quick-action" href="<?= e(admin_url('?p=kaynak&r=posts&a=form')) ?>">
                <?= icon('pen', 17) ?><span>Blog yazısı yaz</span>
            </a>
            <a class="quick-action" href="<?= e(admin_url('?p=kaynak&r=services&a=form')) ?>">
                <?= icon('layers', 17) ?><span>Hizmet ekle</span>
            </a>
            <a class="quick-action" href="<?= e(admin_url('?p=kaynak&r=testimonials&a=form')) ?>">
                <?= icon('quote', 17) ?><span>Müşteri görüşü ekle</span>
            </a>
            <a class="quick-action" href="<?= e(admin_url('?p=ayarlar')) ?>">
                <?= icon('settings', 17) ?><span>Site ayarları</span>
            </a>
            <a class="quick-action" href="<?= e(admin_url('?p=yedek')) ?>">
                <?= icon('download', 17) ?><span>Yedek al</span>
            </a>
        </div>
    </div>
</div>

<div class="admin-grid admin-grid--2-1">

    <!-- SON TALEPLER -->
    <div class="admin-card admin-card--flush">
        <div class="admin-card__head" style="padding:20px 22px 0">
            <h2><?= icon('inbox', 18) ?> Son gelen talepler</h2>
            <a class="btn btn--ghost btn--sm" href="<?= e(admin_url('?p=mesajlar')) ?>">
                <span>Tümü</span><?= icon('arrow-right', 15) ?>
            </a>
        </div>

        <?php $recent = array_slice($messages, 0, 6); ?>
        <?php if ($recent): ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <tbody>
                    <?php foreach ($recent as $m): ?>
                        <tr>
                            <td style="width:44px">
                                <span class="avatar avatar--sm"><?= e(initials((string) ($m['name'] ?? '?'))) ?></span>
                            </td>
                            <td>
                                <a class="admin-table__title" href="<?= e(admin_url('?p=mesajlar&id=' . urlencode((string) $m['id']))) ?>">
                                    <?= e($m['name'] ?? '') ?>
                                </a>
                                <span class="admin-table__slug"><?= e(str_limit((string) ($m['subject'] ?? ''), 48)) ?></span>
                            </td>
                            <td style="text-align:right">
                                <?php if (($m['status'] ?? '') === 'new'): ?>
                                    <span class="badge badge--warn">Yeni</span>
                                <?php endif; ?>
                                <span class="text-dim" style="font-size:.78rem;display:block;margin-top:4px"><?= e(time_ago($m['created_at'] ?? '')) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="border:0;padding:40px 20px">
                <?= icon('inbox', 40) ?>
                <p>Henüz gelen talep yok.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- SİSTEM KAYDI -->
    <div class="admin-card">
        <div class="admin-card__head">
            <h2><?= icon('file', 18) ?> Son hareketler</h2>
            <a class="btn btn--ghost btn--sm" href="<?= e(admin_url('?p=kayitlar')) ?>"><span>Tümü</span></a>
        </div>

        <ul class="activity">
            <?php foreach ($logs as $log): ?>
                <li class="activity__item">
                    <span class="activity__dot" data-type="<?= e($log['type'] ?? '') ?>"></span>
                    <div>
                        <p><?= e($log['message'] ?? '') ?></p>
                        <span><?= e(time_ago($log['at'] ?? '')) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
            <?php if (!$logs): ?>
                <li class="text-dim" style="font-size:.88rem">Henüz kayıt yok.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- İÇERİK ÖZETİ -->
<div class="admin-card">
    <div class="admin-card__head">
        <h2><?= icon('grid', 18) ?> İçerik özeti</h2>
        <span class="text-dim" style="font-size:.82rem">Yayında olan / toplam kayıt</span>
    </div>

    <div class="content-summary">
        <?php foreach ($counts as $key => $c): ?>
            <a class="content-summary__item" href="<?= e(admin_url('?p=kaynak&r=' . urlencode($key))) ?>">
                <span class="content-summary__ico"><?= icon($c['icon'], 19) ?></span>
                <div>
                    <strong><?= (int) $c['active'] ?><span class="text-dim">/<?= (int) $c['total'] ?></span></strong>
                    <span><?= e($c['label']) ?></span>
                </div>
                <?= icon('chevron-right', 16) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- KURULUM İPUÇLARI -->
<?php
$tips = [];
if (setting('contact.email', '') === 'merhaba@oxitstudio.com') {
    $tips[] = ['İletişim e-postanızı güncelleyin', 'Ayarlar → İletişim bölümünden kendi e-posta adresinizi girin.', admin_url('?p=ayarlar#iletisim')];
}
if (setting('site.name', '') === 'Oxit Studio') {
    $tips[] = ['Site adınızı değiştirin', 'Ayarlar → Genel bölümünden marka adınızı yazın.', admin_url('?p=ayarlar')];
}
if (!setting('site.logo', '')) {
    $tips[] = ['Logo yükleyin', 'Kendi logonuzu yükleyerek markanızı güçlendirin.', admin_url('?p=ayarlar')];
}
if (!setting('seo.ga_id', '')) {
    $tips[] = ['Analitik kurun', 'Google Analytics kimliğinizi ekleyerek ziyaretçi davranışını ölçün.', admin_url('?p=ayarlar#seo')];
}
?>
<?php if ($tips): ?>
<div class="admin-card">
    <div class="admin-card__head">
        <h2><?= icon('bulb', 18) ?> Önerilen adımlar</h2>
    </div>
    <div class="tips">
        <?php foreach ($tips as $t): ?>
            <a class="tip" href="<?= e($t[2]) ?>">
                <span class="tip__ico"><?= icon('arrow-right', 16) ?></span>
                <div>
                    <strong><?= e($t[0]) ?></strong>
                    <span><?= e($t[1]) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php admin_footer(); ?>
