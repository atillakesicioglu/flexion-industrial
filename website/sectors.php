<?php

require_once __DIR__ . '/includes/header.php';

$categories = get_active_categories();

// Sidebar accordion için tüm kategorilerin ürünleri
$sidebarProducts = [];
try {
    $pdo    = db();
    $spStmt = $pdo->query('SELECT id, name, category_id FROM products WHERE is_active = 1 ORDER BY category_id, sort_order ASC, id ASC');
    foreach ($spStmt->fetchAll() as $sp) {
        $sidebarProducts[$sp['category_id']][] = $sp;
    }
} catch (Throwable $e) {
    $sidebarProducts = [];
}
?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <aside class="col-lg-3 mb-4 mb-lg-0">
                <h2 class="h6 text-uppercase text-muted mb-3">Sektörler</h2>
                <div class="fx-cat-accordion">
                    <?php foreach ($categories as $cat):
                        $cid      = (int)$cat['id'];
                        $catProds = $sidebarProducts[$cid] ?? [];
                        $hasProds = !empty($catProds);
                        $accId    = 'fx-cat-' . $cid;
                    ?>
                    <div class="fx-cat-item">
                        <?php if ($hasProds): ?>
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
                                    <?php foreach ($catProds as $pr): ?>
                                        <a href="product.php?id=<?= (int)$pr['id'] ?>"
                                           class="fx-cat-child-link"><?= e($pr['name']) ?></a>
                                    <?php endforeach; ?>
                                    <a href="category.php?id=<?= $cid ?>"
                                       class="fx-cat-child-link fx-cat-all-link">
                                        <i class="bi bi-grid-3x3-gap me-1"></i>Tümünü gör
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="category.php?id=<?= $cid ?>"
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
                            <a href="category.php?id=<?= e((string) $cat['id']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark overflow-hidden">
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
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

