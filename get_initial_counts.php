<?php
session_start();
require_once 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

try {
    // Get cart count and items
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(product_id) as items FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(product_id) as items FROM carts WHERE session_id = ?");
        $stmt->execute([$session_id]);
    }
    $cartResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $cartResult['count'];
    $cartItems = $cartResult['items'] ? explode(',', $cartResult['items']) : [];

    // Get wishlist count and items
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(product_id) as items FROM wishlists WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(product_id) as items FROM wishlists WHERE session_id = ?");
        $stmt->execute([$session_id]);
    }
    $wishlistResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $wishlistCount = $wishlistResult['count'];
    $wishlistItems = $wishlistResult['items'] ? explode(',', $wishlistResult['items']) : [];

    echo json_encode([
        'cartCount' => $cartCount,
        'wishlistCount' => $wishlistCount,
        'cartItems' => $cartItems,
        'wishlistItems' => $wishlistItems
    ]);
} catch (PDOException $e) {
    error_log("Error getting initial counts: " . $e->getMessage());
    echo json_encode([
        'cartCount' => 0,
        'wishlistCount' => 0,
        'cartItems' => [],
        'wishlistItems' => []
    ]);
}
