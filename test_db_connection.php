<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    $host = 'localhost';
    $dbname = 'ticket_platform';
    $username = 'root';
    $password = '';
    
    // First, try to connect without database to check MySQL connection
    $conn = new PDO("mysql:host=$host", $username, $password);
    echo "<p style='color: green;'>✅ Successfully connected to MySQL server</p>";
    
    // Then try to connect with database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Successfully connected to database '$dbname'</p>";
    
    // Test if we can query the database
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Tables in database:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    
    // Additional debugging information
    echo "<h3>Debug Information:</h3>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    echo "<p>PDO Drivers Available: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
}
?> 