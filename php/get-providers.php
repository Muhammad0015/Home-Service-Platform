<?php
/**
 * Get Providers
 * Returns list of service providers with optional filtering
 */

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Get filter parameters
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$rating = isset($_GET['rating']) ? (float)$_GET['rating'] : null;
$price = isset($_GET['price']) ? sanitizeInput($_GET['price']) : null;

try {
    $db = getConnection();
    
    // Build query
    $sql = "
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
        WHERE 1=1
    ";
    
    $params = [];
    
    // Apply category filter
    if ($category) {
        $sql .= " AND p.service_category_id = ?";
        $params[] = $category;
    }
    
    // Apply rating filter
    if ($rating) {
        $sql .= " AND p.rating >= ?";
        $params[] = $rating;
    }
    
    // Apply price filter
    if ($price) {
        $priceRange = explode('-', $price);
        if (count($priceRange) === 2) {
            $sql .= " AND p.hourly_rate >= ? AND p.hourly_rate <= ?";
            $params[] = (float)$priceRange[0];
            $params[] = (float)$priceRange[1];
        } elseif (strpos($price, '+') !== false) {
            $minPrice = (float)str_replace('+', '', $price);
            $sql .= " AND p.hourly_rate >= ?";
            $params[] = $minPrice;
        }
    }
    
    // Order by rating
    $sql .= " ORDER BY p.rating DESC, p.total_jobs DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $providers = $stmt->fetchAll();
    
    if (count($providers) > 0) {
        sendJsonResponse(true, 'Providers retrieved successfully', $providers);
    } else {
        sendJsonResponse(true, 'No providers found', []);
    }
    
} catch (PDOException $e) {
    error_log('Get providers error: ' . $e->getMessage());
    sendJsonResponse(false, 'Error retrieving providers');
}
?>