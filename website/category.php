<?php

require_once __DIR__ . '/includes/header.php';

$pdo = db();

$categoryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id AND is_active = 1 LIMIT 1');
$stmt->execute([':id' => $categoryId]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    ?>
    <div class="container py-5">
        <h1 class="h3 mb-3">Kategori bulunamadı</h1>
        <p class="text-muted">Aradığınız kategori sistemde yer almıyor veya pasif durumda.</p>
        <a href="sectors.php" class="btn btn-outline-secondary btn-sm">Tüm sektörlere dön</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$categories = get_active_categories();

$stmt = $pdo->prepare('SELECT * FROM products WHERE category_id = :cid AND is_active = 1 ORDER BY sort_order ASC, name ASC');
$stmt->execute([':cid' => $categoryId]);
$products = $stmt->fetchAll();
?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <aside class="col-lg-3 mb-4 mb-lg-0">
                <h2 class="h6 text-uppercase text-muted mb-3">Sektörler</h2>
                <ul class="list-group small">
                    <?php foreach ($categories as $cat): ?>
                        <?php $active = ((int)$cat['id'] === $categoryId); ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center <?= $active ? 'active' : '' ?>">
                            <a href="category.php?id=<?= e((string) $cat['id']) ?>"
                               class="text-decoration-none <?= $active ? 'text-white' : 'text-dark' ?>">
                                <?= e($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h1 class="h3 mb-1"><?= e($category['name']) ?></h1>
                        <?php if (!empty($category['short_description'])): ?>
                            <p class="text-muted mb-0 small"><?= e($category['short_description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="sectors.php" class="btn btn-outline-secondary btn-sm">
                        Tüm sektörler
                    </a>
                </div>
                <div class="row g-3">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4">
                            <a href="product.php?id=<?= e((string) $product['id']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                                <?php if (!empty($product['main_image'])): ?>
                                    <img src="<?= e($product['main_image']) ?>" class="card-img-top" alt="<?= e($product['name']) ?>">
                                <?php endif; ?>
                                <div class="card-body py-3">
                                    <h2 class="h6 mb-1"><?= e($product['name']) ?></h2>
                                    <?php if (!empty($product['code'])): ?>
                                        <p class="small text-muted mb-1">Kod: <?= e($product['code']) ?></p>
                                    <?php endif; ?>
                                    <p class="small text-muted mb-0"><?= e($product['short_description'] ?? '') ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Bu kategoriye henüz ürün eklenmemiş.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

