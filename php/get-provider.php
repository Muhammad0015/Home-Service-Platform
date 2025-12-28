<?php
/**
 * Get Single Provider
 * Returns details for a specific provider
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Get provider ID
$providerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$providerId) {
    sendJsonResponse(false, 'Provider ID is required');
}

try {
    $db = getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            p.provider_id,
            p.full_name,
            p.email,
            p.phone,
            p.service_category_id,
            p.experience_years,
            p.hourly_rate,
            p.bio,
            p.rating,
            p.total_jobs,
            p.is_verified,
            c.category_name
        FROM providers p
        LEFT JOIN service_categories c ON p.service_category_id = c.category_id
        WHERE p.provider_id = ?
    ");
    
    $stmt->execute([$providerId]);
    $provider = $stmt->fetch();
    
    if ($provider) {
        sendJsonResponse(true, 'Provider retrieved successfully', $provider);
    } else {
        sendJsonResponse(false, 'Provider not found');
    }
    
} catch (PDOException $e) {
    error_log('Get provider error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error retrieving provider');
}
?>