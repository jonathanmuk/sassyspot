<?php
session_start();
require_once 'db_connect.php';

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($userId) {
    // Clear cart for logged-in user
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
} else {
    // Clear cart for guest user
    $_SESSION['guest_cart'] = [];
}

// Reset cart count in session
$_SESSION['cart_count'] = 0;

echo json_encode([
    'success' => true,
    'subtotal' => '0.00',
    'shipping' => '0.00',
    'total' => '0.00',
    'cartCount' => 0
]);
