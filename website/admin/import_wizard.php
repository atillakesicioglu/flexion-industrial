<?php
// Import Wizard - Excel Product Import
// DELETE THIS FILE after import is complete!
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin_login();

$pdo = db();

$DATA_DIR  = __DIR__ . '/import_data';
$IMG_DIR   = $DATA_DIR . '/images';
$ICON_DIR  = $DATA_DIR . '/icons';
$PROD_DEST = __DIR__ . '/../assets/uploads/products/';
$ICON_DEST = __DIR__ . '/../assets/uploads/catalog/';

// Category mapping: Excel value (uppercase) => [slug, name]
$CAT_MAP = [
    'WATER HOSES'             => ['water-hoses',                  'Water Hoses'],
    'AIR-GAS'                 => ['air-gas-hoses',                'Air-Gas Hoses'],
    'AIR - GAS HOSES'         => ['air-gas-hoses',                'Air-Gas Hoses'],
    'OIL & PETROLEUM'         => ['oil-petroleum-hoses',          'Oil & Petroleum Hoses'],
    'WELDING'                 => ['welding-hoses',                'Welding Hoses'],
    'FOOD'                    => ['food-hoses',                   'Food Hoses'],
    'FOOD & BEVERAGE HOSES'   => ['food-hoses',                   'Food Hoses'],
    'MATERIAL HANDLING'       => ['material-handling-hoses',      'Material Handling Hoses'],
    'SEWER & ROAD CLEANING'   => ['sewer-cleaning-hoses',         'Sewer Cleaning Hoses'],
    'SEWER JETTING & CLEANING HOSES' => ['sewer-cleaning-hoses',  'Sewer Cleaning Hoses'],
    'STEAM'                   => ['steam-hoses',                  'Steam Hoses'],
    'CHEMICAL'                => ['chemical-hoses',               'Chemical Hoses'],
    'HYDRAULIC HOSES'         => ['hydraulic-hoses',              'Hydraulic Hoses'],
    'HYDRAULIK HOSES'         => ['hydraulic-hoses',              'Hydraulic Hoses'],
    'ABRASIVE FOOD'           => ['abrasive-food-hoses',          'Abrasive Food Hoses'],
    "ALIZE 200\xC2\xB0C"      => ['alize-200',                   "Alize 200\xC2\xB0C Hoses"],
    "ALIZE SD 200\xC2\xB0C"   => ['alize-sd-200',                "Alize SD 200\xC2\xB0C Hoses"],
    'CABLE PROTECTION'        => ['cable-protection-hoses',       'Cable Protection Hoses'],
    'FIRE FIGHTING'           => ['fire-fighting-hoses',          'Fire Fighting Hoses'],
    'HOT WATER'               => ['hot-water-hoses',              'Hot Water Hoses'],
    'HOT AIR BLOWER HOSES'    => ['hot-air-blower-hoses',         'Hot Air Blower Hoses'],
    'OFFSHORE'                => ['offshore-hoses',               'Offshore Hoses'],
    'PETROLEUM DISPENSING'    => ['petroleum-dispensing-hoses',   'Petroleum Dispensing Hoses'],
    'PVC HOSES'               => ['pvc-hoses',                   'PVC Hoses'],
    'SILICONE'                => ['silicone-hoses',               'Silicone Hoses'],
    'STEEL MILL'              => ['steel-mill-hoses',             'Steel Mill Hoses'],
    'THERMOPLASTIC HOSES'     => ['thermoplastic-hoses',          'Thermoplastic Hoses'],
];

// ?? Helpers ??????????????????????????????????????????????????????????

function get_or_create_cat(PDO $pdo, string $excel_cat, array $cat_map): ?int {
    $key = mb_strtoupper(trim($excel_cat), 'UTF-8');
    if (!isset($cat_map[$key])) return null;
    [$slug, $name] = $cat_map[$key];
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;
    $sort = (int)$pdo->query('SELECT IFNULL(MAX(sort_order),0)+1 FROM categories')->fetchColumn();
    $pdo->prepare('INSERT INTO categories (parent_id,name,slug,sort_order,is_active) VALUES (NULL,?,?,?,1)')
        ->execute([$name, $slug, $sort]);
    return (int)$pdo->lastInsertId();
}

function unique_prod_slug(PDO $pdo, string $name): string {
    $slug = mb_strtolower($name, 'UTF-8');
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    $slug = trim($slug, '-') ?: bin2hex(random_bytes(4));
    $base = $slug; $i = 1;
    while (true) {
        $s = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
        $s->execute([$slug]);
        if (!$s->fetchColumn()) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

function copy_img(string $src, string $destDir): ?string {
    if (!is_file($src)) return null;
    @mkdir($destDir, 0755, true);
    $ext  = strtolower(pathinfo($src, PATHINFO_EXTENSION)) ?: 'jpg';
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    copy($src, rtrim($destDir, '/') . '/' . $name);
    return $name;
}

// ?? Load data ????????????????????????????????????????????????????????
$json_file = $DATA_DIR . '/products.json';
if (!is_file($json_file)) {
    die('<p style="color:red">products.json not found: ' . e($json_file) . '</p>');
}
$products = json_decode(file_get_contents($json_file), true);

$action  = $_POST['action'] ?? 'preview';
$dry_run = ($action !== 'execute');

$results     = [];
$counts      = ['inserted' => 0, 'skipped' => 0, 'no_cat' => 0, 'no_name' => 0];
$icon_results = [];

if (!$dry_run) $pdo->beginTransaction();

try {
    foreach ($products as $p) {
        $code    = trim($p['code'] ?? '');
        $name    = trim($p['name'] ?? '');
        $cat_raw = trim($p['category_raw'] ?? '');
        $row_num = (int)($p['row'] ?? 0);

        if (!$name) {
            $counts['no_name']++;
            $results[] = ['status'=>'no_name','row'=>$row_num,'code'=>$code,'name'=>$name,'cat'=>''];
            continue;
        }

        // Skip if code already exists
        if ($code) {
            $s = $pdo->prepare('SELECT id FROM products WHERE code = ?');
            $s->execute([$code]);
            if ($s->fetchColumn()) {
                $counts['skipped']++;
                $results[] = ['status'=>'skip','row'=>$row_num,'code'=>$code,'name'=>$name,'cat'=>''];
                continue;
            }
        }

        // Category
        $cat_key   = mb_strtoupper(trim($cat_raw), 'UTF-8');
        $cat_info  = $CAT_MAP[$cat_key] ?? null;
        if (!$cat_info) {
            $counts['no_cat']++;
            $results[] = ['status'=>'no_cat','row'=>$row_num,'code'=>$code,'name'=>$name,'cat'=>$cat_raw];
            continue;
        }
        $cat_name = $cat_info[1];

        if ($dry_run) {
            $cat_id  = 0;
            $prod_id = null;
            $img_db  = $p['main_image_file'] ? '[IMG]' : null;
        } else {
            $cat_id = get_or_create_cat($pdo, $cat_raw, $CAT_MAP);
            // Main image
            $img_db = null;
            if (!empty($p['main_image_file'])) {
                $src = $IMG_DIR . '/' . $p['main_image_file'];
                $fname = copy_img($src, $PROD_DEST);
                if ($fname) $img_db = 'assets/uploads/products/' . $fname;
            }
            // Insert product
            $slug = unique_prod_slug($pdo, $name);
            $sort = (int)$pdo->query('SELECT IFNULL(MAX(sort_order),0)+1 FROM products')->fetchColumn();
            $pdo->prepare(
                'INSERT INTO products (category_id,name,slug,code,description,main_image,sort_order,is_active)
                 VALUES (?,?,?,?,?,?,?,1)'
            )->execute([$cat_id,$name,$slug,$code?:null,$p['html_description']?:null,$img_db,$sort]);
            $prod_id = (int)$pdo->lastInsertId();
        }

        $counts['inserted']++;
        $results[] = [
            'status'     => 'ok',
            'row'        => $row_num,
            'code'       => $code,
            'name'       => $name,
            'cat'        => $cat_name,
            'has_img'    => !empty($p['main_image_file']),
            'icon_cnt'   => count($p['icon_files'] ?? []),
            'prod_id'    => $prod_id,
        ];
    }

    if (!$dry_run) $pdo->commit();
} catch (Throwable $e) {
    if (!$dry_run) $pdo->rollBack();
    die('<p style="color:red">ERROR: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

// ?? Icon import (after commit) ???????????????????????????????????????
if (!$dry_run) {
    $row_to_pid = [];
    foreach ($results as $r) {
        if ($r['status'] === 'ok' && $r['prod_id']) {
            $row_to_pid[(int)$r['row']] = (int)$r['prod_id'];
        }
    }
    foreach ($products as $p) {
        $prod_id = $row_to_pid[(int)($p['row'] ?? 0)] ?? null;
        if (!$prod_id) continue;
        $icon_sort = 1;
        foreach ($p['icon_files'] ?? [] as $icon_file) {
            $src = $ICON_DIR . '/' . $icon_file;
            if (!is_file($src)) continue;
            $label = pathinfo($icon_file, PATHINFO_FILENAME);
            // Check if icon exists in library
            $s = $pdo->prepare('SELECT id FROM catalog_product_icons WHERE admin_label = ?');
            $s->execute([$label]);
            $icon_id = $s->fetchColumn();
            if (!$icon_id) {
                @mkdir($ICON_DEST, 0755, true);
                $fname = copy_img($src, $ICON_DEST);
                if (!$fname) continue;
                $isort = (int)$pdo->query('SELECT IFNULL(MAX(sort_order),0)+1 FROM catalog_product_icons')->fetchColumn();
                $pdo->prepare('INSERT INTO catalog_product_icons (image_path,admin_label,sort_order,is_active) VALUES (?,?,?,1)')
                    ->execute(['assets/uploads/catalog/' . $fname, $label, $isort]);
                $icon_id = (int)$pdo->lastInsertId();
            }
            // Link to product
            $c = $pdo->prepare('SELECT id FROM product_icon_picks WHERE product_id=? AND icon_id=?');
            $c->execute([$prod_id,$icon_id]);
            if (!$c->fetchColumn()) {
                $pdo->prepare('INSERT INTO product_icon_picks (product_id,icon_id,sort_order) VALUES (?,?,?)')
                    ->execute([$prod_id,$icon_id,$icon_sort++]);
                $icon_results[] = "Product #$prod_id <- icon #$icon_id ($label)";
            }
        }
    }
}

// ?? HTML output ??????????????????????????????????????????????????????
$title = $dry_run ? 'Import Preview (Dry-Run)' : 'Import Complete';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<h2><?= htmlspecialchars($title) ?></h2>
<div class="d-flex gap-2 mb-3">
  <span class="badge bg-success fs-6">Will insert / Inserted: <?= $counts['inserted'] ?></span>
  <span class="badge bg-secondary fs-6">Skipped (exists): <?= $counts['skipped'] ?></span>
  <span class="badge bg-warning text-dark fs-6">No category: <?= $counts['no_cat'] ?></span>
  <span class="badge bg-danger fs-6">No name: <?= $counts['no_name'] ?></span>
</div>

<?php if ($dry_run): ?>
<form method="post" class="mb-4">
  <input type="hidden" name="action" value="execute">
  <button type="submit" class="btn btn-danger btn-lg"
    onclick="return confirm('<?= $counts['inserted'] ?> products will be inserted. Continue?')">
    Run Import (<?= $counts['inserted'] ?> products)
  </button>
</form>
<?php else: ?>
<div class="alert alert-success"><strong>Import complete!</strong>
  <?= $counts['inserted'] ?> products inserted, <?= count($icon_results) ?> icon links created.</div>
<div class="alert alert-warning">You may now delete: <code>website/admin/import_wizard.php</code> and <code>website/admin/import_data/</code></div>
<?php endif; ?>

<table class="table table-sm table-bordered small">
<thead><tr>
  <th>Status</th><th>Row</th><th>Code</th><th>Product Name</th><th>Category</th><th>Image</th><th>Icons</th>
</tr></thead>
<tbody>
<?php foreach ($results as $r):
  $b = match($r['status']) { 'ok'=>'success', 'skip'=>'secondary', 'no_name'=>'warning', 'no_cat'=>'danger', default=>'light' };
  $l = match($r['status']) { 'ok'=> ($dry_run?'Will insert':'Inserted'), 'skip'=>'Exists', 'no_name'=>'No name', 'no_cat'=>'No cat', default=>$r['status'] };
?>
<tr>
  <td><span class="badge bg-<?= $b ?>"><?= $l ?></span></td>
  <td><?= (int)$r['row'] ?></td>
  <td><?= htmlspecialchars($r['code']) ?></td>
  <td><?= htmlspecialchars($r['name']) ?></td>
  <td><?= htmlspecialchars($r['cat']) ?></td>
  <td><?= ($r['has_img'] ?? false) ? '&#10004;' : '-' ?></td>
  <td><?= (int)($r['icon_cnt'] ?? 0) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php if (!empty($icon_results)): ?>
<h4>Icon Links Created</h4>
<ul class="small"><?php foreach ($icon_results as $ir): ?>
  <li><?= htmlspecialchars($ir) ?></li>
<?php endforeach; ?></ul>
<?php endif; ?>
</body>
</html>
