<?php

require_once __DIR__ . '/../includes/auth.php';

require_admin_login();

$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token           = $_POST['csrf_token'] ?? null;
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword     = trim($_POST['new_password'] ?? '');
    $newPassword2    = trim($_POST['new_password2'] ?? '');

    if (!verify_csrf_token($token)) {
        $error = 'Güvenlik doğrulaması başarısız. Lütfen formu tekrar gönderin.';
    } elseif ($currentPassword === '' || $newPassword === '' || $newPassword2 === '') {
        $error = 'Tüm alanlar zorunludur.';
    } elseif ($newPassword !== $newPassword2) {
        $error = 'Yeni şifreler birbiriyle uyuşmuyor.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Yeni şifre en az 8 karakter olmalıdır.';
    } else {
        $userId = (int) ($_SESSION['admin_id'] ?? 0);
        if ($userId <= 0) {
            $error = 'Oturum bulunamadı.';
        } else {
            if (change_admin_password($userId, $currentPassword, $newPassword)) {
                $success = 'Şifreniz başarıyla güncellendi.';
            } else {
                $error = 'Mevcut şifre hatalı.';
            }
        }
    }
}

$token = csrf_token();

include __DIR__ . '/partials_header.php';
?>

<?php if (!empty($_GET['force'])): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <strong>Güvenlik uyarısı:</strong>&nbsp;Varsayılan şifreyi kullanıyorsunuz. Devam etmeden önce lütfen şifrenizi değiştirin.
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <strong>Profil Bilgileri</strong>
            </div>
            <div class="card-body">
                <p><strong>Kullanıcı adı:</strong> <?= e($_SESSION['admin_username'] ?? 'admin') ?></p>
                <p class="text-muted small mb-0">
                    Buradan sadece şifrenizi değiştirebilirsiniz. Gerekirse daha sonra kullanıcı yönetimi ekleyebiliriz.
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <strong>Şifre Değiştir</strong>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success py-2"><?= e($success) ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                    <div class="mb-3">
                        <label class="form-label">Mevcut Şifre</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <div class="form-text">En az 8 karakter, mümkünse harf + rakam kombinasyonu.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="new_password2" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Şifreyi Güncelle
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials_footer.php'; ?>

