<?php
/**
 * Store — JSON tabanlı, dosya kilitli, atomik veri katmanı.
 *
 * Tüm site verisi /data altındaki .json dosyalarında tutulur.
 * Veritabanı gerekmez; paylaşımlı hostinglerde de sorunsuz çalışır.
 *
 *  Store::read('services')                  → dizi
 *  Store::write('services', $arr)           → bool
 *  Store::mutate('services', fn($d) => $d)  → kilitli okuma+yazma
 *  Store::all('services')                   → kayıt listesi
 *  Store::find('services', $id)             → tek kayıt
 *  Store::insert('services', $row)          → yeni id
 *  Store::update('services', $id, $row)     → bool
 *  Store::delete('services', $id)           → bool
 */

declare(strict_types=1);

final class Store
{
    /** @var array<string, array> Bellek içi okuma önbelleği */
    private static array $cache = [];

    /** Depolama dizinlerini ve koruma dosyalarını hazırlar. */
    public static function ensureStorage(): void
    {
        foreach ([DATA_PATH, UPLOAD_PATH, UPLOAD_PATH . '/projects', UPLOAD_PATH . '/blog', UPLOAD_PATH . '/misc'] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }

        // /data dizinine doğrudan HTTP erişimini engelle
        $htaccess = DATA_PATH . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents(
                $htaccess,
                "# Veri dosyalarına doğrudan erişim yasak\n"
                . "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
                . "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n"
            );
        }
        $indexGuard = DATA_PATH . '/index.html';
        if (!file_exists($indexGuard)) {
            @file_put_contents($indexGuard, '');
        }
    }

    /** Dosya yolunu döndürür. */
    public static function path(string $name): string
    {
        $name = preg_replace('/[^a-z0-9_\-]/i', '', $name) ?? '';
        return DATA_PATH . '/' . $name . '.json';
    }

    /** Dosya var mı? */
    public static function exists(string $name): bool
    {
        return is_file(self::path($name));
    }

    /**
     * JSON dosyasını okur.
     *
     * @return array
     */
    public static function read(string $name, bool $fresh = false): array
    {
        if (!$fresh && isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        $file = self::path($name);
        if (!is_file($file)) {
            return self::$cache[$name] = [];
        }

        $handle = @fopen($file, 'rb');
        if ($handle === false) {
            return self::$cache[$name] = [];
        }

        $raw = '';
        if (flock($handle, LOCK_SH)) {
            $size = filesize($file) ?: 0;
            $raw  = $size > 0 ? (fread($handle, $size) ?: '') : '';
            flock($handle, LOCK_UN);
        }
        fclose($handle);

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = [];
        }

        return self::$cache[$name] = $data;
    }

    /**
     * JSON dosyasına atomik yazar (geçici dosya + rename).
     */
    public static function write(string $name, array $data): bool
    {
        $file = self::path($name);
        $dir  = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($json === false) {
            return false;
        }

        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
            return false;
        }
        @chmod($tmp, 0664);

        if (!@rename($tmp, $file)) {
            @unlink($tmp);
            return false;
        }

        self::$cache[$name] = $data;
        return true;
    }

    /**
     * Kilitli okuma → dönüştürme → yazma. Yarış koşullarını engeller.
     *
     * @param callable(array):array $callback
     */
    public static function mutate(string $name, callable $callback): bool
    {
        $file = self::path($name);
        if (!is_file($file)) {
            @file_put_contents($file, "[]");
        }

        $handle = @fopen($file, 'c+b');
        if ($handle === false) {
            return false;
        }

        $ok = false;
        if (flock($handle, LOCK_EX)) {
            $raw  = stream_get_contents($handle) ?: '';
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                $data = [];
            }

            $result = $callback($data);
            if (!is_array($result)) {
                $result = $data;
            }

            $json = json_encode(
                $result,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($json !== false) {
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, $json);
                fflush($handle);
                self::$cache[$name] = $result;
                $ok = true;
            }
            flock($handle, LOCK_UN);
        }
        fclose($handle);

        return $ok;
    }

    /* =============================================================
     |  Koleksiyon yardımcıları
     ============================================================= */

    /**
     * Koleksiyondaki tüm kayıtları döndürür.
     *
     * @param array{status?:string, sort?:string, dir?:string} $opts
     */
    public static function all(string $name, array $opts = []): array
    {
        $rows = self::read($name);
        $rows = array_values(array_filter($rows, 'is_array'));

        if (!empty($opts['only_active'])) {
            $rows = array_values(array_filter($rows, static fn($r) => !isset($r['active']) || $r['active']));
        }

        $sort = $opts['sort'] ?? 'order';
        $dir  = strtolower($opts['dir'] ?? 'asc');

        usort($rows, static function (array $a, array $b) use ($sort, $dir) {
            $x = $a[$sort] ?? 0;
            $y = $b[$sort] ?? 0;
            $cmp = is_numeric($x) && is_numeric($y)
                ? ($x <=> $y)
                : strcmp((string) $x, (string) $y);
            return $dir === 'desc' ? -$cmp : $cmp;
        });

        return $rows;
    }

    /** Tek kayıt (id ile). */
    public static function find(string $name, $id): ?array
    {
        foreach (self::read($name) as $row) {
            if (is_array($row) && (string) ($row['id'] ?? '') === (string) $id) {
                return $row;
            }
        }
        return null;
    }

    /** Tek kayıt (herhangi bir alan ile). */
    public static function findBy(string $name, string $field, $value): ?array
    {
        foreach (self::read($name) as $row) {
            if (is_array($row) && isset($row[$field]) && (string) $row[$field] === (string) $value) {
                return $row;
            }
        }
        return null;
    }

    /** Yeni kayıt ekler, id döndürür. */
    public static function insert(string $name, array $row): string
    {
        $id = $row['id'] ?? self::uid();
        $row['id'] = (string) $id;
        $row['created_at'] = $row['created_at'] ?? date('Y-m-d H:i:s');
        $row['updated_at'] = date('Y-m-d H:i:s');

        self::mutate($name, static function (array $data) use ($row): array {
            $data[] = $row;
            return $data;
        });

        return (string) $id;
    }

    /** Kaydı günceller. */
    public static function update(string $name, $id, array $fields): bool
    {
        $found = false;
        self::mutate($name, static function (array $data) use ($id, $fields, &$found): array {
            foreach ($data as $i => $row) {
                if (is_array($row) && (string) ($row['id'] ?? '') === (string) $id) {
                    $data[$i] = array_merge($row, $fields, [
                        'id'         => (string) $id,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $found = true;
                    break;
                }
            }
            return $data;
        });
        return $found;
    }

    /** Kaydı siler. */
    public static function delete(string $name, $id): bool
    {
        $found = false;
        self::mutate($name, static function (array $data) use ($id, &$found): array {
            $out = [];
            foreach ($data as $row) {
                if (is_array($row) && (string) ($row['id'] ?? '') === (string) $id) {
                    $found = true;
                    continue;
                }
                $out[] = $row;
            }
            return array_values($out);
        });
        return $found;
    }

    /** Kayıt sayısı. */
    public static function count(string $name, ?callable $filter = null): int
    {
        $rows = self::read($name);
        if ($filter === null) {
            return count($rows);
        }
        return count(array_filter($rows, $filter));
    }

    /** Benzersiz kimlik üretir. */
    public static function uid(string $prefix = ''): string
    {
        return $prefix . base_convert((string) time(), 10, 36) . bin2hex(random_bytes(3));
    }

    /** Önbelleği temizler. */
    public static function flush(?string $name = null): void
    {
        if ($name === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$name]);
        }
    }

    /** Tüm veri dosyalarını tek JSON olarak dışa aktarır (yedekleme). */
    public static function exportAll(): array
    {
        $out = [];
        foreach (glob(DATA_PATH . '/*.json') ?: [] as $file) {
            $key = basename($file, '.json');
            $out[$key] = self::read($key, true);
        }
        return $out;
    }
}
