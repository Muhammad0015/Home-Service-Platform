<?php
/**
 * User/Provider Registration
 * Handles both customer and service provider registration
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
$fullName = sanitizeInput($_POST['fullName'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Server-side validation
$errors = [];

// Validate required fields
if (empty($fullName)) {
    $errors[] = 'Full name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

// Provider-specific validation
if ($userType === 'provider') {
    $serviceCategory = sanitizeInput($_POST['serviceCategory'] ?? '');
    $experience = sanitizeInput($_POST['experience'] ?? '');
    $hourlyRate = sanitizeInput($_POST['hourlyRate'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    
    if (empty($serviceCategory)) {
        $errors[] = 'Service category is required';
    }
    
    if ($experience === '' || !is_numeric($experience)) {
        $errors[] = 'Valid experience years is required';
    }
    
    if (empty($hourlyRate) || !is_numeric($hourlyRate)) {
        $errors[] = 'Valid hourly rate is required';
    }
}

// User-specific data
if ($userType === 'user') {
    $address = sanitizeInput($_POST['address'] ?? '');
}

// If validation errors exist, return them
if (!empty($errors)) {
    sendJsonResponse(false, implode(', ', $errors));
}

try {
    $db = getConnection();
    
    // Check if email already exists
    if ($userType === 'provider') {
        $stmt = $db->prepare("SELECT provider_id FROM providers WHERE email = ?");
    } else {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    }
    
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        sendJsonResponse(false, 'Email already registered. Please use a different email or login.');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new record
    if ($userType === 'provider') {
        $stmt = $db->prepare("
            INSERT INTO providers (full_name, email, password, phone, service_category_id, experience_years, hourly_rate, bio)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $fullName,
            $email,
            $hashedPassword,
            $phone,
            $serviceCategory,
            $experience,
            $hourlyRate,
            $bio
        ]);
        
        $newId = $db->lastInsertId();
        
        // Start session and log in
        $_SESSION['provider_id'] = $newId;
        $_SESSION['provider_name'] = $fullName;
        $_SESSION['provider_email'] = $email;
        
        sendJsonResponse(true, 'Registration successful! Welcome to HomeServe.', [
            'type' => 'provider',
            'id' => $newId,
            'name' => $fullName
        ]);
        
    } else {
        $stmt = $db->prepare("
            INSERT INTO users (full_name, email, password, phone, address)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $fullName,
            $email,
            $hashedPassword,
            $phone,
            $address ?? ''
        ]);
        
        $newId = $db->lastInsertId();
        
        // Start session and log in
        $_SESSION['user_id'] = $newId;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_email'] = $email;
        
        sendJsonResponse(true, 'Registration successful! Welcome to HomeServe.', [
            'type' => 'user',
            'id' => $newId,
            'name' => $fullName
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Registration error: ' . $e->getMessage());
    sendJsonResponse(false, 'An error occurred during registration. Please try again.');
}
?>