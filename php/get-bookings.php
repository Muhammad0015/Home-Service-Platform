<?php
/**
 * Get Bookings
 * Returns bookings for current user or provider
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Get type parameter
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'user';

try {
    $db = getConnection();
    
    if ($type === 'provider' && isset($_SESSION['provider_id'])) {
        // Get bookings for provider
        $providerId = $_SESSION['provider_id'];
        
        $stmt = $db->prepare("
            SELECT 
                b.booking_id,
                b.user_id,
                b.provider_id,
                b.category_id,
                b.booking_date,
                b.booking_time,
                b.address,
                b.description,
                b.status,
                b.total_price,
                b.created_at,
                u.full_name AS customer_name,
                u.phone AS customer_phone,
                u.email AS customer_email,
                c.category_name
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN service_categories c ON b.category_id = c.category_id
            WHERE b.provider_id = ?
            ORDER BY b.created_at DESC
        ");
        
        $stmt->execute([$providerId]);
        
    } elseif ($type === 'user' && isset($_SESSION['user_id'])) {
        // Get bookings for user
        $userId = $_SESSION['user_id'];
        
        $stmt = $db->prepare("
            SELECT 
                b.booking_id,
                b.user_id,
                b.provider_id,
                b.category_id,
                b.booking_date,
                b.booking_time,
                b.address,
                b.description,
                b.status,
                b.total_price,
                b.created_at,
                p.full_name AS provider_name,
                p.phone AS provider_phone,
                p.email AS provider_email,
                c.category_name,
                CASE WHEN r.review_id IS NOT NULL THEN 1 ELSE 0 END AS has_review
            FROM bookings b
            LEFT JOIN providers p ON b.provider_id = p.provider_id
            LEFT JOIN service_categories c ON b.category_id = c.category_id
            LEFT JOIN reviews r ON b.booking_id = r.booking_id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
        ");
        
        $stmt->execute([$userId]);
        
    } else {
        sendJsonResponse(false, 'Not authenticated');
    }
    
    $bookings = $stmt->fetchAll();
    
    sendJsonResponse(true, 'Bookings retrieved successfully', $bookings);
    
} catch (PDOException $e) {
    error_log('Get bookings error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error retrieving bookings');
}
?>