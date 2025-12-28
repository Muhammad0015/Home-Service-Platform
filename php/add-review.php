<?php
/**
 * Add Review
 * Adds a review for a completed booking
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(false, 'Not authenticated');
}

$userId = $_SESSION['user_id'];

// Get form data
$bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$providerId = isset($_POST['provider_id']) ? (int)$_POST['provider_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = sanitizeInput($_POST['comment'] ?? '');

// Validate
$errors = [];

if (!$bookingId) {
    $errors[] = 'Booking ID is required';
}

if (!$providerId) {
    $errors[] = 'Provider ID is required';
}

if ($rating < 1 || $rating > 5) {
    $errors[] = 'Rating must be between 1 and 5';
}

if (!empty($errors)) {
    sendJsonResponse(false, implode(', ', $errors));
}

try {
    $db = getConnection();
    
    // Verify booking exists and belongs to user
    $stmt = $db->prepare("
        SELECT booking_id, status
        FROM bookings
        WHERE booking_id = ? AND user_id = ? AND provider_id = ?
    ");
    
    $stmt->execute([$bookingId, $userId, $providerId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        sendJsonResponse(false, 'Booking not found');
    }
    
    if ($booking['status'] !== 'completed') {
        sendJsonResponse(false, 'You can only review completed bookings');
    }
    
    // Check if review already exists
    $stmt = $db->prepare("SELECT review_id FROM reviews WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    
    if ($stmt->fetch()) {
        sendJsonResponse(false, 'You have already reviewed this booking');
    }
    
    // Insert review
    $stmt = $db->prepare("
        INSERT INTO reviews (booking_id, user_id, provider_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $bookingId,
        $userId,
        $providerId,
        $rating,
        $comment
    ]);
    
    // Update provider's average rating
    $stmt = $db->prepare("
        SELECT AVG(rating) AS avg_rating
        FROM reviews
        WHERE provider_id = ?
    ");
    
    $stmt->execute([$providerId]);
    $result = $stmt->fetch();
    
    $avgRating = round($result['avg_rating'], 2);
    
    $stmt = $db->prepare("UPDATE providers SET rating = ? WHERE provider_id = ?");
    $stmt->execute([$avgRating, $providerId]);
    
    sendJsonResponse(true, 'Review submitted successfully');
    
} catch (PDOException $e) {
    error_log('Add review error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error submitting review');
}
?>