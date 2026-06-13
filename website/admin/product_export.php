<?php
/**
 * Geçici: ürün listesini JSON döndürür. Kulland?ktan sonra sil.
 * /admin/product_export.php?key=flexion_export_2026
 */
declare(strict_types=1);

if (($_GET['key'] ?? '') !== 'flexion_export_2026') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();

$total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$active = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();

$stmt = $pdo->query(
    'SELECT p.id, p.code, p.name, p.is_active, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY c.name, p.name'
);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$byCategory = [];
foreach ($products as $row) {
    $cat = $row['category_name'] ?: '(kategori yok)';
    $byCategory[$cat] = ($byCategory[$cat] ?? 0) + 1;
}
arsort($byCategory);

echo json_encode([
    'total'       => $total,
    'active'      => $active,
    'by_category' => $byCategory,
    'products'    => $products,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
