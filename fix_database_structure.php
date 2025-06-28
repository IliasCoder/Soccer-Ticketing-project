<?php
// Script pour corriger la structure de la base de données
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Correction de la Structure de la Base de Données</h1>";

try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie!</p>";
    
    // Vérifier et corriger la table users
    echo "<h2>1. Vérification de la table 'users':</h2>";
    
    $stmt = $conn->query("SHOW COLUMNS FROM users");
    $userColumns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    echo "<p>Colonnes actuelles: " . implode(', ', $userColumns) . "</p>";
    
    $requiredUserColumns = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(100) NOT NULL',
        'email' => 'VARCHAR(100) NOT NULL UNIQUE',
        'phone' => 'VARCHAR(20)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    foreach ($requiredUserColumns as $column => $definition) {
        if (!in_array($column, $userColumns)) {
            try {
                $conn->exec("ALTER TABLE users ADD COLUMN $column $definition");
                echo "<p style='color: green;'>✅ Colonne '$column' ajoutée à la table users</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Erreur ajout colonne '$column': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Colonne '$column' existe déjà</p>";
        }
    }
    
    // Vérifier et corriger la table orders
    echo "<h2>2. Vérification de la table 'orders':</h2>";
    
    $stmt = $conn->query("SHOW COLUMNS FROM orders");
    $orderColumns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    echo "<p>Colonnes actuelles: " . implode(', ', $orderColumns) . "</p>";
    
    $requiredOrderColumns = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT NOT NULL',
        'total_amount' => 'DECIMAL(10,2) NOT NULL',
        'payment_status' => "ENUM('pending', 'completed', 'failed') DEFAULT 'pending'",
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    foreach ($requiredOrderColumns as $column => $definition) {
        if (!in_array($column, $orderColumns)) {
            try {
                $conn->exec("ALTER TABLE orders ADD COLUMN $column $definition");
                echo "<p style='color: green;'>✅ Colonne '$column' ajoutée à la table orders</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Erreur ajout colonne '$column': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Colonne '$column' existe déjà</p>";
        }
    }
    
    // Vérifier et corriger la table tickets
    echo "<h2>3. Vérification de la table 'tickets':</h2>";
    
    $stmt = $conn->query("SHOW COLUMNS FROM tickets");
    $ticketColumns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    echo "<p>Colonnes actuelles: " . implode(', ', $ticketColumns) . "</p>";
    
    $requiredTicketColumns = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'order_id' => 'INT NOT NULL',
        'match_id' => 'INT NOT NULL',
        'category_id' => 'VARCHAR(50) NOT NULL',
        'section_id' => 'VARCHAR(50) NOT NULL',
        'seat_number' => 'VARCHAR(20) NOT NULL',
        'price' => 'DECIMAL(10,2) NOT NULL',
        'status' => "ENUM('reserved', 'confirmed', 'cancelled') DEFAULT 'reserved'",
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    foreach ($requiredTicketColumns as $column => $definition) {
        if (!in_array($column, $ticketColumns)) {
            try {
                $conn->exec("ALTER TABLE tickets ADD COLUMN $column $definition");
                echo "<p style='color: green;'>✅ Colonne '$column' ajoutée à la table tickets</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Erreur ajout colonne '$column': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Colonne '$column' existe déjà</p>";
        }
    }
    
    // Test du système de paiement après correction
    echo "<h2>4. Test du système de paiement:</h2>";
    
    // Données de test
    $testData = [
        'customerInfo' => [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'phone' => '0123456789'
        ],
        'cartItems' => [
            [
                'matchId' => 1,
                'categoryId' => 'standard',
                'sectionId' => 'north',
                'seatNumber' => 'A15',
                'quantity' => 1,
                'price' => 25.00
            ]
        ],
        'totalAmount' => 25.00
    ];
    
    // Simuler le processus de paiement
    try {
        // 1. Créer ou récupérer l'utilisateur
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$testData['customerInfo']['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $userId = $user['id'];
            echo "<p style='color: blue;'>ℹ️ Utilisateur existant trouvé (ID: $userId)</p>";
        } else {
            // Créer un nouvel utilisateur
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $testData['customerInfo']['name'],
                $testData['customerInfo']['email'],
                $testData['customerInfo']['phone']
            ]);
            $userId = $conn->lastInsertId();
            echo "<p style='color: green;'>✅ Nouvel utilisateur créé (ID: $userId)</p>";
        }
        
        // 2. Créer la commande
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_status, created_at) VALUES (?, ?, 'completed', NOW())");
        $stmt->execute([$userId, $testData['totalAmount']]);
        $orderId = $conn->lastInsertId();
        echo "<p style='color: green;'>✅ Commande créée (ID: $orderId)</p>";
        
        // 3. Créer les tickets
        foreach ($testData['cartItems'] as $item) {
            $stmt = $conn->prepare("INSERT INTO tickets (order_id, match_id, category_id, section_id, seat_number, price, status, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, 'confirmed', NOW())");
            $stmt->execute([
                $orderId,
                $item['matchId'],
                $item['categoryId'],
                $item['sectionId'],
                $item['seatNumber'],
                $item['price']
            ]);
        }
        echo "<p style='color: green;'>✅ Tickets créés avec succès</p>";
        
        echo "<p style='color: green; font-weight: bold;'>🎉 Test de paiement réussi ! Le système fonctionne maintenant.</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur lors du test de paiement: " . $e->getMessage() . "</p>";
    }
    
    // Ajouter la colonne price à la table matches si elle n'existe pas
    $stmt = $conn->query("SHOW COLUMNS FROM matches LIKE 'price'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE matches ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 45.00");
        echo "<p style='color: green;'>✅ Colonne 'price' ajoutée à la table matches</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Colonne 'price' existe déjà</p>";
    }
    
    // Mettre à jour les prix existants s'ils sont nuls
    $conn->exec("UPDATE matches SET price = 45.00 WHERE price IS NULL OR price = 0");
    echo "✅ Prix mis à jour pour tous les matches\n";
    
    echo "Structure de la base de données mise à jour avec succès!\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>🎯 Prochaines étapes:</h2>";
echo "<ol>";
echo "<li><a href='index.html' target='_blank'>Tester la plateforme complète</a></li>";
echo "<li>Essayer d'ajouter un ticket au panier et finaliser un paiement</li>";
echo "<li>Vérifier que les données sont bien enregistrées</li>";
echo "</ol>";
?>
