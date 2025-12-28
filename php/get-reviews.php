<?php
/**
 * Get Reviews
 * Returns reviews for current provider
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if provider is logged in
if (!isset($_SESSION['provider_id'])) {
    sendJsonResponse(false, 'Not authenticated');
}

$providerId = $_SESSION['provider_id'];

try {
    $db = getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            r.review_id,
            r.booking_id,
            r.user_id,
            r.provider_id,
            r.rating,
            r.comment,
            r.created_at,
            u.full_name AS customer_name
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.user_id
        WHERE r.provider_id = ?
        ORDER BY r.created_at DESC
    ");
    
    $stmt->execute([$providerId]);
    $reviews = $stmt->fetchAll();
    
    sendJsonResponse(true, 'Reviews retrieved successfully', $reviews);
    
} catch (PDOException $e) {
    error_log('Get reviews error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error retrieving reviews');
}
?>