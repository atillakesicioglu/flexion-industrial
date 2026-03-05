<?php

require_once __DIR__ . '/functions.php';

$companyName  = get_setting('company_name', 'Flexion Industrial');
$address      = get_setting('company_address', 'Adres bilgisi');
$email        = get_setting('contact_email', 'info@example.com');
$phone        = get_setting('contact_phone', '+90 ... ... .. ..');
$footerText   = get_setting('footer_text', 'All rights reserved.');
$linkedin     = get_setting('social_linkedin', '');
$youtube      = get_setting('social_youtube', '');
?>
</main>

<footer class="fx-footer text-light mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5 class="text-white mb-3"><?= e($companyName) ?></h5>
                <p class="mb-1 small"><?= e($address) ?></p>
                <p class="mb-1 small">
                    <i class="bi bi-telephone me-1"></i><?= e($phone) ?>
                </p>
                <p class="mb-1 small">
                    <i class="bi bi-envelope me-1"></i><?= e($email) ?>
                </p>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white mb-3">Kataloglar & Belgeler</h6>
                <ul class="list-unstyled small">
                    <!-- İleride useful_documents tablosundan doldurulacak -->
                    <li><a href="#">Genel kataloğu indir</a></li>
                    <li><a href="#">Teknik dokümanlar</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white mb-3">Bizi Takip Edin</h6>
                <div class="d-flex gap-3 mb-3">
                    <?php if ($linkedin): ?>
                        <a href="<?= e($linkedin) ?>" target="_blank" rel="noopener">
                            <i class="bi bi-linkedin fs-4"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($youtube): ?>
                        <a href="<?= e($youtube) ?>" target="_blank" rel="noopener">
                            <i class="bi bi-youtube fs-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <p class="small mb-0">
                    <?= e(get_setting('newsletter_text', 'Yeni ürün ve projelerden haberdar olmak için bültenimize abone olun.')) ?>
                </p>
            </div>
        </div>
        <div class="fx-footer-bottom d-flex justify-content-between align-items-center">
            <span class="small"><?= e($footerText) ?></span>
            <span class="small text-muted">Designed for Flexion</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

