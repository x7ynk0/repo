<?php
/**
 * Genel amaçlı CRUD motoru.
 *
 * resources.php içindeki tanımlara göre liste, form, kaydetme,
 * silme, kopyalama ve durum değiştirme işlemlerini üretir.
 */

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    exit('Doğrudan erişim engellendi.');
}

/**
 * Bir kaynak için POST verisinden kayıt dizisi oluşturur.
 */
function crud_extract(array $res, array $existing = []): array
{
    $row = [];

    foreach ((array) $res['fields'] as $name => $def) {
        $type = (string) ($def['type'] ?? 'text');

        switch ($type) {

            case 'bool':
                $row[$name] = input_bool($name);
                break;

            case 'number':
            case 'range':
                $row[$name] = input_int($name, (int) ($def['default'] ?? 0));
                if (isset($def['min'])) $row[$name] = max((int) $def['min'], $row[$name]);
                if (isset($def['max'])) $row[$name] = min((int) $def['max'], $row[$name]);
                break;

            case 'price':
                $raw = str_replace([',', ' ', '.'], '', (string) input($name, '0'));
                $row[$name] = (float) $raw;
                break;

            case 'list':
                $lines = preg_split('/\r\n|\r|\n/', (string) input($name, '')) ?: [];
                $row[$name] = array_values(array_filter(array_map('trim', $lines), static fn($v) => $v !== ''));
                break;

            case 'pairs':
                $keys = (array) ($def['pair_keys'] ?? ['label' => 'Etiket', 'value' => 'Değer']);
                $k1 = array_keys($keys)[0];
                $k2 = array_keys($keys)[1];
                $a1 = (array) ($_POST[$name . '_' . $k1] ?? []);
                $a2 = (array) ($_POST[$name . '_' . $k2] ?? []);
                $pairs = [];
                foreach ($a1 as $i => $v1) {
                    $v1 = trim((string) $v1);
                    $v2 = trim((string) ($a2[$i] ?? ''));
                    if ($v1 === '' && $v2 === '') continue;
                    $pairs[] = [$k1 => $v1, $k2 => $v2];
                }
                $row[$name] = $pairs;
                break;

            case 'image':
                $current = (string) input($name, '');
                $folder  = (string) input($name . '__folder', 'misc');

                if (input_bool($name . '__delete')) {
                    delete_upload((string) ($existing[$name] ?? ''));
                    $current = '';
                }

                if (!empty($_FILES[$name . '__file']['name'])) {
                    $up = upload_image($name . '__file', $folder);
                    if ($up['ok']) {
                        if (!empty($existing[$name])) {
                            delete_upload((string) $existing[$name]);
                        }
                        $current = (string) $up['path'];
                    } else {
                        flash('error', $def['label'] . ': ' . $up['error']);
                    }
                }

                $row[$name] = $current;
                break;

            case 'gallery':
                $keep   = array_values(array_filter((array) ($_POST[$name] ?? [])));
                $remove = array_map('intval', (array) ($_POST[$name . '__remove'] ?? []));
                foreach ($remove as $idx) {
                    if (isset($keep[$idx])) {
                        delete_upload((string) $keep[$idx]);
                        unset($keep[$idx]);
                    }
                }
                $keep   = array_values($keep);
                $folder = (string) input($name . '__folder', 'misc');

                if (!empty($_FILES[$name . '__files']['name'][0])) {
                    $files = $_FILES[$name . '__files'];
                    $count = count((array) $files['name']);
                    for ($i = 0; $i < $count; $i++) {
                        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
                        $_FILES['__tmp_gallery'] = [
                            'name'     => $files['name'][$i],
                            'type'     => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error'    => $files['error'][$i],
                            'size'     => $files['size'][$i],
                        ];
                        $up = upload_image('__tmp_gallery', $folder);
                        if ($up['ok']) {
                            $keep[] = $up['path'];
                        } else {
                            flash('error', 'Galeri: ' . $up['error']);
                        }
                    }
                    unset($_FILES['__tmp_gallery']);
                }

                $row[$name] = $keep;
                break;

            case 'date':
                $row[$name] = (string) input($name, date('Y-m-d'));
                break;

            default:
                $row[$name] = (string) input($name, '');
        }
    }

    return $row;
}

/** Zorunlu alanları doğrular. */
function crud_validate(array $res, array $row): array
{
    $errors = [];
    foreach ((array) $res['fields'] as $name => $def) {
        if (empty($def['required'])) continue;
        $val = $row[$name] ?? '';
        if (is_array($val) ? count($val) === 0 : trim((string) $val) === '') {
            $errors[$name] = ($def['label'] ?? $name) . ' alanı zorunludur.';
        }
    }
    return $errors;
}

/* =====================================================================
   İSTEK YÖNLENDİRME
   ===================================================================== */

$resKey = (string) input('r', '', $_GET);
$res    = admin_resource($resKey);

if ($res === null) {
    flash('error', 'Geçersiz içerik türü.');
    redirect(admin_url('?p=panel'));
}

$action = (string) input('a', 'list', $_GET);
$id     = (string) input('id', '', $_GET);
$listUrl = admin_url('?p=kaynak&r=' . urlencode($resKey));

/* ---------------------- SİLME ---------------------- */
if ($action === 'delete' && $id !== '') {
    if (!csrf_verify()) {
        flash('error', 'Güvenlik doğrulaması başarısız.');
        redirect($listUrl);
    }

    $rec = Store::find($resKey, $id);
    if ($rec) {
        // Bağlı görselleri temizle
        foreach ((array) $res['fields'] as $fName => $fDef) {
            if (($fDef['type'] ?? '') === 'image') {
                delete_upload((string) ($rec[$fName] ?? ''));
            } elseif (($fDef['type'] ?? '') === 'gallery') {
                foreach ((array) ($rec[$fName] ?? []) as $g) {
                    delete_upload((string) $g);
                }
            }
        }
        Store::delete($resKey, $id);
        auth_log('delete', $res['label_single'] . ' silindi: ' . ($rec['title'] ?? $rec['name'] ?? $rec['q'] ?? $id));
        flash('success', $res['label_single'] . ' silindi.');
    } else {
        flash('error', 'Kayıt bulunamadı.');
    }
    redirect($listUrl);
}

/* ---------------------- DURUM DEĞİŞTİR ---------------------- */
if ($action === 'toggle' && $id !== '') {
    if (!csrf_verify()) {
        flash('error', 'Güvenlik doğrulaması başarısız.');
        redirect($listUrl);
    }
    $field = (string) input('f', 'active', $_GET);
    $rec   = Store::find($resKey, $id);
    if ($rec) {
        Store::update($resKey, $id, [$field => empty($rec[$field])]);
        flash('success', 'Durum güncellendi.');
    }
    redirect($listUrl);
}

/* ---------------------- KOPYALA ---------------------- */
if ($action === 'duplicate' && $id !== '') {
    if (!csrf_verify()) {
        flash('error', 'Güvenlik doğrulaması başarısız.');
        redirect($listUrl);
    }
    $rec = Store::find($resKey, $id);
    if ($rec) {
        unset($rec['id'], $rec['created_at'], $rec['updated_at']);
        foreach (['title', 'name', 'q'] as $k) {
            if (isset($rec[$k])) { $rec[$k] .= ' (kopya)'; break; }
        }
        if (!empty($res['slug_from'])) {
            $rec['slug'] = unique_slug($resKey, (string) ($rec[$res['slug_from']] ?? 'kayit'));
        }
        $rec['active'] = false;
        Store::insert($resKey, $rec);
        flash('success', 'Kayıt kopyalandı. Kopya taslak olarak eklendi.');
    }
    redirect($listUrl);
}

/* ---------------------- KAYDET ---------------------- */
if ($action === 'save' && is_post()) {
    csrf_guard();

    $editId   = (string) input('__id', '');
    $existing = $editId !== '' ? (Store::find($resKey, $editId) ?? []) : [];

    $row    = crud_extract($res, $existing);
    $errors = crud_validate($res, $row);

    if ($errors) {
        foreach ($errors as $msg) {
            flash('error', $msg);
        }
        $_SESSION['_form_old'] = $row;
        redirect(admin_url('?p=kaynak&r=' . urlencode($resKey) . '&a=form' . ($editId !== '' ? '&id=' . urlencode($editId) : '')));
    }

    // Slug üret
    if (!empty($res['slug_from'])) {
        $source = (string) ($row[$res['slug_from']] ?? '');
        $row['slug'] = unique_slug($resKey, $source !== '' ? $source : 'kayit', $editId !== '' ? $editId : null);
    }

    if ($editId !== '' && $existing) {
        Store::update($resKey, $editId, $row);
        auth_log('update', $res['label_single'] . ' güncellendi: ' . ($row['title'] ?? $row['name'] ?? $row['q'] ?? $editId));
        flash('success', $res['label_single'] . ' güncellendi.');
    } else {
        $newId = Store::insert($resKey, $row);
        auth_log('create', $res['label_single'] . ' eklendi: ' . ($row['title'] ?? $row['name'] ?? $row['q'] ?? $newId));
        flash('success', $res['label_single'] . ' eklendi.');
    }

    redirect($listUrl);
}

/* ---------------------- FORM ---------------------- */
if ($action === 'form') {
    $record = $id !== '' ? Store::find($resKey, $id) : null;
    $isEdit = $record !== null;

    $old = $_SESSION['_form_old'] ?? null;
    unset($_SESSION['_form_old']);
    if ($old) {
        $record = array_merge($record ?? [], $old);
    }

    admin_header(
        ($isEdit ? 'Düzenle: ' : 'Yeni ') . $res['label_single'],
        ['subtitle' => $res['desc'] ?? '']
    );
    ?>
    <div class="admin-card">
        <form method="post" enctype="multipart/form-data"
              action="<?= e(admin_url('?p=kaynak&r=' . urlencode($resKey) . '&a=save')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="__id" value="<?= e($isEdit ? (string) $record['id'] : '') ?>">

            <div class="admin-form-grid">
                <?php foreach ((array) $res['fields'] as $fName => $fDef): ?>
                    <?php admin_field($fName, $fDef, $record[$fName] ?? null); ?>
                <?php endforeach; ?>
            </div>

            <div class="admin-form-actions">
                <button class="btn btn--primary" type="submit">
                    <?= icon('check', 17) ?><span><?= $isEdit ? 'Değişiklikleri kaydet' : 'Kaydet' ?></span>
                </button>
                <a class="btn btn--ghost" href="<?= e($listUrl) ?>"><?= icon('arrow-left', 16) ?><span>Vazgeç</span></a>

                <?php if ($isEdit): ?>
                    <a class="btn btn--outline" style="margin-left:auto;color:#f87171;border-color:rgba(248,113,113,.4)"
                       href="<?= e($listUrl . '&a=delete&id=' . urlencode((string) $record['id']) . '&_token=' . urlencode(csrf_token())) ?>"
                       data-confirm="Bu kaydı kalıcı olarak silmek istediğinize emin misiniz?">
                        <?= icon('trash', 16) ?><span>Sil</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php
    admin_footer();
    exit;
}

/* ---------------------- LİSTE ---------------------- */
$q    = trim((string) input('q', '', $_GET));
$rows = Store::all($resKey, ['sort' => $res['sort'] ?? 'order', 'dir' => $res['dir'] ?? 'asc']);

if ($q !== '') {
    $needle = mb_strtolower($q);
    $rows = array_values(array_filter($rows, static function (array $r) use ($needle): bool {
        $hay = mb_strtolower(json_encode($r, JSON_UNESCAPED_UNICODE) ?: '');
        return str_contains($hay, $needle);
    }));
}

$pageNo = max(1, input_int('sayfa', 1));
$paged  = paginate($rows, PER_PAGE_ADMIN, $pageNo);

admin_header($res['label'], [
    'subtitle' => $res['desc'] ?? '',
    'action'   => ['url' => $listUrl . '&a=form', 'label' => 'Yeni ekle', 'icon' => 'plus'],
]);
?>

<div class="admin-toolbar-row">
    <form class="admin-search" method="get" action="<?= e(admin_url()) ?>">
        <input type="hidden" name="p" value="kaynak">
        <input type="hidden" name="r" value="<?= e($resKey) ?>">
        <span class="admin-search__ico"><?= icon('search', 17) ?></span>
        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e($res['label']) ?> içinde ara…">
        <?php if ($q !== ''): ?>
            <a class="admin-search__clear" href="<?= e($listUrl) ?>" aria-label="Temizle"><?= icon('x', 16) ?></a>
        <?php endif; ?>
    </form>
    <span class="admin-count"><?= $paged['total'] ?> kayıt</span>
</div>

<div class="admin-card admin-card--flush">
    <?php if ($paged['items']): ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <?php foreach ((array) $res['columns'] as $col): ?>
                            <th><?= e($col['label']) ?></th>
                        <?php endforeach; ?>
                        <th>Durum</th>
                        <th style="text-align:right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($paged['items'] as $row): ?>
                    <tr>
                        <?php foreach ((array) $res['columns'] as $col):
                            $val = $row[$col['key']] ?? ''; ?>
                            <td data-col="<?= e($col['type']) ?>">
                                <?php switch ($col['type']):
                                    case 'title': ?>
                                        <a class="admin-table__title"
                                           href="<?= e($listUrl . '&a=form&id=' . urlencode((string) $row['id'])) ?>">
                                            <?= e(str_limit((string) $val, 60)) ?>
                                        </a>
                                        <?php if (!empty($row['slug'])): ?>
                                            <span class="admin-table__slug">/<?= e((string) $row['slug']) ?></span>
                                        <?php endif; ?>
                                        <?php break; ?>

                                    <?php case 'thumb': ?>
                                        <span class="admin-thumb">
                                            <?php if ($val): ?>
                                                <img src="<?= e(upload_url((string) $val)) ?>" alt="" loading="lazy">
                                            <?php else: ?>
                                                <?= icon('file', 16) ?>
                                            <?php endif; ?>
                                        </span>
                                        <?php break; ?>

                                    <?php case 'avatar': ?>
                                        <span class="avatar avatar--sm">
                                            <?php if ($val): ?>
                                                <img src="<?= e(upload_url((string) $val)) ?>" alt="">
                                            <?php else: ?>
                                                <?= e(initials((string) ($row['name'] ?? '?'))) ?>
                                            <?php endif; ?>
                                        </span>
                                        <?php break; ?>

                                    <?php case 'icon': ?>
                                        <span class="admin-icon-cell"><?= icon((string) ($val ?: 'code'), 18) ?></span>
                                        <?php break; ?>

                                    <?php case 'badge': ?>
                                        <?php if ($val): ?><span class="badge"><?= e((string) $val) ?></span><?php endif; ?>
                                        <?php break; ?>

                                    <?php case 'price': ?>
                                        <span class="mono"><?= (float) $val > 0 ? e(money($val)) : '—' ?></span>
                                        <?php break; ?>

                                    <?php case 'date': ?>
                                        <span class="text-dim"><?= e(tr_date((string) $val)) ?></span>
                                        <?php break; ?>

                                    <?php case 'number': ?>
                                        <span class="mono"><?= (int) $val ?></span>
                                        <?php break; ?>

                                    <?php case 'stars': ?>
                                        <?= stars((int) $val) ?>
                                        <?php break; ?>

                                    <?php case 'progress': ?>
                                        <span class="admin-progress"><span style="width:<?= (int) $val ?>%"></span></span>
                                        <span class="mono" style="font-size:.78rem"><?= (int) $val ?>%</span>
                                        <?php break; ?>

                                    <?php case 'bool': ?>
                                        <a class="admin-dot<?= !empty($val) ? ' is-on' : '' ?>"
                                           href="<?= e($listUrl . '&a=toggle&f=' . e($col['key']) . '&id=' . urlencode((string) $row['id']) . '&_token=' . urlencode(csrf_token())) ?>"
                                           title="Değiştir"></a>
                                        <?php break; ?>

                                    <?php default: ?>
                                        <span class="text-dim"><?= e(str_limit((string) $val, 40)) ?></span>
                                <?php endswitch; ?>
                            </td>
                        <?php endforeach; ?>

                        <td>
                            <a class="badge <?= !empty($row['active']) ? 'badge--success' : '' ?>"
                               href="<?= e($listUrl . '&a=toggle&f=active&id=' . urlencode((string) $row['id']) . '&_token=' . urlencode(csrf_token())) ?>">
                                <?= !empty($row['active']) ? 'Yayında' : 'Taslak' ?>
                            </a>
                        </td>

                        <td class="admin-table__actions">
                            <a class="admin-table__action" href="<?= e($listUrl . '&a=form&id=' . urlencode((string) $row['id'])) ?>" title="Düzenle"><?= icon('edit', 16) ?></a>
                            <a class="admin-table__action" href="<?= e($listUrl . '&a=duplicate&id=' . urlencode((string) $row['id']) . '&_token=' . urlencode(csrf_token())) ?>" title="Kopyala"><?= icon('layers', 16) ?></a>
                            <?= admin_delete_link($listUrl . '&a=delete&id=' . urlencode((string) $row['id']) . '&_token=' . urlencode(csrf_token())) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= pagination_html($paged, admin_url('?p=kaynak&r=' . urlencode($resKey) . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>

    <?php else: ?>
        <div class="empty-state" style="border:0">
            <?= icon($res['icon'], 46) ?>
            <h3><?= $q !== '' ? 'Sonuç bulunamadı' : 'Henüz kayıt yok' ?></h3>
            <p><?= $q !== '' ? 'Farklı bir arama terimi deneyin.' : 'İlk kaydınızı ekleyerek başlayın.' ?></p>
            <a class="btn btn--primary mt-6" href="<?= e($listUrl . '&a=form') ?>">
                <?= icon('plus', 16) ?><span>Yeni <?= e($res['label_single']) ?> ekle</span>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
admin_footer();
