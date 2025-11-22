<?php
require_once '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// GET all deliveries
if ($method === 'GET') {
    $query = "SELECT sm.*, p.name as product_name, p.sku 
              FROM stock_movements sm 
              JOIN products p ON sm.product_id = p.id 
              WHERE sm.type = 'delivery'
              ORDER BY sm.created_at DESC";
    
    $result = $conn->query($query);
    $deliveries = [];
    
    while ($row = $result->fetch_assoc()) {
        $deliveries[] = $row;
    }
    
    sendResponse(true, 'Deliveries retrieved', $deliveries);
}

// CREATE delivery
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $productId = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);
    $reference = validateInput($input['reference'] ?? '');
    
    if ($productId <= 0 || $quantity <= 0) {
        sendResponse(false, 'Valid product and quantity required');
    }
    
    // Check if enough stock
    $product = $conn->query("SELECT quantity FROM products WHERE id = $productId")->fetch_assoc();
    
    if (!$product) {
        sendResponse(false, 'Product not found');
    }
    
    if ($product['quantity'] < $quantity) {
        sendResponse(false, 'Insufficient stock. Available: ' . $product['quantity']);
    }
    
    // Update product quantity
    $conn->query("UPDATE products SET quantity = quantity - $quantity WHERE id = $productId");
    
    // Record movement
    $stmt = $conn->prepare("INSERT INTO stock_movements (product_id, type, quantity, reference) VALUES (?, 'delivery', ?, ?)");
    $stmt->bind_param("iis", $productId, $quantity, $reference);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Delivery added and stock updated');
    } else {
        sendResponse(false, 'Failed to add delivery');
    }
}

sendResponse(false, 'Invalid request');
?>
