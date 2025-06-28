<?php
// Afficher toutes les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Création de la base de données et des tables</h1>";

// Paramètres de connexion
$host = "localhost";  // ou 127.0.0.1
$port = "3306";       // port MySQL par défaut
$username = "root";
$password = "";       // vide par défaut sur XAMPP/WAMP

try {
    // Connexion au serveur MySQL sans spécifier de base de données
    $dsn = "mysql:host=$host;port=$port";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>✅ Connexion au serveur MySQL réussie!</p>";
    
    // Créer la base de données si elle n'existe pas
    $dbname = "ticket_platform";
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "<p style='color:green'>✅ Base de données '$dbname' créée ou déjà existante.</p>";
    
    // Sélectionner la base de données
    $conn->exec("USE $dbname");
    
    // Créer la table matches si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        home_team VARCHAR(100) NOT NULL,
        away_team VARCHAR(100) NOT NULL,
        match_date DATE NOT NULL,
        stadium VARCHAR(200) DEFAULT 'Stade',
        match_time TIME DEFAULT '20:00:00',
        price DECIMAL(10,2) DEFAULT 25.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "<p style='color:green'>✅ Table 'matches' créée ou déjà existante.</p>";
    
    // Vérifier si la table est vide
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
    
    // Créer la table users si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "<p style='color:green'>✅ Table 'users' créée ou déjà existante.</p>";
    
    // Créer la table orders si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    $conn->exec($sql);
    echo "<p style='color:green'>✅ Table 'orders' créée ou déjà existante.</p>";
    
    // Créer la table tickets si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        match_id INT NOT NULL,
        category_id VARCHAR(50) NOT NULL,
        section_id VARCHAR(50) NOT NULL,
        seat_number VARCHAR(20) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        status ENUM('reserved', 'confirmed', 'cancelled') DEFAULT 'reserved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (match_id) REFERENCES matches(id)
    )";
    
    $conn->exec($sql);
    echo "<p style='color:green'>✅ Table 'tickets' créée ou déjà existante.</p>";
    
    echo "<h2>Configuration terminée avec succès!</h2>";
    echo "<p>Vous pouvez maintenant utiliser la plateforme de billetterie.</p>";
    echo "<p><a href='index.html'>Accéder à la plateforme</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>
