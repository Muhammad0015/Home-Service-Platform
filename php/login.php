<?php
/**
 * User/Provider Login
 * Handles authentication for both customers and service providers
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

// Get form data
$userType = sanitizeInput($_POST['userType'] ?? 'user');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Server-side validation
$errors = [];

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($password)) {
    $errors[] = 'Password is required';
}

if (!empty($errors)) {
    sendJsonResponse(false, implode(', ', $errors));
}

try {
    $db = getConnection();
    
    if ($userType === 'provider') {
        // Check providers table
        $stmt = $db->prepare("
            SELECT provider_id, full_name, email, password
            FROM providers
            WHERE email = ?
        ");
        
        $stmt->execute([$email]);
        $provider = $stmt->fetch();
        
        if (!$provider) {
            sendJsonResponse(false, 'Invalid email or password');
        }
        
        // Verify password
        if (!password_verify($password, $provider['password'])) {
            sendJsonResponse(false, 'Invalid email or password');
        }
        
        // Clear any existing session
        session_unset();
        
        // Set session variables
        $_SESSION['provider_id'] = $provider['provider_id'];
        $_SESSION['provider_name'] = $provider['full_name'];
        $_SESSION['provider_email'] = $provider['email'];
        
        sendJsonResponse(true, 'Login successful!', [
            'type' => 'provider',
            'id' => $provider['provider_id'],
            'name' => $provider['full_name']
        ]);
        
    } else {
        // Check users table
        $stmt = $db->prepare("
            SELECT user_id, full_name, email, password
            FROM users
            WHERE email = ?
        ");
        
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            sendJsonResponse(false, 'Invalid email or password');
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            sendJsonResponse(false, 'Invalid email or password');
        }
        
        // Clear any existing session
        session_unset();
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        
        sendJsonResponse(true, 'Login successful!', [
            'type' => 'user',
            'id' => $user['user_id'],
            'name' => $user['full_name']
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    sendJsonResponse(false, 'An error occurred during login. Please try again.');
}
?>