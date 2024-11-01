<?php
require_once 'db_connect.php';

// Get the search query
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Prepare the SQL statement
$sql = "SELECT * FROM products WHERE name LIKE :query";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => "%$query%"]);

// Fetch the results
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($results);
