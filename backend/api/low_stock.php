<?php
require_once '../../db.php';

$conn = getDBConnection();

// Get low stock products
$result = $conn->query("SELECT * FROM products WHERE quantity <= low_stock_threshold ORDER BY quantity ASC");

$lowStockProducts = [];

while ($row = $result->fetch_assoc()) {
    $lowStockProducts[] = $row;
}

sendResponse(true, 'Low stock products retrieved', $lowStockProducts);
?>
