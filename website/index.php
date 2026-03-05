<?php

require_once __DIR__ . '/includes/header.php';

$sections = get_home_sections();
$categories = get_active_categories();
$latestNews = get_latest_news(3);
?>

<?php foreach ($sections as $section): ?>
    <?php
    $type = $section['section_type'];
    $c    = $section['content'];
    ?>

    <?php if ($type === 'hero'): ?>
        <section class="fx-hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <p class="text-uppercase small text-slate-300 mb-2"><?= e($c['eyebrow'] ?? 'Rubber hoses since 1966') ?></p>
                        <h1 class="display-5 fw-bold mb-3"><?= e($c['title'] ?? 'Industrial hoses and cable solutions') ?></h1>
                        <p class="lead mb-4"><?= e($c['subtitle'] ?? 'Flexion, ağır sanayi ve kritik uygulamalar için yüksek performanslı hortum ve kablo çözümleri sunar.') ?></p>
                        <?php if (!empty($c['button_text'])): ?>
                            <a href="<?= e($c['button_url'] ?? '#') ?>" class="btn btn-primary btn-lg">
                                <?= e($c['button_text']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-6 text-center">
                        <?php if (!empty($c['image'])): ?>
                            <img src="<?= e($c['image']) ?>" alt="" class="img-fluid rounded-3 shadow-lg">
                        <?php else: ?>
                            <img src="assets/placeholders/hero-hoses.jpg" alt="" class="img-fluid rounded-3 shadow-lg">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php elseif ($type === 'sectors'): ?>
        <section class="py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h2 class="h3 mb-1"><?= e($c['title'] ?? 'Uygulama Sektörleri') ?></h2>
                        <p class="text-muted mb-0 small"><?= e($c['subtitle'] ?? 'Zorlu endüstriyel ortamlar için tasarlanmış hortum ve kablolar.') ?></p>
                    </div>
                    <a href="sectors.php" class="btn btn-outline-secondary btn-sm">
                        Tüm sektörleri gör
                    </a>
                </div>
                <div class="row g-3">
                    <?php foreach ($categories as $cat): ?>
                        <div class="col-6 col-md-3">
                            <a href="category.php?id=<?= e((string) $cat['id']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                                <?php if (!empty($cat['image'])): ?>
                                    <img src="<?= e($cat['image']) ?>" class="card-img-top" alt="<?= e($cat['name']) ?>">
                                <?php endif; ?>
                                <div class="card-body py-3">
                                    <h3 class="h6 mb-1"><?= e($cat['name']) ?></h3>
                                    <p class="small text-muted mb-0"><?= e($cat['short_description'] ?? '') ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php elseif ($type === 'text_image'): ?>
        <section class="py-5">
            <div class="container">
                <div class="row align-items-center <?= !empty($c['image_right']) ? 'flex-row-reverse' : '' ?>">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h2 class="h3 mb-3"><?= e($c['title'] ?? 'About Flexion') ?></h2>
                        <p class="text-muted mb-3"><?= e($c['text'] ?? '') ?></p>
                        <?php if (!empty($c['button_text'])): ?>
                            <a href="<?= e($c['button_url'] ?? '#') ?>" class="btn btn-outline-secondary">
                                <?= e($c['button_text']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($c['image'])): ?>
                            <img src="<?= e($c['image']) ?>" alt="" class="img-fluid rounded-3 shadow-sm">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php elseif ($type === 'news'): ?>
        <section class="py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h2 class="h3 mb-1"><?= e($c['title'] ?? 'Güncel Haberler') ?></h2>
                        <p class="text-muted mb-0 small"><?= e($c['subtitle'] ?? '') ?></p>
                    </div>
                    <a href="news.php" class="btn btn-outline-secondary btn-sm">
                        Tüm haberleri gör
                    </a>
                </div>
                <div class="row g-3">
                    <?php foreach ($latestNews as $news): ?>
                        <div class="col-md-4">
                            <a href="news.php?slug=<?= e($news['slug']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                                <?php if (!empty($news['image'])): ?>
                                    <img src="<?= e($news['image']) ?>" class="card-img-top" alt="<?= e($news['title']) ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h3 class="h6 mb-2"><?= e($news['title']) ?></h3>
                                    <p class="small text-muted mb-0"><?= e($news['summary'] ?? '') ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php endforeach; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

