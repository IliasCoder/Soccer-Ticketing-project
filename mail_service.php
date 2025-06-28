<?php
require 'vendor/autoload.php';
require_once 'TicketGenerator.php';
require_once 'config.php';

class MailService {
    private $sendgrid;
    private $fromEmail;
    private $fromName;
    private $ticketGenerator;
    
    public function __construct() {
        $this->sendgrid = new \SendGrid(SENDGRID_API_KEY);
        $this->fromEmail = SENDGRID_FROM_EMAIL;
        $this->fromName = SENDGRID_FROM_NAME;
        $this->ticketGenerator = new TicketGenerator();
    }
    
    public function sendTicketConfirmation($customerEmail, $customerName, $orderDetails, $tickets) {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->addTo($customerEmail, $customerName);
            $email->setSubject('Confirmation de vos billets - Commande #' . $orderDetails['id']);
            
            // Création du contenu HTML de l'email
            $emailContent = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #2c3e50; text-align: center;'>Merci pour votre achat, {$customerName}!</h2>
                    <p style='color: #34495e;'>Votre commande a été confirmée avec succès.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h3 style='color: #2c3e50; margin-top: 0;'>Détails de la commande:</h3>
                        <p><strong>Numéro de commande:</strong> #{$orderDetails['id']}</p>
                        <p><strong>Montant total:</strong> {$orderDetails['total_amount']}€</p>
                    </div>
                    
                    <p style='color: #2c3e50;'><strong>Vos billets sont joints à cet email au format PDF.</strong></p>
                    
                    <h3 style='color: #2c3e50;'>Récapitulatif de vos billets:</h3>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <tr style='background-color: #2c3e50; color: white;'>
                            <th style='padding: 12px; border: 1px solid #ddd;'>Match</th>
                            <th style='padding: 12px; border: 1px solid #ddd;'>Date</th>
                            <th style='padding: 12px; border: 1px solid #ddd;'>Heure</th>
                            <th style='padding: 12px; border: 1px solid #ddd;'>Stade</th>
                            <th style='padding: 12px; border: 1px solid #ddd;'>Place</th>
                        </tr>";
            
            // Ajouter chaque ticket au récapitulatif et générer les PDF
            foreach ($tickets as $index => $ticket) {
                $date = new DateTime($ticket['match_date']);
                $formattedDate = $date->format('d/m/Y');
                $time = substr($ticket['match_time'], 0, 5);
                
                $emailContent .= "
                    <tr>
                        <td style='padding: 12px; border: 1px solid #ddd;'>{$ticket['match_name']}</td>
                        <td style='padding: 12px; border: 1px solid #ddd;'>{$formattedDate}</td>
                        <td style='padding: 12px; border: 1px solid #ddd;'>{$time}</td>
                        <td style='padding: 12px; border: 1px solid #ddd;'>{$ticket['stadium']}</td>
                        <td style='padding: 12px; border: 1px solid #ddd;'>Section {$ticket['section_id']}, Place {$ticket['seat_number']}</td>
                    </tr>";
                
                try {
                    // Générer le PDF pour ce ticket
                    $pdfContent = $this->ticketGenerator->generateTicketPDF($ticket);
                    
                    // Ajouter le PDF en pièce jointe
                    $email->addAttachment(
                        base64_encode($pdfContent),
                        'application/pdf',
                        "ticket_{$orderDetails['id']}_{$index}.pdf",
                        'attachment'
                    );
                } catch (Exception $e) {
                    logError("Erreur lors de la génération du PDF pour le ticket {$ticket['id']}: " . $e->getMessage());
                    throw $e;
                }
            }
            
            $emailContent .= "
                    </table>
                    
                    <div style='background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <strong>Important:</strong>
                        <ul style='margin: 10px 0; padding-left: 20px;'>
                            <li>Vos billets sont joints à cet email au format PDF</li>
                            <li>Chaque billet contient un QR code unique</li>
                            <li>Imprimez vos billets ou présentez-les sur votre téléphone</li>
                            <li>Une pièce d'identité pourra vous être demandée</li>
                        </ul>
                    </div>
                    
                    <div style='margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px; text-align: center;'>
                        <p style='color: #666; margin: 0;'>
                            Pour toute question, n'hésitez pas à nous contacter.
                        </p>
                    </div>
                </div>";
            
            $email->addContent("text/html", $emailContent);
            
            $response = $this->sendgrid->send($email);
            if ($response->statusCode() != 202) {
                logError("Erreur SendGrid: " . $response->statusCode() . " - " . json_encode($response->headers()));
                return false;
            }
            return true;
            
        } catch (Exception $e) {
            logError("Erreur lors de l'envoi de l'email: " . $e->getMessage());
            return false;
        }
    }

    public function sendTicketEmail($ticketData) {
        try {
            // Vérification et valeurs par défaut pour les données requises
            $ticketData = array_merge([
                'match_date' => date('Y-m-d'),
                'match_time' => '20:00',
                'stadium' => 'Stade Principal',
                'id' => 'TEMP-' . uniqid(),
                'category_id' => 'Standard',
                'section_id' => 'Section A',
                'seat_number' => 'A1',
                'price' => 0.00,
                'match_name' => 'Match de Test'
            ], $ticketData);

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->addTo($ticketData['email']);
            $email->setSubject('Votre billet - Billetterie Sportive');
            
            // Contenu HTML de l'email
            $emailContent = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #2c3e50; text-align: center;'>Votre billet est prêt !</h2>
                    <p style='color: #34495e;'>Vous trouverez votre billet en pièce jointe à cet email.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h3 style='color: #2c3e50; margin-top: 0;'>Détails du match:</h3>
                        <p><strong>Match:</strong> {$ticketData['match_name']}</p>
                        <p><strong>Date:</strong> {$ticketData['match_date']}</p>
                        <p><strong>Heure:</strong> {$ticketData['match_time']}</p>
                        <p><strong>Stade:</strong> {$ticketData['stadium']}</p>
                        <p><strong>Section:</strong> {$ticketData['section_id']}</p>
                        <p><strong>Place:</strong> {$ticketData['seat_number']}</p>
                    </div>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h3 style='color: #2c3e50; margin-top: 0;'>Informations importantes:</h3>
                        <ul style='color: #34495e;'>
                            <li>Conservez ce billet en bon état jusqu'à l'événement</li>
                            <li>Présentez ce billet à l'entrée du stade</li>
                            <li>Une pièce d'identité pourra vous être demandée</li>
                        </ul>
                    </div>
                    
                    <p style='color: #2c3e50; text-align: center; margin-top: 30px;'>
                        Merci de votre confiance !<br>
                        L'équipe de la Billetterie Sportive
                    </p>
                </div>
            ";
            
            $email->addContent("text/html", $emailContent);
            
            // Vérifier si le fichier existe
            if (!file_exists($ticketData['filepath'])) {
                throw new Exception("Le fichier du ticket n'existe pas: " . $ticketData['filepath']);
            }
            
            // Ajouter le ticket en pièce jointe
            $attachment = base64_encode(file_get_contents($ticketData['filepath']));
            $email->addAttachment(
                $attachment,
                'application/pdf',
                $ticketData['filename'],
                'attachment'
            );
            
            // Envoyer l'email
            $response = $this->sendgrid->send($email);
            
            return $response->statusCode() === 202;
            
        } catch (Exception $e) {
            error_log("Error sending ticket email: " . $e->getMessage());
            return false;
        }
    }
}
?> 