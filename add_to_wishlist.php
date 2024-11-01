<?php
session_start();
require_once 'db_connect.php';

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($productId > 0) {
    if ($userId) {
        // For logged-in users
        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
    } else {
        // For guest users
        if (!isset($_SESSION['guest_wishlist'])) {
            $_SESSION['guest_wishlist'] = [];
        }
        if (!in_array($productId, $_SESSION['guest_wishlist'])) {
            $_SESSION['guest_wishlist'][] = $productId;
        }
    }

    // Get updated wishlist count
    if ($userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
        $stmt->execute([$userId]);
        $wishlistCount = $stmt->fetchColumn();
    } else {
        $wishlistCount = count($_SESSION['guest_wishlist']);
    }

    $_SESSION['wishlist_count'] = $wishlistCount;

    // Fetch the newly added wishlist item
    $newWishlistItem = fetchWishlistItem($productId);

    // Fetch a new related product
    $newRelatedProduct = fetchNewRelatedProduct($userId, $productId);

    echo json_encode([
        'success' => true, 
        'wishlistCount' => $wishlistCount, 
        'newWishlistItem' => $newWishlistItem,
        'newRelatedProduct' => $newRelatedProduct
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
}

function fetchWishlistItem($productId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchNewRelatedProduct($userId, $addedProductId) {
    global $pdo;
    
    // Get the category of the added product
    $stmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
    $stmt->execute([$addedProductId]);
    $categoryId = $stmt->fetchColumn();

    // Fetch a new related product
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = ? 
        AND id NOT IN (SELECT product_id FROM wishlists WHERE user_id = ?)
        AND id != ?
        ORDER BY RAND()
        LIMIT 1
    ");
    $stmt->execute([$categoryId, $userId, $addedProductId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
