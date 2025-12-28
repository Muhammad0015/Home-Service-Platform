<?php
/**
 * SQLite Database Connection
 * Home Service Booking Platform
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get database connection
 */
function getConnection() {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../data/database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Send JSON response
 */
function sendJsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Server-side validation functions
 */
function validateRequired($value, $fieldName) {
    if (empty(trim($value))) {
        return "$fieldName is required";
    }
    return null;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return null;
}

function validatePassword($password) {
    if (strlen($password) < 6) {
        return "Password must be at least 6 characters";
    }
    return null;
}

function validatePhone($phone) {
    if (!empty($phone) && !preg_match('/^[0-9\-\+\s\(\)]{7,20}$/', $phone)) {
        return "Invalid phone number format";
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['provider_id']);
}

/**
 * Check if user is a customer
 */
function isUser() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is a provider
 */
function isProvider() {
    return isset($_SESSION['provider_id']);
}

/**
 * Get current user/provider ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentProviderId() {
    return $_SESSION['provider_id'] ?? null;
}
?>