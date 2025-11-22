<?php
require_once '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// POST /login - User login
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = validateInput($input['username'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        sendResponse(false, 'Username and password are required');
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Invalid credentials');
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($password, $user['password_hash'])) {
        sendResponse(false, 'Invalid credentials');
    }
    
    // Remove password from response
    unset($user['password_hash']);
    
    sendResponse(true, 'Login successful', [
        'user' => $user,
        'token' => base64_encode($user['id'] . ':' . time()) // Simple token for demo
    ]);
}

// POST /signup - User registration
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'signup') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = validateInput($input['username'] ?? '');
    $email = validateInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        sendResponse(false, 'All fields are required');
    }
    
    if (strlen($username) < 3) {
        sendResponse(false, 'Username must be at least 3 characters');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Invalid email format');
    }
    
    if (strlen($password) < 6) {
        sendResponse(false, 'Password must be at least 6 characters');
    }
    
    if ($password !== $confirmPassword) {
        sendResponse(false, 'Passwords do not match');
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        sendResponse(false, 'Username or email already exists');
    }
    
    // Create user
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $passwordHash);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        sendResponse(true, 'Account created successfully', [
            'user' => [
                'id' => $userId,
                'username' => $username,
                'email' => $email
            ]
        ]);
    } else {
        sendResponse(false, 'Registration failed. Please try again');
    }
}

sendResponse(false, 'Invalid request');
?>
