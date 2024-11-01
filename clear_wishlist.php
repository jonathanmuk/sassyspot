<?php
session_start();
require_once 'db_connect.php';

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($userId) {
    // For logged-in users
    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ?");
    $stmt->execute([$userId]);
} else {
    // For guest users
    $_SESSION['guest_wishlist'] = [];
}

// Reset wishlist count
$_SESSION['wishlist_count'] = 0;

// Fetch new related products
$newRelatedProducts = fetchNewRelatedProducts($userId);

echo json_encode(['success' => true, 'wishlistCount' => 0, 'newRelatedProducts' => $newRelatedProducts]);

function fetchNewRelatedProducts($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE id NOT IN (SELECT product_id FROM wishlists WHERE user_id = ?)
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
