<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($productId > 0) {
        if ($userId) {
            // Remove item for logged-in user
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            // Remove item for guest user
            if (isset($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $key => $item) {
                    if ($item == $productId) {
                        unset($_SESSION['guest_cart'][$key]);
                        break;
                    }
                }
            }
        }

        // Recalculate cart totals and count
        $subtotal = 0;
        $cartCount = 0;
        if ($userId) {
            $stmt = $pdo->prepare("SELECT c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cartCount = count($cartItems);
        } else {
            $cartItems = [];
            if (isset($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $id) {
                    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $price = $stmt->fetchColumn();
                    $cartItems[] = ['quantity' => 1, 'price' => $price];
                }
                $cartCount = count($_SESSION['guest_cart']);
            }
        }

        foreach ($cartItems as $item) {
            $subtotal += $item['quantity'] * $item['price'];
        }

        $shipping = $subtotal > 100 ? 0 : 10000;
        $total = $subtotal + $shipping;

        // Update session cart count
        $_SESSION['cart_count'] = $cartCount;

        echo json_encode([
            'success' => true,
            'subtotal' => number_format($subtotal),
            'shipping' => number_format($shipping),
            'total' => number_format($total),
            'cartCount' => $cartCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
