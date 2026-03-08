<?php

require_once __DIR__ . '/../includes/auth.php';

require_admin_login();

$pdo     = db();
$error   = null;
$success = null;

// Silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? null)) {
    if (isset($_POST['delete_submission'])) {
        $sid = (int)($_POST['id'] ?? 0);
        if ($sid > 0) {
            try {
                $pdo->prepare('DELETE FROM contact_submissions WHERE id = ?')->execute([$sid]);
                $success = 'Mesaj silindi.';
            } catch (Throwable $e) {
                $error = 'Silinemedi: ' . htmlspecialchars($e->getMessage());
            }
        }
    } elseif (isset($_POST['mark_read'])) {
        $sid = (int)($_POST['id'] ?? 0);
        if ($sid > 0) {
            try {
                $pdo->prepare('UPDATE contact_submissions SET is_read = 1 WHERE id = ?')->execute([$sid]);
                $success = 'Okundu olarak işaretlendi.';
            } catch (Throwable $e) {}
        }
    } elseif (isset($_POST['mark_all_read'])) {
        try {
            $pdo->exec('UPDATE contact_submissions SET is_read = 1');
            $success = 'Tümü okundu olarak işaretlendi.';
        } catch (Throwable $e) {}
    }
}

// Filtre
$filterType = $_GET['type'] ?? '';
if (!in_array($filterType, ['contact', 'inquiry'], true)) $filterType = '';

$submissions = [];
$unreadCount = 0;
try {
    $where = $filterType ? 'WHERE cs.type = :type' : '';
    $sql = "SELECT cs.*, p.name AS product_name
            FROM contact_submissions cs
            LEFT JOIN products p ON p.id = cs.product_id
            $where
            ORDER BY cs.created_at DESC";
    $stmt = $pdo->prepare($sql);
    if ($filterType) $stmt->bindValue(':type', $filterType);
    $stmt->execute();
    $submissions = $stmt->fetchAll();

    $unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0')->fetchColumn();
} catch (Throwable $e) {
    $error = 'Mesajlar alınamadı. Lütfen <a href="migrate.php">migrasyonu</a> çalıştırın.';
}

$token = csrf_token();
include __DIR__ . '/partials_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="h5 mb-0">Gelen Mesajlar
            <?php if ($unreadCount > 0): ?>
                <span class="badge bg-danger ms-2"><?= $unreadCount ?> yeni</span>
            <?php endif; ?>
        </h2>
        <p class="text-muted small mb-0">İletişim formu ve ürün bilgi talepleri</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="submissions.php" class="btn btn-sm btn-outline-secondary <?= !$filterType ? 'active' : '' ?>">Tümü</a>
        <a href="submissions.php?type=contact" class="btn btn-sm btn-outline-secondary <?= $filterType==='contact' ? 'active' : '' ?>">
            <i class="bi bi-envelope me-1"></i>İletişim
        </a>
        <a href="submissions.php?type=inquiry" class="btn btn-sm btn-outline-secondary <?= $filterType==='inquiry' ? 'active' : '' ?>">
            <i class="bi bi-info-circle me-1"></i>Bilgi Talebi
        </a>
        <?php if ($unreadCount > 0): ?>
        <form method="post" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
            <button name="mark_all_read" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-check-all me-1"></i>Tümünü Okundu İşaretle
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success py-2"><?= $success ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($submissions)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                Henüz mesaj bulunmuyor.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tür</th>
                        <th>Ad</th>
                        <th>E-posta</th>
                        <th>Şirket / Ülke</th>
                        <th>Ürün</th>
                        <th>Mesaj</th>
                        <th>Tarih</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                        <tr class="<?= !(bool)$sub['is_read'] ? 'table-warning' : '' ?>">
                            <td>
                                <?php if ($sub['type'] === 'inquiry'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">Bilgi Talebi</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border small">İletişim</span>
                                <?php endif; ?>
                                <?php if (!(bool)$sub['is_read']): ?>
                                    <span class="badge bg-danger ms-1 small">Yeni</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= e($sub['name']) ?></td>
                            <td>
                                <a href="mailto:<?= e($sub['email']) ?>" class="text-decoration-none small">
                                    <?= e($sub['email']) ?>
                                </a>
                                <?php if ($sub['phone']): ?>
                                    <br><span class="small text-muted"><?= e($sub['phone']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= e($sub['company'] ?? '') ?>
                                <?php if ($sub['country']): ?><br><?= e($sub['country']) ?><?php endif; ?>
                            </td>
                            <td class="small">
                                <?php if ($sub['product_id'] && $sub['product_name']): ?>
                                    <a href="../product.php?id=<?= e((string)$sub['product_id']) ?>" target="_blank" class="text-decoration-none">
                                        <?= e($sub['product_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small" style="max-width:200px;">
                                <span data-bs-toggle="tooltip" title="<?= e($sub['message']) ?>">
                                    <?= e(mb_strimwidth($sub['message'], 0, 60, '...')) ?>
                                </span>
                            </td>
                            <td class="small text-muted text-nowrap">
                                <?= e(date('d.m.Y H:i', strtotime($sub['created_at']))) ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <?php if (!(bool)$sub['is_read']): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                                        <input type="hidden" name="id" value="<?= e((string)$sub['id']) ?>">
                                        <button name="mark_read" class="btn btn-xs btn-sm btn-outline-success py-0 px-2">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-xs btn-sm btn-outline-primary py-0 px-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#msgModal<?= e((string)$sub['id']) ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                                        <input type="hidden" name="id" value="<?= e((string)$sub['id']) ?>">
                                        <button name="delete_submission" class="btn btn-xs btn-sm btn-outline-danger py-0 px-2"
                                                onclick="return confirm('Bu mesajı silmek istiyor musunuz?')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <!-- Mesaj detay modal -->
                        <div class="modal fade" id="msgModal<?= e((string)$sub['id']) ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title"><?= e($sub['name']) ?> — <?= e(date('d.m.Y H:i', strtotime($sub['created_at']))) ?></h6>
                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if ($sub['product_name']): ?>
                                            <p class="small"><strong>Ürün:</strong> <?= e($sub['product_name']) ?></p>
                                        <?php endif; ?>
                                        <p class="small"><strong>E-posta:</strong> <?= e($sub['email']) ?></p>
                                        <?php if ($sub['phone']): ?><p class="small"><strong>Telefon:</strong> <?= e($sub['phone']) ?></p><?php endif; ?>
                                        <?php if ($sub['company']): ?><p class="small"><strong>Şirket:</strong> <?= e($sub['company']) ?></p><?php endif; ?>
                                        <?php if ($sub['country']): ?><p class="small"><strong>Ülke:</strong> <?= e($sub['country']) ?></p><?php endif; ?>
                                        <hr>
                                        <p style="white-space:pre-wrap;"><?= e($sub['message']) ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="mailto:<?= e($sub['email']) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-reply me-1"></i>Yanıtla
                                        </a>
                                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(function (el) { new bootstrap.Tooltip(el); });
});
</script>

<?php include __DIR__ . '/partials_footer.php'; ?>
