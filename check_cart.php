<?php
session_start();
require_once 'db_connect.php';

$response = ['inCart' => false];

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($productId > 0) {
    if ($userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $response['inCart'] = $stmt->fetchColumn() > 0;
    } else {
        $response['inCart'] = isset($_SESSION['guest_cart'][$productId]);
    }
}

echo json_encode($response);
