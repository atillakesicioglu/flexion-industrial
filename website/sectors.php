<?php

require_once __DIR__ . '/includes/header.php';

$categoriesTree   = get_categories_tree();
// Legacy sectors page: üst seviye kategoriler kart olarak listelenir
$categories       = $categoriesTree;
$activeCategoryId = 0;
?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <aside class="col-lg-3 mb-4 mb-lg-0">
                <?php require __DIR__ . '/includes/categories_sidebar.php'; ?>
            </aside>
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h1 class="h3 mb-1"><?= e(t('cat_categories_title', 'Categories')) ?></h1>
                        <p class="text-muted mb-0 small">
                            <?= e(t('sectors_legacy_desc', 'Main industrial application areas for Flexion hose solutions.')) ?>
                        </p>
                    </div>
                </div>
                <div class="row g-3">
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-6 col-md-4 fx-animate">
                            <?php $catHref = !empty($cat['slug']) ? localized_url('/' . ltrim((string)$cat['slug'], '/')) : localized_url('category?id=' . (int)$cat['id']); ?>
                            <a href="<?= e($catHref) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark overflow-hidden">
                                <?php if (!empty($cat['image'])): ?>
                                    <img src="<?= e(asset_url($cat['image'])) ?>" class="card-img-top fx-card-img" alt="<?= e($cat['name']) ?>" loading="lazy">
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

