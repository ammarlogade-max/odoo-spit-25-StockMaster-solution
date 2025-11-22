<?php
require_once '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// GET all receipts
if ($method === 'GET') {
    $query = "SELECT sm.*, p.name as product_name, p.sku 
              FROM stock_movements sm 
              JOIN products p ON sm.product_id = p.id 
              WHERE sm.type = 'receipt'
              ORDER BY sm.created_at DESC";
    
    $result = $conn->query($query);
    $receipts = [];
    
    while ($row = $result->fetch_assoc()) {
        $receipts[] = $row;
    }
    
    sendResponse(true, 'Receipts retrieved', $receipts);
}

// CREATE receipt
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $productId = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);
    $reference = validateInput($input['reference'] ?? '');
    
    if ($productId <= 0 || $quantity <= 0) {
        sendResponse(false, 'Valid product and quantity required');
    }
    
    // Update product quantity
    $conn->query("UPDATE products SET quantity = quantity + $quantity WHERE id = $productId");
    
    // Record movement
    $stmt = $conn->prepare("INSERT INTO stock_movements (product_id, type, quantity, reference) VALUES (?, 'receipt', ?, ?)");
    $stmt->bind_param("iis", $productId, $quantity, $reference);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Receipt added and stock updated');
    } else {
        sendResponse(false, 'Failed to add receipt');
    }
}

sendResponse(false, 'Invalid request');
?>
