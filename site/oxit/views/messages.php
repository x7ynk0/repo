<?php
/**
 * Gelen talepler (iletişim formu mesajları).
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

$baseUrl = admin_url('?p=mesajlar');
$action  = (string) input('a', '', $_GET);
$id      = (string) input('id', '', $_GET);

/* ---------------- İşlemler ---------------- */
if ($action !== '' && $action !== 'view') {
    if (!csrf_verify()) {
        flash('error', 'Güvenlik doğrulaması başarısız.');
        redirect($baseUrl);
    }

    switch ($action) {
        case 'status':
            $status = (string) input('s', 'read', $_GET);
            if (in_array($status, ['new', 'read', 'replied', 'archived'], true)) {
                Store::update('messages', $id, ['status' => $status]);
                flash('success', 'Talep durumu güncellendi.');
            }
            break;

        case 'star':
            $m = Store::find('messages', $id);
            if ($m) {
                Store::update('messages', $id, ['starred' => empty($m['starred'])]);
            }
            break;

        case 'note':
            if (is_post()) {
                Store::update('messages', $id, ['note' => (string) input('note')]);
                flash('success', 'Not kaydedildi.');
            }
            redirect($baseUrl . '&id=' . urlencode($id));

        case 'delete':
            Store::delete('messages', $id);
            auth_log('delete', 'İletişim talebi silindi: ' . $id);
            flash('success', 'Talep silindi.');
            redirect($baseUrl);

        case 'read-all':
            Store::mutate('messages', static function (array $rows): array {
                foreach ($rows as $i => $r) {
                    if (($r['status'] ?? '') === 'new') {
                        $rows[$i]['status'] = 'read';
                    }
                }
                return $rows;
            });
            flash('success', 'Tüm talepler okundu olarak işaretlendi.');
            redirect($baseUrl);

        case 'export':
            $rows = Store::all('messages', ['sort' => 'created_at', 'dir' => 'desc']);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="talepler-' . date('Y-m-d') . '.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // Excel için BOM
            fputcsv($out, ['Tarih', 'Ad Soyad', 'E-posta', 'Telefon', 'Şirket', 'Konu', 'Hizmetler', 'Bütçe', 'Termin', 'Durum', 'Mesaj'], ';');
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['created_at'] ?? '', $r['name'] ?? '', $r['email'] ?? '', $r['phone'] ?? '',
                    $r['company'] ?? '', $r['subject'] ?? '', implode(', ', (array) ($r['services'] ?? [])),
                    $r['budget'] ?? '', $r['deadline'] ?? '', $r['status'] ?? '', $r['message'] ?? '',
                ], ';');
            }
            fclose($out);
            exit;
    }

    redirect($baseUrl . ($id !== '' && $action === 'star' ? '' : ''));
}

/* ---------------- Detay görünümü ---------------- */
if ($id !== '') {
    $msg = Store::find('messages', $id);
    if ($msg === null) {
        flash('error', 'Talep bulunamadı.');
        redirect($baseUrl);
    }

    if (($msg['status'] ?? '') === 'new') {
        Store::update('messages', $id, ['status' => 'read']);
        $msg['status'] = 'read';
    }

    admin_header('Talep detayı', ['subtitle' => $msg['subject'] ?? '']);
    ?>
    <a class="btn btn--ghost btn--sm mb-4" href="<?= e($baseUrl) ?>"><?= icon('arrow-left', 15) ?><span>Tüm talepler</span></a>

    <div class="admin-grid admin-grid--2-1">
        <div class="admin-card">
            <div class="admin-card__head">
                <div class="flex gap-4" style="align-items:center">
                    <span class="avatar"><?= e(initials((string) ($msg['name'] ?? '?'))) ?></span>
                    <div>
                        <h2 style="margin:0"><?= e($msg['name'] ?? '') ?></h2>
                        <span class="text-dim" style="font-size:.85rem">
                            <?= e(tr_date($msg['created_at'] ?? '', true)) ?> · <?= e(time_ago($msg['created_at'] ?? '')) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="message-body">
                <h3 style="font-size:1.1rem;margin-bottom:12px"><?= e($msg['subject'] ?? 'Konu belirtilmemiş') ?></h3>
                <p style="white-space:pre-wrap;line-height:1.8"><?= e($msg['message'] ?? '') ?></p>
            </div>

            <?php if (!empty($msg['services'])): ?>
                <div class="mt-6">
                    <span class="footer-title">İlgilenilen hizmetler</span>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <?php foreach ((array) $msg['services'] as $s): ?>
                            <span class="tag"><?= e($s) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form class="mt-7" method="post" action="<?= e($baseUrl . '&a=note&id=' . urlencode($id) . '&_token=' . urlencode(csrf_token())) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label class="field__label" for="m-note">Dahili not</label>
                    <textarea class="textarea" id="m-note" name="note" rows="4"
                              placeholder="Bu talep hakkında not alın (yalnızca siz görürsünüz)"><?= e($msg['note'] ?? '') ?></textarea>
                </div>
                <button class="btn btn--primary btn--sm mt-4" type="submit"><?= icon('check', 15) ?><span>Notu kaydet</span></button>
            </form>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card__head"><h2><?= icon('user', 18) ?> İletişim bilgileri</h2></div>
                <ul class="info-list">
                    <li><span>E-posta</span><span><a href="mailto:<?= e($msg['email'] ?? '') ?>" style="color:var(--accent)"><?= e($msg['email'] ?? '') ?></a></span></li>
                    <?php if (!empty($msg['phone'])): ?>
                        <li><span>Telefon</span><span><a href="tel:<?= e($msg['phone']) ?>" style="color:var(--accent)"><?= e($msg['phone']) ?></a></span></li>
                    <?php endif; ?>
                    <?php if (!empty($msg['company'])): ?>
                        <li><span>Şirket</span><span><?= e($msg['company']) ?></span></li>
                    <?php endif; ?>
                    <li><span>Bütçe</span><span><?= e($msg['budget'] ?? '—') ?></span></li>
                    <li><span>Termin</span><span><?= e($msg['deadline'] ?: '—') ?></span></li>
                </ul>

                <a class="btn btn--primary btn--block mt-6"
                   href="mailto:<?= e($msg['email'] ?? '') ?>?subject=<?= rawurlencode('Re: ' . ($msg['subject'] ?? 'Talebiniz')) ?>">
                    <?= icon('mail', 16) ?><span>E-posta ile yanıtla</span>
                </a>
                <?php if (!empty($msg['phone'])): ?>
                    <a class="btn btn--ghost btn--block mt-4" href="https://wa.me/<?= e(preg_replace('/\D+/', '', (string) $msg['phone'])) ?>" target="_blank" rel="noopener">
                        <?= icon('headset', 16) ?><span>WhatsApp'tan yaz</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="admin-card mt-4">
                <div class="admin-card__head"><h2><?= icon('settings', 18) ?> Durum</h2></div>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $statuses = ['new' => 'Yeni', 'read' => 'Okundu', 'replied' => 'Yanıtlandı', 'archived' => 'Arşiv'];
                    foreach ($statuses as $key => $label): ?>
                        <a class="filter-btn<?= ($msg['status'] ?? '') === $key ? ' is-active' : '' ?>"
                           href="<?= e($baseUrl . '&a=status&s=' . $key . '&id=' . urlencode($id) . '&_token=' . urlencode(csrf_token())) ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <a class="btn btn--outline btn--block mt-6" style="color:#f87171;border-color:rgba(248,113,113,.4)"
                   href="<?= e($baseUrl . '&a=delete&id=' . urlencode($id) . '&_token=' . urlencode(csrf_token())) ?>"
                   data-confirm="Bu talebi kalıcı olarak silmek istediğinize emin misiniz?">
                    <?= icon('trash', 16) ?><span>Talebi sil</span>
                </a>
            </div>

            <div class="admin-card mt-4">
                <div class="admin-card__head"><h2><?= icon('info', 18) ?> Teknik bilgi</h2></div>
                <ul class="info-list" style="font-size:.82rem">
                    <li><span>Kaynak sayfa</span><span><?= e(str_limit((string) ($msg['source'] ?? '—'), 30)) ?></span></li>
                    <li><span>Ziyaretçi kodu</span><span class="mono"><?= e(str_limit((string) ($msg['ip'] ?? '—'), 16)) ?></span></li>
                    <li><span>Tarayıcı</span><span><?= e(str_limit((string) ($msg['agent'] ?? '—'), 28)) ?></span></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
    admin_footer();
    exit;
}

/* ---------------- Liste görünümü ---------------- */
$filter = (string) input('durum', '', $_GET);
$q      = trim((string) input('q', '', $_GET));

$rows = Store::all('messages', ['sort' => 'created_at', 'dir' => 'desc']);

if ($filter !== '') {
    $rows = array_values(array_filter($rows, static fn($m) => ($m['status'] ?? '') === $filter));
}
if ($q !== '') {
    $needle = mb_strtolower($q);
    $rows = array_values(array_filter($rows, static function (array $m) use ($needle): bool {
        return str_contains(mb_strtolower(($m['name'] ?? '') . ' ' . ($m['email'] ?? '') . ' ' . ($m['subject'] ?? '') . ' ' . ($m['message'] ?? '') . ' ' . ($m['company'] ?? '')), $needle);
    }));
}

$pageNo = max(1, input_int('sayfa', 1));
$paged  = paginate($rows, PER_PAGE_ADMIN, $pageNo);

$all = Store::read('messages');
$countBy = static function (string $status) use ($all): int {
    return count(array_filter($all, static fn($m) => ($m['status'] ?? '') === $status));
};

admin_header('Gelen Talepler', ['subtitle' => 'İletişim formundan gelen teklif ve bilgi talepleri']);
?>

<div class="admin-toolbar-row">
    <div class="filter-bar" style="margin:0">
        <a class="filter-btn<?= $filter === '' ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>">
            Tümü <span class="mono" style="opacity:.6">(<?= count($all) ?>)</span>
        </a>
        <?php foreach (['new' => 'Yeni', 'read' => 'Okundu', 'replied' => 'Yanıtlandı', 'archived' => 'Arşiv'] as $key => $label): ?>
            <a class="filter-btn<?= $filter === $key ? ' is-active' : '' ?>" href="<?= e($baseUrl . '&durum=' . $key) ?>">
                <?= e($label) ?> <span class="mono" style="opacity:.6">(<?= $countBy($key) ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="flex gap-2">
        <a class="btn btn--ghost btn--sm" href="<?= e($baseUrl . '&a=read-all&_token=' . urlencode(csrf_token())) ?>">
            <?= icon('check', 15) ?><span>Tümünü okundu yap</span>
        </a>
        <a class="btn btn--ghost btn--sm" href="<?= e($baseUrl . '&a=export&_token=' . urlencode(csrf_token())) ?>">
            <?= icon('download', 15) ?><span>CSV indir</span>
        </a>
    </div>
</div>

<div class="admin-toolbar-row">
    <form class="admin-search" method="get" action="<?= e(admin_url()) ?>">
        <input type="hidden" name="p" value="mesajlar">
        <?php if ($filter !== ''): ?><input type="hidden" name="durum" value="<?= e($filter) ?>"><?php endif; ?>
        <span class="admin-search__ico"><?= icon('search', 17) ?></span>
        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Ad, e-posta, konu veya mesaj içinde ara…">
    </form>
    <span class="admin-count"><?= $paged['total'] ?> talep</span>
</div>

<div class="admin-card admin-card--flush">
    <?php if ($paged['items']): ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:44px"></th>
                        <th>Gönderen</th>
                        <th>Konu</th>
                        <th>Bütçe</th>
                        <th>Tarih</th>
                        <th>Durum</th>
                        <th style="text-align:right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($paged['items'] as $m): ?>
                    <tr class="<?= ($m['status'] ?? '') === 'new' ? 'is-unread' : '' ?>">
                        <td><span class="avatar avatar--sm"><?= e(initials((string) ($m['name'] ?? '?'))) ?></span></td>
                        <td>
                            <a class="admin-table__title" href="<?= e($baseUrl . '&id=' . urlencode((string) $m['id'])) ?>">
                                <?= e($m['name'] ?? '') ?>
                            </a>
                            <span class="admin-table__slug"><?= e($m['email'] ?? '') ?></span>
                        </td>
                        <td>
                            <span><?= e(str_limit((string) ($m['subject'] ?? ''), 40)) ?></span>
                            <span class="admin-table__slug"><?= e(str_limit((string) ($m['message'] ?? ''), 52)) ?></span>
                        </td>
                        <td><span class="text-dim" style="font-size:.82rem"><?= e($m['budget'] ?? '—') ?></span></td>
                        <td><span class="text-dim" style="font-size:.82rem"><?= e(time_ago($m['created_at'] ?? '')) ?></span></td>
                        <td>
                            <?php
                            $statusMap = [
                                'new'      => ['Yeni', 'badge--warn'],
                                'read'     => ['Okundu', ''],
                                'replied'  => ['Yanıtlandı', 'badge--success'],
                                'archived' => ['Arşiv', ''],
                            ];
                            $st = $statusMap[$m['status'] ?? 'read'] ?? ['—', ''];
                            ?>
                            <span class="badge <?= e($st[1]) ?>"><?= e($st[0]) ?></span>
                        </td>
                        <td class="admin-table__actions">
                            <a class="admin-table__action" href="<?= e($baseUrl . '&id=' . urlencode((string) $m['id'])) ?>" title="Görüntüle"><?= icon('eye', 16) ?></a>
                            <a class="admin-table__action" href="mailto:<?= e($m['email'] ?? '') ?>" title="Yanıtla"><?= icon('mail', 16) ?></a>
                            <?= admin_delete_link($baseUrl . '&a=delete&id=' . urlencode((string) $m['id']) . '&_token=' . urlencode(csrf_token())) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= pagination_html($paged, $baseUrl . ($filter !== '' ? '&durum=' . $filter : '') . ($q !== '' ? '&q=' . urlencode($q) : '')) ?>
    <?php else: ?>
        <div class="empty-state" style="border:0">
            <?= icon('inbox', 46) ?>
            <h3>Talep bulunamadı</h3>
            <p>Bu filtreye uyan bir talep yok. Site iletişim formundan gelen mesajlar burada listelenir.</p>
        </div>
    <?php endif; ?>
</div>

<?php admin_footer(); ?>
