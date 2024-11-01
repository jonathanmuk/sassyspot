<?php
session_start();
require_once 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'cartCount' => 0, 'message' => '', 'inCart' => false];

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($productId > 0) {
    try {
        if ($userId) {
            // Check if the item is already in the cart
            $stmt = $pdo->prepare("SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                $response['inCart'] = true;
                $response['message'] = 'Item already in cart';
            } else {
                // Add new item to cart
                $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $stmt->execute([$userId, $productId]);
                $response['message'] = 'Item added to cart successfully';
            }

            // Get updated cart count
            $stmt = $pdo->prepare("SELECT SUM(quantity) FROM carts WHERE user_id = ?");
            $stmt->execute([$userId]);
            $cartCount = $stmt->fetchColumn();
        } else {
            // For guest users
            if (!isset($_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'] = [];
            }
            if (isset($_SESSION['guest_cart'][$productId])) {
                $response['inCart'] = true;
                $response['message'] = 'Item already in cart';
            } else {
                $_SESSION['guest_cart'][$productId] = 1;
                $response['message'] = 'Item added to cart successfully';
            }
            $cartCount = array_sum($_SESSION['guest_cart']);
        }

        $_SESSION['cart_count'] = $cartCount;

        $response['success'] = true;
        $response['cartCount'] = $cartCount;
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid product ID';
}

echo json_encode($response);