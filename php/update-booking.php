<?php
/**
 * Update Booking
 * Updates booking status (accept, complete, cancel)
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

// Check authentication
if (!isset($_SESSION['user_id']) && !isset($_SESSION['provider_id'])) {
    sendJsonResponse(false, 'Not authenticated');
}

// Get form data
$bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$status = sanitizeInput($_POST['status'] ?? '');

// Validate
if (!$bookingId) {
    sendJsonResponse(false, 'Booking ID is required');
}

$validStatuses = ['pending', 'accepted', 'completed', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    sendJsonResponse(false, 'Invalid status');
}

try {
    $db = getConnection();
    
    // Get booking details
    $stmt = $db->prepare("SELECT user_id, provider_id, status FROM bookings WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        sendJsonResponse(false, 'Booking not found');
    }
    
    // Verify access
    $canUpdate = false;
    
    // Users can cancel their own pending bookings
    if (isset($_SESSION['user_id']) && $booking['user_id'] == $_SESSION['user_id']) {
        if ($status === 'cancelled' && $booking['status'] === 'pending') {
            $canUpdate = true;
        }
    }
    
    // Providers can accept, complete, or decline their jobs
    if (isset($_SESSION['provider_id']) && $booking['provider_id'] == $_SESSION['provider_id']) {
        if ($status === 'accepted' && $booking['status'] === 'pending') {
            $canUpdate = true;
        } elseif ($status === 'completed' && $booking['status'] === 'accepted') {
            $canUpdate = true;
        } elseif ($status === 'cancelled' && $booking['status'] === 'pending') {
            $canUpdate = true;
        }
    }
    
    if (!$canUpdate) {
        sendJsonResponse(false, 'You do not have permission to update this booking');
    }
    
    // Update booking status
    $stmt = $db->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $stmt->execute([$status, $bookingId]);
    
    // If completed, update provider's total jobs
    if ($status === 'completed') {
        $stmt = $db->prepare("UPDATE providers SET total_jobs = total_jobs + 1 WHERE provider_id = ?");
        $stmt->execute([$booking['provider_id']]);
    }
    
    sendJsonResponse(true, 'Booking updated successfully');
    
} catch (PDOException $e) {
    error_log('Update booking error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error updating booking');
}
?>