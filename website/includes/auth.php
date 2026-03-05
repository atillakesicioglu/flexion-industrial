<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Admin kullanıcıyı veritabanından çeker.
 */
function find_user_by_username(string $username): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username AND is_active = 1 LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * İlk kurulum kolaylığı için: eğer veritabanındaki şifre hash değilse,
 * ilk başarılı girişte bcrypt ile hash'leyip güncelliyoruz.
 */
function ensure_password_is_hashed(array $user, string $plainPassword): void
{
    // Eğer şifre zaten bcrypt/equiv hash ise dokunma
    if (strlen($user['password']) > 40 && str_starts_with($user['password'], '$2')) {
        return;
    }

    if ($user['password'] !== $plainPassword) {
        return;
    }

    $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
    $stmt->execute([
        ':password' => $hashed,
        ':id'       => $user['id'],
    ]);
}

/**
 * Admin login işlemini yapar.
 */
function admin_login(string $username, string $password): bool
{
    $user = find_user_by_username($username);

    if (!$user) {
        return false;
    }

    $dbPassword = $user['password'];

    $passwordOk = false;

    // 1) Hash'lenmiş şifre senaryosu
    if (strlen($dbPassword) > 40 && str_starts_with($dbPassword, '$2')) {
        $passwordOk = password_verify($password, $dbPassword);
    } else {
        // 2) Eski/düz metin şifre senaryosu
        $passwordOk = ($password === $dbPassword);
    }

    if (!$passwordOk) {
        return false;
    }

    // Eğer şifre düz metinse ilk başarılı girişte hash'e çevir
    ensure_password_is_hashed($user, $password);

    session_regenerate_id(true);
    $_SESSION['admin_id']       = (int) $user['id'];
    $_SESSION['admin_username'] = $user['username'];

    return true;
}

/**
 * Admin logout.
 */
function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Admin şifresini değiştirir.
 */
function change_admin_password(int $userId, string $currentPassword, string $newPassword): bool
{
    $pdo = db();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id AND is_active = 1 LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $dbPassword = $user['password'];

    // Mevcut şifre doğrulaması
    $currentOk = false;
    if (strlen($dbPassword) > 40 && str_starts_with($dbPassword, '$2')) {
        $currentOk = password_verify($currentPassword, $dbPassword);
    } else {
        $currentOk = ($currentPassword === $dbPassword);
    }

    if (!$currentOk) {
        return false;
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

    $update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
    $update->execute([
        ':password' => $hashed,
        ':id'       => $userId,
    ]);

    return true;
}

