<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'action' => '', 'wishlistCount' => 0];

if (!isset($_POST['product_id'])) {
    $response['message'] = 'Product ID not set.';
    echo json_encode($response);
    exit;
}

$product_id = $_POST['product_id'];

// Function to get wishlist count
function getWishlistCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

if (!isset($_SESSION['user_id'])) {
    // Guest user
    if (!isset($_SESSION['guest_wishlist'])) {
        $_SESSION['guest_wishlist'] = [];
    }

    $index = array_search($product_id, $_SESSION['guest_wishlist']);
    if ($index !== false) {
        unset($_SESSION['guest_wishlist'][$index]);
        $_SESSION['guest_wishlist'] = array_values($_SESSION['guest_wishlist']); // Re-index array
        $response['action'] = 'removed';
    } else {
        $_SESSION['guest_wishlist'][] = $product_id;
        $response['action'] = 'added';
    }

    $response['success'] = true;
    $response['wishlistCount'] = count($_SESSION['guest_wishlist']);
    $response['message'] = 'Wishlist updated successfully';
} else {
    // Logged-in user
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        $check_stmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check_stmt->execute([$user_id, $product_id]);

        if ($check_stmt->rowCount() > 0) {
            $delete_stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $delete_stmt->execute([$user_id, $product_id]);
            $response['action'] = 'removed';
        } else {
            $insert_stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $insert_stmt->execute([$user_id, $product_id]);
            $response['action'] = 'added';
        }

        $pdo->commit();

        $response['success'] = true;
        $response['wishlistCount'] = getWishlistCount($pdo, $user_id);
        $response['message'] = 'Wishlist updated successfully';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Database error in toggle_wishlist_item.php: ' . $e->getMessage());
    }
}

$_SESSION['wishlist_count'] = $response['wishlistCount'];
echo json_encode($response);
