<?php
require_once '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// GET all products
if ($method === 'GET' && !isset($_GET['id'])) {
    $result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    sendResponse(true, 'Products retrieved', $products);
}

// GET single product
if ($method === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendResponse(true, 'Product found', $result->fetch_assoc());
    } else {
        sendResponse(false, 'Product not found');
    }
}

// CREATE product
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = validateInput($input['name'] ?? '');
    $sku = validateInput($input['sku'] ?? '');
    $category = validateInput($input['category'] ?? 'General');
    $quantity = intval($input['quantity'] ?? 0);
    $price = floatval($input['price'] ?? 0);
    $threshold = intval($input['low_stock_threshold'] ?? 10);
    
    if (empty($name) || empty($sku)) {
        sendResponse(false, 'Name and SKU are required');
    }
    
    // Check if SKU exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ?");
    $stmt->bind_param("s", $sku);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        sendResponse(false, 'SKU already exists');
    }
    
    $stmt = $conn->prepare("INSERT INTO products (name, sku, category, quantity, price, low_stock_threshold) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssidi", $name, $sku, $category, $quantity, $price, $threshold);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Product created successfully', ['id' => $conn->insert_id]);
    } else {
        sendResponse(false, 'Failed to create product');
    }
}

// UPDATE product
if ($method === 'PUT' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = validateInput($input['name'] ?? '');
    $sku = validateInput($input['sku'] ?? '');
    $category = validateInput($input['category'] ?? 'General');
    $quantity = intval($input['quantity'] ?? 0);
    $price = floatval($input['price'] ?? 0);
    $threshold = intval($input['low_stock_threshold'] ?? 10);
    
    $stmt = $conn->prepare("UPDATE products SET name=?, sku=?, category=?, quantity=?, price=?, low_stock_threshold=? WHERE id=?");
    $stmt->bind_param("sssidii", $name, $sku, $category, $quantity, $price, $threshold, $id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Product updated successfully');
    } else {
        sendResponse(false, 'Failed to update product');
    }
}

// DELETE product
if ($method === 'DELETE' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Product deleted successfully');
    } else {
        sendResponse(false, 'Failed to delete product');
    }
}

sendResponse(false, 'Invalid request');
?>
