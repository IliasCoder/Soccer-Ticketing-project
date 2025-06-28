<?php
require_once 'mail_service.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $mailService = new MailService();
    
    // Données de test complètes avec les bonnes clés
    $ticketData = [
        'email' => 'habibifatimazahrae14@gmail.com',
        'filepath' => 'tickets/test_ticket.pdf',
        'filename' => 'test_ticket.pdf',
        'match_name' => 'PSG vs Marseille',
        'match_date' => '2024-06-15',
        'match_time' => '20:45',
        'stadium' => 'Parc des Princes',
        'section_id' => 'Tribune Nord',
        'seat_number' => 'A-123',
        'price' => 99.99,
        'customer_name' => 'Test Client',
        'id' => 'TEST-123',
        'category_id' => 'VIP',
        'section_name' => 'Tribune Nord'
    ];
    
    // Tenter d'envoyer l'email
    $result = $mailService->sendTicketEmail($ticketData);
    
    if ($result) {
        echo "✅ Email envoyé avec succès! Vérifiez votre boîte de réception.";
    } else {
        echo "❌ Échec de l'envoi de l'email.";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?> 