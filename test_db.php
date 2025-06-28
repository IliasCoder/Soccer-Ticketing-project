<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'database.php';

try {
    $db = new Database();
    $result = $db->testConnection();
    
    if ($result['success']) {
        echo "Database connection successful!\n";
        
        // Test tables
        $conn = $db->getConnection();
        
        $tables = ['users', 'orders', 'tickets', 'matches'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "$table table exists and has $count records\n";
            } catch (PDOException $e) {
                echo "$table table error: " . $e->getMessage() . "\n";
            }
        }
        
    } else {
        echo "Database connection failed: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 