<?php
/**
 * Kimlik doğrulama katmanı.
 * Yönetici hesabı /data/users.json içinde tutulur.
 * İlk girişte tek seferlik kurulum ekranı devreye girer.
 */

declare(strict_types=1);

/** Sistemde en az bir yönetici var mı? */
function auth_installed(): bool
{
    $users = Store::read('users', true);
    foreach ($users as $u) {
        if (!empty($u['username']) && !empty($u['password'])) {
            return true;
        }
    }
    return false;
}

/** Oturum açmış kullanıcı. */
function auth_user(): ?array
{
    if (empty($_SESSION['uid'])) {
        return null;
    }
    $user = Store::find('users', $_SESSION['uid']);
    if ($user === null || empty($user['active'])) {
        auth_logout();
        return null;
    }
    return $user;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

/** Yönetici mi? */
function auth_is_admin(): bool
{
    $u = auth_user();
    return $u !== null && ($u['role'] ?? 'admin') === 'admin';
}

/**
 * İlk yöneticiyi oluşturur (tek seferlik).
 *
 * @return array{ok:bool, error?:string, id?:string}
 */
function auth_install(string $name, string $username, string $email, string $password, string $passwordConfirm): array
{
    if (auth_installed()) {
        return ['ok' => false, 'error' => 'Yönetici hesabı zaten oluşturulmuş.'];
    }

    $name     = trim($name);
    $username = strtolower(trim($username));
    $email    = trim($email);

    if (mb_strlen($name) < 2) {
        return ['ok' => false, 'error' => 'Ad soyad en az 2 karakter olmalı.'];
    }
    if (!preg_match('/^[a-z0-9_.\-]{3,32}$/', $username)) {
        return ['ok' => false, 'error' => 'Kullanıcı adı 3-32 karakter olmalı; harf, rakam, nokta, tire ve alt çizgi içerebilir.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Geçerli bir e-posta adresi girin.'];
    }
    if (mb_strlen($password) < PASSWORD_MIN) {
        return ['ok' => false, 'error' => 'Parola en az ' . PASSWORD_MIN . ' karakter olmalı.'];
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return ['ok' => false, 'error' => 'Parola en az bir harf ve bir rakam içermeli.'];
    }
    if (!hash_equals($password, $passwordConfirm)) {
        return ['ok' => false, 'error' => 'Parolalar eşleşmiyor.'];
    }

    $id = Store::insert('users', [
        'name'       => $name,
        'username'   => $username,
        'email'      => $email,
        'password'   => password_hash($password, PASSWORD_DEFAULT),
        'role'       => 'admin',
        'active'     => true,
        'avatar'     => '',
        'last_login' => null,
        'login_count'=> 0,
    ]);

    auth_log('install', 'İlk yönetici hesabı oluşturuldu: ' . $username);

    return ['ok' => true, 'id' => $id];
}

/**
 * Giriş denemesi.
 *
 * @return array{ok:bool, error?:string}
 */
function auth_login(string $username, string $password, bool $remember = false): array
{
    $key = 'lock_' . ip_hash();
    $try = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];

    if (($try['until'] ?? 0) > time()) {
        $left = (int) ceil(($try['until'] - time()) / 60);
        return ['ok' => false, 'error' => 'Çok fazla hatalı deneme. ' . $left . ' dakika sonra tekrar deneyin.'];
    }

    $username = strtolower(trim($username));
    $user     = Store::findBy('users', 'username', $username);
    if ($user === null) {
        $user = Store::findBy('users', 'email', $username);
    }

    $valid = $user !== null
        && !empty($user['active'])
        && password_verify($password, (string) ($user['password'] ?? ''));

    if (!$valid) {
        $try['count'] = (int) ($try['count'] ?? 0) + 1;
        if ($try['count'] >= LOGIN_MAX_TRY) {
            $try['until'] = time() + LOGIN_LOCK_TIME;
            $try['count'] = 0;
        }
        $_SESSION[$key] = $try;
        auth_log('login_fail', 'Başarısız giriş denemesi: ' . $username);

        return ['ok' => false, 'error' => 'Kullanıcı adı veya parola hatalı.'];
    }

    unset($_SESSION[$key]);

    // Parola algoritması güncellenmişse yeniden hashle
    if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
        Store::update('users', $user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    session_regenerate_id(true);
    $_SESSION['uid']       = $user['id'];
    $_SESSION['uname']     = $user['username'];
    $_SESSION['__last']    = time();

    Store::update('users', $user['id'], [
        'last_login'  => date('Y-m-d H:i:s'),
        'login_count' => (int) ($user['login_count'] ?? 0) + 1,
    ]);

    auth_log('login', 'Giriş yapıldı: ' . $user['username']);

    return ['ok' => true];
}

function auth_logout(): void
{
    if (!empty($_SESSION['uname'])) {
        auth_log('logout', 'Çıkış yapıldı: ' . $_SESSION['uname']);
    }
    unset($_SESSION['uid'], $_SESSION['uname']);
    session_regenerate_id(true);
}

/** Parola değiştirir. */
function auth_change_password(string $userId, string $current, string $new, string $confirm): array
{
    $user = Store::find('users', $userId);
    if ($user === null) {
        return ['ok' => false, 'error' => 'Kullanıcı bulunamadı.'];
    }
    if (!password_verify($current, (string) $user['password'])) {
        return ['ok' => false, 'error' => 'Mevcut parola hatalı.'];
    }
    if (mb_strlen($new) < PASSWORD_MIN) {
        return ['ok' => false, 'error' => 'Yeni parola en az ' . PASSWORD_MIN . ' karakter olmalı.'];
    }
    if (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
        return ['ok' => false, 'error' => 'Parola en az bir harf ve bir rakam içermeli.'];
    }
    if (!hash_equals($new, $confirm)) {
        return ['ok' => false, 'error' => 'Yeni parolalar eşleşmiyor.'];
    }

    Store::update('users', $userId, ['password' => password_hash($new, PASSWORD_DEFAULT)]);
    auth_log('password', 'Parola değiştirildi: ' . $user['username']);

    return ['ok' => true];
}

/** Oturum zorunlu — değilse giriş sayfasına gönderir. */
function auth_require(): array
{
    $user = auth_user();
    if ($user === null) {
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? admin_url();
        redirect(admin_url('?p=giris'));
    }
    return $user;
}

/** Sistem günlüğü (son 500 kayıt tutulur). */
function auth_log(string $type, string $message): void
{
    Store::mutate('logs', static function (array $logs) use ($type, $message): array {
        $logs[] = [
            'id'      => Store::uid(),
            'type'    => $type,
            'message' => $message,
            'ip'      => ip_hash(),
            'agent'   => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 180),
            'at'      => date('Y-m-d H:i:s'),
        ];
        if (count($logs) > 500) {
            $logs = array_slice($logs, -500);
        }
        return array_values($logs);
    });
}
