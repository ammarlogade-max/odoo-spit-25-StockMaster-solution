<?php
require_once '../../db.php';

$conn = getDBConnection();

// Get Dashboard KPIs
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];

$lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity <= low_stock_threshold")->fetch_assoc()['count'];

$totalValue = $conn->query("SELECT SUM(quantity * price) as total FROM products")->fetch_assoc()['total'];

$todayProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];

$recentMovements = [];
$result = $conn->query("SELECT sm.*, p.name as product_name FROM stock_movements sm 
                        JOIN products p ON sm.product_id = p.id 
                        ORDER BY sm.created_at DESC LIMIT 5");

while ($row = $result->fetch_assoc()) {
    $recentMovements[] = $row;
}

sendResponse(true, 'Dashboard data retrieved', [
    'total_products' => $totalProducts,
    'low_stock_count' => $lowStock,
    'total_value' => round($totalValue ?? 0, 2),
    'today_products' => $todayProducts,
    'recent_movements' => $recentMovements
]);
?>
