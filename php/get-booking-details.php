<?php
/**
 * Get Booking Details
 * Returns details for a specific booking
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) && !isset($_SESSION['provider_id'])) {
    sendJsonResponse(false, 'Not authenticated');
}

// Get booking ID
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$bookingId) {
    sendJsonResponse(false, 'Booking ID is required');
}

try {
    $db = getConnection();
    
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
            p.full_name AS provider_name,
            p.phone AS provider_phone,
            p.email AS provider_email,
            c.category_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.user_id
        LEFT JOIN providers p ON b.provider_id = p.provider_id
        LEFT JOIN service_categories c ON b.category_id = c.category_id
        WHERE b.booking_id = ?
    ");
    
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        sendJsonResponse(false, 'Booking not found');
    }
    
    // Verify access (user can only see their own bookings)
    if (isset($_SESSION['user_id']) && $booking['user_id'] != $_SESSION['user_id']) {
        // Check if it's a provider viewing their job
        if (!isset($_SESSION['provider_id']) || $booking['provider_id'] != $_SESSION['provider_id']) {
            sendJsonResponse(false, 'Access denied');
        }
    }
    
    if (isset($_SESSION['provider_id']) && $booking['provider_id'] != $_SESSION['provider_id']) {
        // Check if it's a user viewing their booking
        if (!isset($_SESSION['user_id']) || $booking['user_id'] != $_SESSION['user_id']) {
            sendJsonResponse(false, 'Access denied');
        }
    }
    
    sendJsonResponse(true, 'Booking retrieved successfully', $booking);
    
} catch (PDOException $e) {
    error_log('Get booking details error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error retrieving booking details');
}
?>