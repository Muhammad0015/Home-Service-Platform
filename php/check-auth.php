<?php
/**
 * Authentication Check
 * Returns current user/provider session info
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    sendJsonResponse(true, 'Authenticated', [
        'type' => 'user',
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email']
    ]);
}

// Check if provider is logged in
if (isset($_SESSION['provider_id'])) {
    sendJsonResponse(true, 'Authenticated', [
        'type' => 'provider',
        'id' => $_SESSION['provider_id'],
        'name' => $_SESSION['provider_name'],
        'email' => $_SESSION['provider_email']
    ]);
}

// Not authenticated
sendJsonResponse(false, 'Not authenticated');
?>