<?php
session_start();
require_once 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();

try {
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE session_id = ?");
        $stmt->execute([$session_id]);
    }
    $count = $stmt->fetchColumn();

    echo json_encode(['count' => $count]);
} catch (PDOException $e) {
    error_log("Error getting cart count: " . $e->getMessage());
    echo json_encode(['count' => 0]);
}
