<?php
/**
 * Update Profile
 * Updates profile data for current user or provider
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

// Get type
$type = isset($_POST['type']) ? sanitizeInput($_POST['type']) : 'user';

try {
    $db = getConnection();
    
    if ($type === 'provider' && isset($_SESSION['provider_id'])) {
        $providerId = $_SESSION['provider_id'];
        
        // Get form data
        $fullName = sanitizeInput($_POST['fullName'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $serviceCategory = isset($_POST['serviceCategory']) ? (int)$_POST['serviceCategory'] : null;
        $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : null;
        $hourlyRate = isset($_POST['hourlyRate']) ? (float)$_POST['hourlyRate'] : null;
        $bio = sanitizeInput($_POST['bio'] ?? '');
        
        // Validate
        if (empty($fullName)) {
            sendJsonResponse(false, 'Full name is required');
        }
        
        // Update profile
        $stmt = $db->prepare("
            UPDATE providers
            SET full_name = ?, phone = ?, service_category_id = ?, experience_years = ?, hourly_rate = ?, bio = ?
            WHERE provider_id = ?
        ");
        
        $stmt->execute([
            $fullName,
            $phone,
            $serviceCategory,
            $experience,
            $hourlyRate,
            $bio,
            $providerId
        ]);
        
        // Update session
        $_SESSION['provider_name'] = $fullName;
        
        sendJsonResponse(true, 'Profile updated successfully');
        
    } elseif ($type === 'user' && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        // Get form data
        $fullName = sanitizeInput($_POST['fullName'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        
        // Validate
        if (empty($fullName)) {
            sendJsonResponse(false, 'Full name is required');
        }
        
        // Update profile
        $stmt = $db->prepare("
            UPDATE users
            SET full_name = ?, phone = ?, address = ?
            WHERE user_id = ?
        ");
        
        $stmt->execute([
            $fullName,
            $phone,
            $address,
            $userId
        ]);
        
        // Update session
        $_SESSION['user_name'] = $fullName;
        
        sendJsonResponse(true, 'Profile updated successfully');
        
    } else {
        sendJsonResponse(false, 'Not authenticated');
    }
    
} catch (PDOException $e) {
    error_log('Update profile error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error updating profile');
}
?>