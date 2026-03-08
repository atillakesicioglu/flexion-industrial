<?php

require_once __DIR__ . '/includes/header.php';

$pdo  = db();
$slug = $_GET['slug'] ?? null;

if ($slug) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM news WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $article = $stmt->fetch();
    } catch (Throwable $e) {
        $article = null;
    }

    if (!$article) {
        http_response_code(404);
        ?>
        <div class="container py-5">
            <h1 class="h3 mb-3">Haber bulunamadı</h1>
            <p class="text-muted">Aradığınız içerik sistemde yer almıyor veya pasif durumda.</p>
            <a href="news.php" class="btn btn-outline-secondary btn-sm">Tüm haberlere dön</a>
        </div>
        <?php
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
    ?>
    <?php
    $bannerImg   = get_setting('news_banner_image', '');
    $bannerTitle = get_setting('news_banner_title', 'Haberler & Insights');
    $nOpacity    = max(0, min(100, (int) get_setting('news_banner_opacity', '50')));
    $nBlur       = max(0, min(20,  (int) get_setting('news_banner_blur', '0')));
    $nTColor     = get_setting('news_banner_title_color', '#ffffff');
    $nTSize      = get_setting('news_banner_title_size', '2rem');
    $nTPos       = get_setting('news_banner_title_position', 'center');
    $nAlignMap   = ['left'=>'text-start','center'=>'text-center','right'=>'text-end'];
    $nAlignClass = $nAlignMap[$nTPos] ?? 'text-center';
    if ($bannerImg): ?>
        <section class="fx-page-banner mb-0">
            <div class="fx-banner-bg" style="background-image:url('<?= e($bannerImg) ?>');
                 filter:blur(<?= $nBlur ?>px); transform:scale(1.05);"></div>
            <div class="fx-banner-overlay" style="background:rgba(0,0,0,<?= round($nOpacity/100,2) ?>);"></div>
            <div class="fx-banner-content">
                <div class="container <?= $nAlignClass ?>">
                    <h1 class="fx-banner-title"
                        style="color:<?= e($nTColor) ?>;font-size:<?= e($nTSize) ?>;">
                        <?= e($bannerTitle) ?>
                    </h1>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="h3 mb-3"><?= e($article['title']) ?></h1>
                    <?php if (!empty($article['published_at'])): ?>
                        <p class="small text-muted mb-3">
                            <?= e(date('d.m.Y', strtotime($article['published_at']))) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($article['image'])): ?>
                        <img src="<?= e($article['image']) ?>" alt="<?= e($article['title']) ?>" class="img-fluid rounded-3 mb-4">
                    <?php endif; ?>
                    <div class="text-muted small">
                        <?= $article['content'] ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h2 class="h6 mb-3">Diğer Haberler</h2>
                    <ul class="list-unstyled small">
                        <?php
                        $sideNews = [];
                        try {
                            $side = $pdo->prepare('SELECT slug, title FROM news WHERE is_active = 1 AND id <> :id ORDER BY IFNULL(published_at, id) DESC LIMIT 6');
                            $side->execute([':id' => $article['id']]);
                            $sideNews = $side->fetchAll();
                        } catch (Throwable $e) {}
                        foreach ($sideNews as $n): ?>
                            <li class="mb-2">
                                <a href="news.php?slug=<?= e($n['slug']) ?>" class="text-decoration-none">
                                    <?= e($n['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Liste modu
$items = [];
try {
    $stmt  = $pdo->query('SELECT * FROM news WHERE is_active = 1 ORDER BY IFNULL(published_at, id) DESC');
    $items = $stmt->fetchAll();
} catch (Throwable $e) { /* tablo yoksa boş */ }
?>

<?php
$bannerImg   = get_setting('news_banner_image', '');
$bannerTitle = get_setting('news_banner_title', 'Haberler & Insights');
$nOpacity2   = max(0, min(100, (int) get_setting('news_banner_opacity', '50')));
$nBlur2      = max(0, min(20,  (int) get_setting('news_banner_blur', '0')));
$nTColor2    = get_setting('news_banner_title_color', '#ffffff');
$nTSize2     = get_setting('news_banner_title_size', '2rem');
$nTPos2      = get_setting('news_banner_title_position', 'center');
$nAlignMap2  = ['left'=>'text-start','center'=>'text-center','right'=>'text-end'];
$nAlignCls2  = $nAlignMap2[$nTPos2] ?? 'text-center';
if ($bannerImg): ?>
    <section class="fx-page-banner mb-0">
        <div class="fx-banner-bg" style="background-image:url('<?= e($bannerImg) ?>');
             filter:blur(<?= $nBlur2 ?>px); transform:scale(1.05);"></div>
        <div class="fx-banner-overlay" style="background:rgba(0,0,0,<?= round($nOpacity2/100,2) ?>);"></div>
        <div class="fx-banner-content">
            <div class="container <?= $nAlignCls2 ?>">
                <h1 class="fx-banner-title"
                    style="color:<?= e($nTColor2) ?>;font-size:<?= e($nTSize2) ?>;">
                    <?= e($bannerTitle) ?>
                </h1>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h1 class="h3 mb-1">Haberler & Insights</h1>
                <p class="text-muted mb-0 small">Flexion ürünleri ve projeleri hakkında güncel içerikler.</p>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ($items as $news): ?>
                <div class="col-md-4 fx-animate">
                    <a href="news.php?slug=<?= e($news['slug']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                        <?php if (!empty($news['image'])): ?>
                            <img src="<?= e($news['image']) ?>" class="card-img-top fx-card-img" alt="<?= e($news['title']) ?>">
                        <?php else: ?>
                            <div class="fx-card-img bg-light d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-newspaper fs-2"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h2 class="h6 mb-2"><?= e($news['title']) ?></h2>
                            <?php if (!empty($news['published_at'])): ?>
                                <p class="small text-muted mb-1">
                                    <?= e(date('d.m.Y', strtotime($news['published_at']))) ?>
                                </p>
                            <?php endif; ?>
                            <p class="small text-muted mb-0"><?= e($news['summary'] ?? '') ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Henüz haber eklenmemiş.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

