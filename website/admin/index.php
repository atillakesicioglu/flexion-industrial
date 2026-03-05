<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_login();

$pdo = db();

$totalCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalProducts   = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalNews       = (int) $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();

include __DIR__ . '/partials_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Kategoriler</div>
                        <div class="h4 mb-0"><?= e((string) $totalCategories) ?></div>
                    </div>
                    <div class="text-danger fs-3">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Ürünler</div>
                        <div class="h4 mb-0"><?= e((string) $totalProducts) ?></div>
                    </div>
                    <div class="text-primary fs-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Haberler</div>
                        <div class="h4 mb-0"><?= e((string) $totalNews) ?></div>
                    </div>
                    <div class="text-success fs-3">
                        <i class="bi bi-newspaper"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <strong>Hızlı Aksiyonlar</strong>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="categories.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Kategorileri Yönet
                </a>
            </div>
            <div class="col-md-3">
                <a href="products.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-box-seam me-2"></i>Ürünleri Yönet
                </a>
            </div>
            <div class="col-md-3">
                <a href="homepage.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-layout-text-window-reverse me-2"></i>Ana Sayfa Blokları
                </a>
            </div>
            <div class="col-md-3">
                <a href="news.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-newspaper me-2"></i>Haberler & Insights
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials_footer.php'; ?>

