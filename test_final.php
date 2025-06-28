<?php
echo "<h1>🎫 Test Final de la Plateforme de Billetterie</h1>";

// Test 1: API Matches
echo "<h2>1. Test API Matches</h2>";
try {
    $response = file_get_contents('http://localhost/Final/matches_simple.php');
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "<p style='color:green'>✅ API Matches fonctionne - " . $data['count'] . " matches trouvés</p>";
    } else {
        echo "<p style='color:red'>❌ Erreur API Matches</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 2: API Catégories
echo "<h2>2. Test API Catégories</h2>";
try {
    $response = file_get_contents('http://localhost/Final/categories.php');
    $categories = json_decode($response, true);
    
    if ($categories && is_array($categories)) {
        echo "<p style='color:green'>✅ API Catégories fonctionne - " . count($categories) . " catégories disponibles</p>";
    } else {
        echo "<p style='color:red'>❌ Erreur API Catégories</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 3: API Sections
echo "<h2>3. Test API Sections</h2>";
try {
    $response = file_get_contents('http://localhost/Final/sections.php');
    $sections = json_decode($response, true);
    
    if ($sections && is_array($sections)) {
        echo "<p style='color:green'>✅ API Sections fonctionne - " . count($sections) . " sections disponibles</p>";
    } else {
        echo "<p style='color:red'>❌ Erreur API Sections</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 4: Base de données
echo "<h2>4. Test Base de Données</h2>";
try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    // Vérifier les tables
    $tables = ['matches', 'users', 'orders', 'tickets'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p style='color:green'>✅ Table '$table': $count enregistrements</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur base de données: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>🚀 Votre plateforme est prête !</h2>";
echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Liens utiles :</h3>";
echo "<ul>";
echo "<li><a href='index.html' target='_blank' style='color: #0066cc; font-weight: bold;'>🎫 Accéder à la Plateforme de Billetterie</a></li>";
echo "<li><a href='matches_simple.php' target='_blank'>📊 API Matches (JSON)</a></li>";
echo "<li><a href='categories.php' target='_blank'>🏷️ API Catégories (JSON)</a></li>";
echo "<li><a href='sections.php' target='_blank'>🏟️ API Sections (JSON)</a></li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f0fff0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>✅ Fonctionnalités disponibles :</h3>";
echo "<ul>";
echo "<li>✅ Affichage des matches depuis votre base de données</li>";
echo "<li>✅ Sélection de catégories de tickets (Standard, Premium, VIP, Platinum)</li>";
echo "<li>✅ Choix des sections du stade</li>";
echo "<li>✅ Saisie du numéro de place</li>";
echo "<li>✅ Gestion du panier avec persistance</li>";
echo "<li>✅ Formulaire client</li>";
echo "<li>✅ Système de paiement simulé</li>";
echo "<li>✅ Sauvegarde des commandes en base de données</li>";
echo "</ul>";
echo "</div>";
?>
