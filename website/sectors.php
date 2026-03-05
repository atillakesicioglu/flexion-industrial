<?php

require_once __DIR__ . '/includes/header.php';

$categories = get_active_categories();
?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <aside class="col-lg-3 mb-4 mb-lg-0">
                <h2 class="h6 text-uppercase text-muted mb-3">Sektörler</h2>
                <ul class="list-group small">
                    <?php foreach ($categories as $cat): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="category.php?id=<?= e((string) $cat['id']) ?>" class="text-decoration-none text-dark">
                                <?= e($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
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
                        <div class="col-6 col-md-4">
                            <a href="category.php?id=<?= e((string) $cat['id']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                                <?php if (!empty($cat['image'])): ?>
                                    <img src="<?= e($cat['image']) ?>" class="card-img-top" alt="<?= e($cat['name']) ?>">
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

