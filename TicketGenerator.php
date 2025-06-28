<?php
require_once 'vendor/autoload.php';

class TicketGenerator {
    public function generateTicketPDF($ticketData) {
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

        // Créer un nouveau document PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(180, 250), true, 'UTF-8', false);
        
        // Définir les informations du document
        $pdf->SetCreator('Billetterie Sportive');
        $pdf->SetAuthor('Billetterie Sportive');
        $pdf->SetTitle('Ticket - ' . $ticketData['match_name']);
        
        // Supprimer les en-têtes et pieds de page par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Ajouter une page
        $pdf->AddPage();
        
        // Définir quelques variables de style
        $primaryColor = '#1a73e8';
        $secondaryColor = '#4285f4';
        $backgroundColor = '#f8f9fa';
        
        // Formater la date et l'heure
        $date = new DateTime($ticketData['match_date']);
        $formattedDate = $date->format('d/m/Y');
        $time = substr($ticketData['match_time'], 0, 5); // Format HH:mm
        
        // Créer le contenu HTML
        $html = <<<HTML
        <style>
            .ticket-container {
                padding: 20px;
                background-color: {$backgroundColor};
                font-family: Arial, sans-serif;
            }
            .header {
                background-color: {$primaryColor};
                color: white;
                padding: 15px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .match-info {
                background-color: white;
                padding: 20px;
                margin: 10px 0;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .details-table {
                width: 100%;
                margin: 20px 0;
                border-collapse: collapse;
            }
            .details-table td {
                padding: 10px;
                border: 1px solid #ddd;
            }
            .details-table td:first-child {
                background-color: {$backgroundColor};
                font-weight: bold;
                width: 40%;
            }
            .important-info {
                background-color: #fff3cd;
                padding: 15px;
                margin-top: 20px;
                border-radius: 5px;
                font-size: 0.9em;
            }
            .qr-section {
                text-align: center;
                margin: 20px 0;
            }
        </style>
        
        <div class="ticket-container">
            <div class="header">
                <h1 style="font-size: 24px; margin: 0;">BILLET OFFICIEL</h1>
                <h2 style="font-size: 18px; margin: 10px 0 0 0;">{$ticketData['match_name']}</h2>
            </div>
            
            <div class="match-info">
                <table class="details-table">
                    <tr>
                        <td>Date</td>
                        <td>{$formattedDate}</td>
                    </tr>
                    <tr>
                        <td>Heure</td>
                        <td>{$time}</td>
                    </tr>
                    <tr>
                        <td>Stade</td>
                        <td>{$ticketData['stadium']}</td>
                    </tr>
                    <tr>
                        <td>Catégorie</td>
                        <td>{$ticketData['category_id']}</td>
                    </tr>
                    <tr>
                        <td>Section</td>
                        <td>{$ticketData['section_id']}</td>
                    </tr>
                    <tr>
                        <td>Place</td>
                        <td>{$ticketData['seat_number']}</td>
                    </tr>
                    <tr>
                        <td>Prix</td>
                        <td>{$ticketData['price']} €</td>
                    </tr>
                </table>
            </div>
            
            <div class="qr-section">
                [QR_CODE_PLACEHOLDER]
            </div>
            
            <div class="important-info">
                <strong>IMPORTANT :</strong><br>
                • Ce billet est unique et ne peut être utilisé qu'une seule fois.<br>
                • Une pièce d'identité pourra vous être demandée.<br>
                • Conservez ce billet en bon état jusqu'à la fin de l'événement.
            </div>
        </div>
        HTML;

        // Écrire le HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Ajouter le QR Code
        $style = array(
            'border' => false,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        );
        
        $qrData = json_encode([
            'ticket_id' => $ticketData['id'],
            'match' => $ticketData['match_name'],
            'date' => $formattedDate,
            'time' => $time,
            'stadium' => $ticketData['stadium'],
            'seat' => $ticketData['seat_number']
        ]);
        
        $pdf->write2DBarcode($qrData, 'QRCODE,H', 70, 160, 50, 50, $style);
        
        // Générer le PDF
        return $pdf->Output('ticket.pdf', 'S');
    }
}
?> 