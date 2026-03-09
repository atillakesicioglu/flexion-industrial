<?php

require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $token    = $_POST['csrf_token'] ?? null;

    if (!verify_csrf_token($token)) {
        $error = 'Güvenlik doğrulaması başarısız. Lütfen formu tekrar gönderin.';
    } elseif ($username === '' || $password === '') {
        $error = 'Kullanıcı adı ve şifre zorunludur.';
    } else {
        if (admin_login($username, $password)) {
            redirect('index.php');
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flexion Admin Giriş</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f5f7;
        }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e1e4ea;
        }
        .brand {
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #d71920;
        }
        .btn-primary {
            background-color: #d71920;
            border-color: #d71920;
        }
        .btn-primary:hover {
            background-color: #b5131a;
            border-color: #b5131a;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card p-4 p-md-5 bg-white">
        <div class="mb-4 text-center">
            <div class="brand fs-4 mb-1">FLEXION</div>
            <div class="text-muted small">Admin Kontrol Paneli</div>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
            <div class="mb-3">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" name="username" class="form-control" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <?php if (APP_ENV === 'development'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <small class="text-muted">Varsayılan: <strong>admin / admin123</strong></small>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary w-100">
                Giriş Yap
            </button>
        </form>
    </div>
</div>
</body>
</html>

