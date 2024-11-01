<?php
session_start();
require_once 'db_connect.php';

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($productId > 0) {
    if ($userId) {
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
    } else {
        if (isset($_SESSION['guest_wishlist'])) {
            $_SESSION['guest_wishlist'] = array_diff($_SESSION['guest_wishlist'], [$productId]);
        }
    }

    // Get updated wishlist count
    if ($userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
        $stmt->execute([$userId]);
        $wishlistCount = $stmt->fetchColumn();
    } else {
        $wishlistCount = isset($_SESSION['guest_wishlist']) ? count($_SESSION['guest_wishlist']) : 0;
    }

    $_SESSION['wishlist_count'] = $wishlistCount;

    // Fetch a new related product
    $newRelatedProduct = fetchNewRelatedProduct($userId, $productId);

    echo json_encode(['success' => true, 'wishlistCount' => $wishlistCount, 'newRelatedProduct' => $newRelatedProduct]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
}

function fetchNewRelatedProduct($userId, $removedProductId) {
    global $pdo;
    
    // Get the category of the removed product
    $stmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
    $stmt->execute([$removedProductId]);
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
    $stmt->execute([$categoryId, $userId, $removedProductId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
