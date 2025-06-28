<?php
// Script de débogage pour le paiement
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Débogage du système de paiement</h1>";

// Vérifier si le fichier de log existe
if (file_exists('payment_errors.log')) {
    echo "<h2>Dernières erreurs de paiement:</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents('payment_errors.log')) . "</pre>";
} else {
    echo "<p>Aucun fichier de log d'erreurs trouvé.</p>";
}

// Vérifier si une dernière requête a été enregistrée
if (file_exists('last_payment_request.json')) {
    echo "<h2>Dernière requête de paiement:</h2>";
    $jsonData = file_get_contents('last_payment_request.json');
    echo "<pre>" . htmlspecialchars($jsonData) . "</pre>";
    
    // Analyser les données
    $data = json_decode($jsonData, true);
    if ($data) {
        echo "<h3>Analyse des données:</h3>";
        echo "<ul>";
        if (isset($data['customerInfo'])) {
            echo "<li>Client: " . htmlspecialchars($data['customerInfo']['name']) . " (" . htmlspecialchars($data['customerInfo']['email']) . ")</li>";
        }
        if (isset($data['cartItems'])) {
            echo "<li>Articles dans le panier: " . count($data['cartItems']) . "</li>";
        }
        if (isset($data['totalAmount'])) {
            echo "<li>Montant total: " . htmlspecialchars($data['totalAmount']) . "€</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Erreur de décodage JSON: " . json_last_error_msg() . "</p>";
    }
} else {
    echo "<p>Aucune requête de paiement enregistrée.</p>";
}

// Tester la connexion à la base de données
echo "<h2>Test de la base de données:</h2>";
try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color:green'>✅ Connexion à la base de données réussie!</p>";
    
    // Vérifier les tables nécessaires
    $tables = ['users', 'orders', 'tickets'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✅ Table '$table' existe</p>";
            
            // Afficher la structure de la table
            $stmt = $conn->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<details>";
            echo "<summary>Structure de la table $table</summary>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>" . $column['Field'] . "</td>";
                echo "<td>" . $column['Type'] . "</td>";
                echo "<td>" . $column['Null'] . "</td>";
                echo "<td>" . $column['Key'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</details>";
        } else {
            echo "<p style='color:red'>❌ Table '$table' n'existe pas!</p>";
            
            // Créer la table manquante
            echo "<p>Création de la table '$table'...</p>";
            
            if ($table == 'users') {
                $conn->exec("CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    phone VARCHAR(20),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
            } else if ($table == 'orders') {
                $conn->exec("CREATE TABLE orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL,
                    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )");
            } else if ($table == 'tickets') {
                $conn->exec("CREATE TABLE tickets (
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
                    FOREIGN KEY (order_id) REFERENCES orders(id)
                )");
            }
            
            echo "<p style='color:green'>✅ Table '$table' créée!</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur de base de données: " . $e->getMessage() . "</p>";
}

// Formulaire de test
echo "<h2>Test manuel du paiement:</h2>";
echo "<form id='testForm' style='background:#f5f5f5; padding:20px; border-radius:10px;'>";
echo "<div style='margin-bottom:15px;'>";
echo "<label style='display:block; margin-bottom:5px;'>Nom du client:</label>";
echo "<input type='text' id='customerName' value='Test Client' style='width:100%; padding:8px;'>";
echo "</div>";
echo "<div style='margin-bottom:15px;'>";
echo "<label style='display:block; margin-bottom:5px;'>Email du client:</label>";
echo "<input type='email' id='customerEmail' value='test@example.com' style='width:100%; padding:8px;'>";
echo "</div>";
echo "<div style='margin-bottom:15px;'>";
echo "<label style='display:block; margin-bottom:5px;'>Montant total:</label>";
echo "<input type='number' id='totalAmount' value='50.00' style='width:100%; padding:8px;'>";
echo "</div>";
echo "<button type='button' id='testButton' style='background:#4CAF50; color:white; padding:10px 15px; border:none; border-radius:5px; cursor:pointer;'>Tester le paiement</button>";
echo "</form>";

echo "<div id='testResult' style='margin-top:20px; padding:15px; border-radius:5px; display:none;'></div>";

echo "<script>
document.getElementById('testButton').addEventListener('click', async function() {
    const testResult = document.getElementById('testResult');
    testResult.style.display = 'block';
    testResult.style.background = '#f8f9fa';
    testResult.innerHTML = '<p>Envoi de la requête de test...</p>';
    
    const testData = {
        customerInfo: {
            name: document.getElementById('customerName').value,
            email: document.getElementById('customerEmail').value,
            phone: '0123456789'
        },
        cartItems: [
            {
                matchId: 1,
                categoryId: 'standard',
                sectionId: 'north',
                seatNumber: 'A15',
                quantity: 2,
                price: parseFloat(document.getElementById('totalAmount').value) / 2
            }
        ],
        totalAmount: parseFloat(document.getElementById('totalAmount').value)
    };
    
    try {
        const response = await fetch('payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(testData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            testResult.style.background = '#d4edda';
            testResult.innerHTML = '<h3 style=\"color:#155724\">✅ Paiement réussi!</h3>' +
                '<p><strong>ID de commande:</strong> ' + result.orderId + '</p>' +
                '<p><strong>Client:</strong> ' + result.orderDetails.customer_name + '</p>' +
                '<p><strong>Email:</strong> ' + result.orderDetails.customer_email + '</p>' +
                '<p><strong>Montant:</strong> ' + result.orderDetails.total_amount + '€</p>';
        } else {
            testResult.style.background = '#f8d7da';
            testResult.innerHTML = '<h3 style=\"color:#721c24\">❌ Erreur de paiement</h3>' +
                '<p>' + result.error + '</p>';
        }
    } catch (error) {
        testResult.style.background = '#f8d7da';
        testResult.innerHTML = '<h3 style=\"color:#721c24\">❌ Erreur de connexion</h3>' +
                '<p>' + error.message + '</p>';
    }
});
</script>";

echo "<hr>";
echo "<p><a href='index.html'>Retour à la plateforme de billetterie</a></p>";
?>
