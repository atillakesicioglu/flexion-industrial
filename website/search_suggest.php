<?php
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$pdo = db();
$like = '%' . $q . '%';
$items = [];

try {
    $stmt = $pdo->prepare(
        'SELECT p.id, p.main_image,
                COALESCE(NULLIF(pt.name, \'\'), p.name) AS name,
                COALESCE(NULLIF(pt.slug, \'\'), p.slug) AS slug
         FROM products p
         LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.language = :lang
         WHERE p.is_active = 1
           AND (
                p.name LIKE :q1 OR p.code LIKE :q2 OR p.short_description LIKE :q3
                OR pt.name LIKE :q4 OR pt.short_description LIKE :q5
           )
         ORDER BY name ASC
         LIMIT 5'
    );
    $stmt->execute([
        ':lang' => CURRENT_LANG,
        ':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like, ':q5' => $like,
    ]);
    foreach (($stmt->fetchAll() ?: []) as $row) {
        $slug  = (string)($row['slug'] ?? '');
        $img   = !empty($row['main_image']) ? asset_url((string)$row['main_image']) : '';
        $items[] = [
            'title' => (string)($row['name'] ?? ''),
            'url'   => $slug !== '' ? product_url($slug) : 'product?id=' . (int)($row['id'] ?? 0),
            'image' => $img,
        ];
    }
} catch (Throwable $e) {
    // sessiz
}

echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
