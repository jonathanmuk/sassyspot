<?php
session_start();
require_once 'db_connect.php';

$response = ['success' => false, 'message' => '', 'action' => '', 'cartCount' => 0];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    if (isset($_SESSION['user_id'])) {
        // User is logged in
        $user_id = $_SESSION['user_id'];

        // Check if the item is already in the cart
        $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$user_id, $product_id]);

        if ($check_stmt->rowCount() > 0) {
            // Item exists, remove it
            $delete_sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
            $delete_stmt = $pdo->prepare($delete_sql);
            if ($delete_stmt->execute([$user_id, $product_id])) {
                $response['success'] = true;
                $response['message'] = 'Item removed from cart';
                $response['action'] = 'removed';
            }
        } else {
            // Item doesn't exist, add it
            $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
            $insert_stmt = $pdo->prepare($insert_sql);
            if ($insert_stmt->execute([$user_id, $product_id])) {
                $response['success'] = true;
                $response['message'] = 'Item added to cart';
                $response['action'] = 'added';
            }
        }

        // Get updated cart count
        $count_sql = "SELECT COUNT(*) FROM cart WHERE user_id = ?";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute([$user_id]);
        $response['cartCount'] = $count_stmt->fetchColumn();

        // Update session
        $_SESSION['cart_count'] = $response['cartCount'];
    } else {
        // User is not logged in, use session-based cart
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }

        $index = array_search($product_id, $_SESSION['guest_cart']);
        if ($index !== false) {
            // Item exists, remove it
            unset($_SESSION['guest_cart'][$index]);
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
            $response['action'] = 'removed';
        } else {
            // Item doesn't exist, add it
            $_SESSION['guest_cart'][] = $product_id;
            $response['success'] = true;
            $response['message'] = 'Item added to cart';
            $response['action'] = 'added';
        }

        $response['cartCount'] = count($_SESSION['guest_cart']);
        $_SESSION['cart_count'] = $response['cartCount'];
    }
}

echo json_encode($response);
