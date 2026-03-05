<?php

require_once __DIR__ . '/../includes/auth.php';

require_admin_login();

$pdo = db();
$error = null;
$success = null;

// Yeni / güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!verify_csrf_token($token)) {
        $error = 'Güvenlik doğrulaması başarısız. Lütfen formu tekrar deneyin.';
    } else {
        if (isset($_POST['save_section'])) {
            $id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $sectionType = trim($_POST['section_type'] ?? '');
            $title       = trim($_POST['title'] ?? '');
            $isActive    = isset($_POST['is_active']) ? 1 : 0;

            $content = [
                'eyebrow'     => $_POST['eyebrow'] ?? null,
                'subtitle'    => $_POST['subtitle'] ?? null,
                'button_text' => $_POST['button_text'] ?? null,
                'button_url'  => $_POST['button_url'] ?? null,
                'image'       => $_POST['image'] ?? null,
                'text'        => $_POST['text'] ?? null,
            ];

            $json = json_encode($content, JSON_UNESCAPED_UNICODE);

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE home_sections SET section_type = :type, title = :title, content_json = :content, is_active = :active WHERE id = :id');
                $stmt->execute([
                    ':type'    => $sectionType,
                    ':title'   => $title,
                    ':content' => $json,
                    ':active'  => $isActive,
                    ':id'      => $id,
                ]);
                $success = 'Blok güncellendi.';
            } else {
                $sort   = (int) $pdo->query('SELECT IFNULL(MAX(sort_order),0)+1 FROM home_sections')->fetchColumn();
                $stmt = $pdo->prepare('INSERT INTO home_sections (section_type, title, content_json, sort_order, is_active) VALUES (:type, :title, :content, :sort, :active)');
                $stmt->execute([
                    ':type'    => $sectionType,
                    ':title'   => $title,
                    ':content' => $json,
                    ':sort'    => $sort,
                    ':active'  => $isActive,
                ]);
                $success = 'Yeni blok eklendi.';
            }
        } elseif (isset($_POST['delete_section'])) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM home_sections WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $success = 'Blok silindi.';
            }
        } elseif (isset($_POST['save_order'])) {
            $ids = $_POST['order'] ?? [];
            $i   = 1;
            $stmt = $pdo->prepare('UPDATE home_sections SET sort_order = :sort WHERE id = :id');
            foreach ($ids as $id) {
                $stmt->execute([
                    ':sort' => $i++,
                    ':id'   => (int) $id,
                ]);
            }
            $success = 'Sıralama güncellendi.';
        }
    }
}

// Liste
$sections = [];
$stmt = $pdo->query('SELECT * FROM home_sections ORDER BY sort_order ASC, id ASC');
foreach ($stmt as $row) {
    $row['content'] = [];
    if (!empty($row['content_json'])) {
        $data = json_decode($row['content_json'], true);
        if (is_array($data)) {
            $row['content'] = $data;
        }
    }
    $sections[] = $row;
}

$token = csrf_token();

include __DIR__ . '/partials_header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Ana Sayfa Blokları</strong>
                <small class="text-muted">Sürükle-bırak ile sıralayabilirsin.</small>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success py-2"><?= e($success) ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                    <input type="hidden" name="save_order" value="1">
                    <ul class="list-group" id="home-sections-list">
                        <?php foreach ($sections as $section): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between" data-id="<?= e((string) $section['id']) ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="drag-handle text-muted" style="cursor:grab;">
                                        <i class="bi bi-grip-vertical"></i>
                                    </span>
                                    <div>
                                        <div class="small text-uppercase text-muted"><?= e($section['section_type']) ?></div>
                                        <div><?= e($section['title'] ?? '') ?></div>
                                        <?php if (!empty($section['is_active']) === 0): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">Pasif</span>
                                        <?php elseif (!(bool)$section['is_active']): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">Pasif</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="?edit=<?= e((string) $section['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                        Düzenle
                                    </a>
                                    <button type="submit" name="delete_section" value="1" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Bu bloğu silmek istediğinize emin misiniz?')">
                                        <input type="hidden" name="id" value="<?= e((string) $section['id']) ?>">
                                        Sil
                                    </button>
                                    <input type="hidden" name="order[]" value="<?= e((string) $section['id']) ?>">
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Sıralamayı Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <?php
        $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editSection = null;
        if ($editId) {
            foreach ($sections as $sec) {
                if ((int) $sec['id'] === $editId) {
                    $editSection = $sec;
                    break;
                }
            }
        }
        $content = $editSection['content'] ?? [];
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <strong><?= $editSection ? 'Bloğu Düzenle' : 'Yeni Blok Ekle' ?></strong>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                    <input type="hidden" name="save_section" value="1">
                    <?php if ($editSection): ?>
                        <input type="hidden" name="id" value="<?= e((string) $editSection['id']) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Blok Tipi</label>
                        <select name="section_type" class="form-select" required>
                            <?php
                            $types = [
                                'hero'       => 'Hero Banner',
                                'sectors'    => 'Sektör Grid',
                                'text_image' => 'Metin + Görsel',
                                'news'       => 'Haberler Bloğu',
                            ];
                            $currentType = $editSection['section_type'] ?? 'hero';
                            foreach ($types as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $currentType === $key ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" name="title" class="form-control"
                               value="<?= e($editSection['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Üst Küçük Başlık (eyebrow)</label>
                        <input type="text" name="eyebrow" class="form-control"
                               value="<?= e($content['eyebrow'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alt Başlık / Kısa Açıklama</label>
                        <textarea name="subtitle" class="form-control" rows="2"><?= e($content['subtitle'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Görsel URL</label>
                        <input type="text" name="image" class="form-control"
                               value="<?= e($content['image'] ?? '') ?>">
                        <div class="form-text">Örn: assets/uploads/hero.jpg</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metin (metin/görsel bloklar için)</label>
                        <textarea name="text" class="form-control" rows="3"><?= e($content['text'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buton Metni</label>
                        <input type="text" name="button_text" class="form-control"
                               value="<?= e($content['button_text'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buton Linki</label>
                        <input type="text" name="button_url" class="form-control"
                               value="<?= e($content['button_url'] ?? '') ?>">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                            <?= !isset($editSection['is_active']) || (int)($editSection['is_active']) === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Bu blok aktif olsun
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= $editSection ? 'Güncelle' : 'Ekle' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="../assets/js/admin-homepage.js"></script>

