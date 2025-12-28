<?php
/**
 * Book Service
 * Creates a new booking
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
    sendJsonResponse(false, 'Please login to book a service');
}

// Get form data
$userId = $_SESSION['user_id'];
$providerId = isset($_POST['provider_id']) ? (int)$_POST['provider_id'] : 0;
$categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$bookingDate = sanitizeInput($_POST['booking_date'] ?? '');
$bookingTime = sanitizeInput($_POST['booking_time'] ?? '');
$address = sanitizeInput($_POST['address'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$totalPrice = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;

// Server-side validation
$errors = [];

if (!$providerId) {
    $errors[] = 'Provider is required';
}

if (!$categoryId) {
    $errors[] = 'Service category is required';
}

if (empty($bookingDate)) {
    $errors[] = 'Booking date is required';
} else {
    // Validate date is not in the past
    $selectedDate = new DateTime($bookingDate);
    $today = new DateTime('today');
    
    if ($selectedDate < $today) {
        $errors[] = 'Booking date cannot be in the past';
    }
}

if (empty($bookingTime)) {
    $errors[] = 'Booking time is required';
}

if (empty($address)) {
    $errors[] = 'Service address is required';
}

if (!empty($errors)) {
    sendJsonResponse(false, implode(', ', $errors));
}

try {
    $db = getConnection();
    
    // Verify provider exists
    $stmt = $db->prepare("SELECT provider_id, hourly_rate FROM providers WHERE provider_id = ?");
    $stmt->execute([$providerId]);
    $provider = $stmt->fetch();
    
    if (!$provider) {
        sendJsonResponse(false, 'Provider not found');
    }
    
    // Use provider's hourly rate if total price not provided
    if (!$totalPrice) {
        $totalPrice = $provider['hourly_rate'];
    }
    
    // Insert booking
    $stmt = $db->prepare("
        INSERT INTO bookings (user_id, provider_id, category_id, booking_date, booking_time, address, description, total_price, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->execute([
        $userId,
        $providerId,
        $categoryId,
        $bookingDate,
        $bookingTime,
        $address,
        $description,
        $totalPrice
    ]);
    
    $bookingId = $db->lastInsertId();
    
    sendJsonResponse(true, 'Booking created successfully!', [
        'booking_id' => $bookingId
    ]);
    
} catch (PDOException $e) {
    error_log('Book service error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error creating booking. Please try again.');
}
?>