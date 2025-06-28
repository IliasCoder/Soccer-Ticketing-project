<?php

require_once 'config.php';

try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    // use exec() because no results are returned
    $conn->exec($sql);
    echo "Database created successfully<br>";
} catch(PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "<br>";
}

$conn = null;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) DEFAULT 'change_me',
            role ENUM('admin', 'user') DEFAULT 'user' NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "matches" => "CREATE TABLE IF NOT EXISTS matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            home_team VARCHAR(100) NOT NULL,
            away_team VARCHAR(100) NOT NULL,
            match_date DATE NOT NULL,
            stadium VARCHAR(100) NOT NULL,
            match_time TIME NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "orders" => "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        "tickets" => "CREATE TABLE IF NOT EXISTS tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            match_id INT NOT NULL,
            category_id VARCHAR(50) NOT NULL,
            section_id VARCHAR(50) NOT NULL,
            seat_number VARCHAR(20) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            status ENUM('reserved', 'confirmed', 'cancelled') DEFAULT 'reserved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (match_id) REFERENCES matches(id)
        )"
    ];

    foreach ($tables as $table => $sql) {
        $conn->exec($sql);
        echo "Table $table created successfully<br>";
    }

    // Vérifier s'il y a déjà des données dans la table matches
    $stmt = $conn->query("SELECT COUNT(*) FROM matches");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insérer des données de test
        $sql = "INSERT INTO matches (home_team, away_team, match_date, stadium, match_time, price) VALUES
            ('PSG', 'Marseille', '2024-12-15', 'Parc des Princes', '20:00:00', 45.00),
            ('Lyon', 'Lille', '2024-12-20', 'Groupama Stadium', '18:00:00', 35.00),
            ('Monaco', 'Bordeaux', '2024-12-22', 'Stade Louis II', '19:00:00', 30.00),
            ('Nantes', 'Rennes', '2024-12-28', 'Stade de la Beaujoire', '17:00:00', 25.00)";
        
        $conn->exec($sql);
        echo "<p style='color:green'>✅ Données de test insérées dans la table 'matches'.</p>";
    } else {
        echo "<p>La table 'matches' contient déjà $count enregistrements.</p>";
    }

    echo "<p style='color:green'>✅ Configuration de la base de données terminée avec succès!</p>";

} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
