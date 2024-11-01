<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($productId > 0 && $quantity > 0) {
        if ($userId) {
            // Update quantity for logged-in user
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $userId, $productId]);
        } else {
            // Update quantity for guest user
            if (isset($_SESSION['guest_cart'][$productId])) {
                $_SESSION['guest_cart'][$productId] = $quantity;
            }
        }

        // Recalculate cart totals
        $subtotal = 0;
        if ($userId) {
            $stmt = $pdo->prepare("SELECT c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $cartItems = [];
            foreach ($_SESSION['guest_cart'] as $id => $qty) {
                $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $price = $stmt->fetchColumn();
                $cartItems[] = ['quantity' => $qty, 'price' => $price];
            }
        }

        foreach ($cartItems as $item) {
            $subtotal += $item['quantity'] * $item['price'];
        }

        $shipping = $subtotal > 100 ? 0 : 10000;
        $total = $subtotal + $shipping;

        echo json_encode([
            'success' => true,
            'subtotal' => number_format($subtotal),
            'shipping' => number_format($shipping),
            'total' => number_format($total)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
