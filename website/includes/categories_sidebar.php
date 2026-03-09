<?php
/**
 * Kategori sidebar accordion partial'ı.
 *
 * Gereken değişkenler (çağıran sayfa tarafından tanımlanmalı):
 *   $categoriesTree  — get_categories_tree() çıktısı (array)
 *   $activeCategoryId — (int|null) Vurgulanacak aktif kategori ID'si; yoksa 0
 */

$activeCategoryId = (int) ($activeCategoryId ?? 0);
?>
<h2 class="h6 text-uppercase text-muted mb-3">Sektörler</h2>
<div class="fx-cat-accordion">
    <?php foreach ($categoriesTree as $cat):
        $cid         = (int) $cat['id'];
        $hasChildren = !empty($cat['children']);
        $accId       = 'fx-cat-' . $cid;

        // Bu item aktif mi veya aktif kategorinin ebeveyni mi?
        $isOpen = ($activeCategoryId > 0 && $cid === $activeCategoryId);
        if (!$isOpen && $hasChildren && $activeCategoryId > 0) {
            foreach ($cat['children'] as $ch) {
                if ((int) $ch['id'] === $activeCategoryId) {
                    $isOpen = true;
                    break;
                }
            }
        }
    ?>
    <div class="fx-cat-item">
        <?php if ($hasChildren): ?>
            <button class="fx-cat-btn<?= $isOpen ? ' fx-cat-active' : '' ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#<?= $accId ?>"
                    aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
                    aria-controls="<?= $accId ?>">
                <span><?= e($cat['name']) ?></span>
                <i class="bi bi-chevron-down fx-cat-chevron"></i>
            </button>
            <div class="collapse<?= $isOpen ? ' show' : '' ?>" id="<?= $accId ?>">
                <div class="fx-cat-children">
                    <?php foreach ($cat['children'] as $child): ?>
                        <a href="category?id=<?= (int) $child['id'] ?>"
                           class="fx-cat-child-link<?= (int) $child['id'] === $activeCategoryId ? ' fx-cat-child-active' : '' ?>">
                            <?= e($child['name']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="category?id=<?= $cid ?>"
                       class="fx-cat-child-link fx-cat-all-link">
                        <i class="bi bi-grid-3x3-gap me-1"></i>Tümünü gör
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="category?id=<?= $cid ?>"
               class="fx-cat-btn text-decoration-none<?= $isOpen ? ' fx-cat-active' : '' ?>">
                <span><?= e($cat['name']) ?></span>
                <i class="bi bi-chevron-right fx-cat-chevron" style="transform:none;"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
