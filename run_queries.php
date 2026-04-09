<?php
$host = '127.0.0.1';
$db = 'pharmacy';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Query 1: COUNT(*) and SUM(track_inventory) ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(track_inventory) as tracked FROM products");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== Query 2: First 10 products ===\n";
    $stmt = $pdo->query("SELECT id, name, track_inventory, in_stock FROM products LIMIT 10");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== Query 3: COUNT from product_batches ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM product_batches");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== Query 4: SUM(qty_received) and SUM(qty_sold) ===\n";
    $stmt = $pdo->query("SELECT SUM(qty_received) as total_received, SUM(qty_sold) as total_sold FROM product_batches");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
