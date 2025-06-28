<?php
require 'vendor/autoload.php';
require_once 'config.php';
require_once 'email_template.php';

use SendGrid\Mail\Mail;

function sendConfirmationEmail($orderDetails) {
    try {
        $email = new Mail();
        $email->setFrom(SENDGRID_FROM_EMAIL, SENDGRID_FROM_NAME);
        $email->setSubject("Confirmation de votre commande #" . $orderDetails['orderId']);
        $email->addTo($orderDetails['customerEmail'], $orderDetails['customerName']);
        
        // Generate HTML content
        $htmlContent = generateEmailTemplate($orderDetails);
        $email->addContent("text/html", $htmlContent);
        
        // Create plain text version
        $plainTextContent = "Confirmation de commande #" . $orderDetails['orderId'] . "\n\n";
        $plainTextContent .= "Merci pour votre achat, " . $orderDetails['customerName'] . "!\n\n";
        $plainTextContent .= "Total: " . $orderDetails['totalAmount'] . "€\n\n";
        $plainTextContent .= "Pour plus de détails, consultez la version HTML de cet email.";
        $email->addContent("text/plain", $plainTextContent);

        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        $response = $sendgrid->send($email);
        
        return [
            'success' => true,
            'message' => 'Email envoyé avec succès',
            'status' => $response->statusCode()
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage()
        ];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data) {
        $result = sendConfirmationEmail($data);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
?> 