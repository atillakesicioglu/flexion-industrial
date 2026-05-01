<?php

require_once __DIR__ . '/includes/header.php';

$categoriesTree = get_categories_tree();
$pdo = db();

$allProducts = [];
try {
    $stmt = $pdo->query(
        'SELECT p.id, p.name, p.code, p.main_image, p.short_description, c.name AS category_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1
         ORDER BY c.sort_order ASC, c.name ASC, p.sort_order ASC, p.name ASC'
    );
    $allProducts = $stmt->fetchAll();
} catch (Throwable $e) {
    $allProducts = [];
}
?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <aside class="col-lg-3 mb-4 mb-lg-0">
                <h2 class="h6 text-uppercase text-muted mb-3">Sektörler</h2>
                <div class="fx-cat-accordion">
                    <?php foreach ($categoriesTree as $cat):
                        $cid        = (int)$cat['id'];
                        $hasChildren = !empty($cat['children']);
                        $accId       = 'fx-cat-' . $cid;
                    ?>
                    <div class="fx-cat-item">
                        <?php if ($hasChildren): ?>
                            <button class="fx-cat-btn"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= $accId ?>"
                                    aria-expanded="false"
                                    aria-controls="<?= $accId ?>">
                                <span><?= e($cat['name']) ?></span>
                                <i class="bi bi-chevron-down fx-cat-chevron"></i>
                            </button>
                            <div class="collapse" id="<?= $accId ?>">
                                <div class="fx-cat-children">
                                    <?php foreach ($cat['children'] as $child): ?>
                                        <a href="category?id=<?= (int)$child['id'] ?>"
                                           class="fx-cat-child-link"><?= e($child['name']) ?></a>
                                    <?php endforeach; ?>
                                    <a href="category?id=<?= $cid ?>"
                                       class="fx-cat-child-link fx-cat-all-link">
                                        <i class="bi bi-grid-3x3-gap me-1"></i>Tümünü gör
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="category?id=<?= $cid ?>"
                               class="fx-cat-btn text-decoration-none">
                                <span><?= e($cat['name']) ?></span>
                                <i class="bi bi-chevron-right fx-cat-chevron" style="transform:none;"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </aside>
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h1 class="h3 mb-1">Uygulama Sektörleri</h1>
                        <p class="text-muted mb-0 small">
                            Flexion hortum ve kablolarının kullanıldığı başlıca endüstriyel alanlar.
                        </p>
                    </div>
                </div>
                <div class="row g-3">
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-6 col-md-4 fx-animate">
                            <a href="category?id=<?= e((string) $cat['id']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark overflow-hidden">
                                <?php if (!empty($cat['image'])): ?>
                                    <img src="<?= e($cat['image']) ?>" class="card-img-top fx-card-img" alt="<?= e($cat['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="fx-card-img bg-light text-muted">
                                        <i class="bi bi-image fs-2"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body py-3">
                                    <h2 class="h6 mb-1"><?= e($cat['name']) ?></h2>
                                    <p class="small text-muted mb-0"><?= e($cat['short_description'] ?? '') ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr class="my-5">

                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h2 class="h4 mb-1">Tum Urunler</h2>
                        <p class="text-muted mb-0 small">
                            Tum kategorilerdeki aktif urunlerin tam listesi.
                        </p>
                    </div>
                    <span class="badge text-bg-light border"><?= count($allProducts) ?> urun</span>
                </div>

                <div class="row g-3">
                    <?php foreach ($allProducts as $product): ?>
                        <div class="col-6 col-md-4 col-xl-3 fx-animate">
                            <a href="product?id=<?= e((string) $product['id']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark overflow-hidden">
                                <?php if (!empty($product['main_image'])): ?>
                                    <img src="<?= e($product['main_image']) ?>" class="card-img-top fx-card-img" alt="<?= e($product['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="fx-card-img bg-light text-muted">
                                        <i class="bi bi-box-seam fs-2"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body py-3">
                                    <h3 class="h6 mb-1"><?= e($product['name']) ?></h3>
                                    <p class="small text-muted mb-1"><?= e($product['category_name']) ?></p>
                                    <?php if (!empty($product['code'])): ?>
                                        <p class="small text-muted mb-1">Kod: <?= e($product['code']) ?></p>
                                    <?php endif; ?>
                                    <p class="small text-muted mb-0"><?= e($product['short_description'] ?? '') ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($allProducts)): ?>
                        <div class="col-12">
                            <div class="alert alert-info mb-0">Henüz aktif urun bulunmuyor.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

