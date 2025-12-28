<?php
/**
 * Get Profile
 * Returns profile data for current user or provider
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Get type parameter
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'user';

try {
    $db = getConnection();
    
    if ($type === 'provider' && isset($_SESSION['provider_id'])) {
        $providerId = $_SESSION['provider_id'];
        
        $stmt = $db->prepare("
            SELECT 
                provider_id,
                full_name,
                email,
                phone,
                service_category_id,
                experience_years,
                hourly_rate,
                bio,
                rating,
                total_jobs,
                is_verified,
                created_at
            FROM providers
            WHERE provider_id = ?
        ");
        
        $stmt->execute([$providerId]);
        $profile = $stmt->fetch();
        
        if ($profile) {
            sendJsonResponse(true, 'Profile retrieved successfully', $profile);
        } else {
            sendJsonResponse(false, 'Profile not found');
        }
        
    } elseif ($type === 'user' && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        $stmt = $db->prepare("
            SELECT 
                user_id,
                full_name,
                email,
                phone,
                address,
                created_at
            FROM users
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        
        if ($profile) {
            sendJsonResponse(true, 'Profile retrieved successfully', $profile);
        } else {
            sendJsonResponse(false, 'Profile not found');
        }
        
    } else {
        sendJsonResponse(false, 'Not authenticated');
    }
    
} catch (PDOException $e) {
    error_log('Get profile error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error retrieving profile');
}
?>