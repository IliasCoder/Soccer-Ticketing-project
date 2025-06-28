<?php
require_once 'vendor/autoload.php';
require_once 'mail_service.php';

header('Content-Type: application/json');

try {
    // Récupérer les données de la requête
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['filename']) || !isset($data['email'])) {
        throw new Exception('Données manquantes');
    }

    $filename = basename($data['filename']); // Sécuriser le nom du fichier
    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        throw new Exception('Email invalide');
    }

    // Vérifier que le fichier existe
    $filepath = __DIR__ . DIRECTORY_SEPARATOR . 'tickets' . DIRECTORY_SEPARATOR . $filename;
    if (!file_exists($filepath)) {
        throw new Exception('Fichier ticket non trouvé');
    }

    // Créer une instance du service d'email
    $mailService = new MailService();

    // Préparer les données pour l'email
    $ticketData = [
        'filename' => $filename,
        'filepath' => $filepath,
        'email' => $email
    ];

    // Envoyer l'email
    $result = $mailService->sendTicketEmail($ticketData);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Ticket envoyé avec succès'
        ]);
    } else {
        throw new Exception('Erreur lors de l\'envoi de l\'email');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 