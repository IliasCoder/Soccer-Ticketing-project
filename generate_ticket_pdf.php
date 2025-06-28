<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('vendor/autoload.php');

class TicketPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, '🎫 Billet Officiel', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

function generateTicketPDF($ticketData) {
    try {
        // Create tickets directory if it doesn't exist
        $ticketsDir = __DIR__ . DIRECTORY_SEPARATOR . 'tickets';
        if (!file_exists($ticketsDir)) {
            if (!mkdir($ticketsDir, 0777, true)) {
                throw new Exception("Failed to create tickets directory");
            }
            chmod($ticketsDir, 0777);
        }

        // Verify directory is writable
        if (!is_writable($ticketsDir)) {
            throw new Exception("Tickets directory is not writable");
        }

        // Create new PDF document
        $pdf = new TicketPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Billetterie Sportive');
        $pdf->SetAuthor('Billetterie Sportive');
        $pdf->SetTitle('Billet - ' . $ticketData['matchName']);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Add QR Code
        $qrData = json_encode([
            'orderId' => $ticketData['orderId'],
            'matchId' => $ticketData['matchId'],
            'seat' => $ticketData['seatNumber']
        ]);
        $pdf->write2DBarcode($qrData, 'QRCODE,H', 160, 20, 35, 35);

        // Header Information
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $ticketData['matchName'], 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Date: ' . $ticketData['matchDate'] . ' à ' . $ticketData['matchTime'], 0, 1, 'L');
        $pdf->Cell(0, 10, 'Lieu: ' . $ticketData['venue'], 0, 1, 'L');

        // Customer Information
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Informations Client', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Nom: ' . $ticketData['customerName'], 0, 1, 'L');
        $pdf->Cell(0, 10, 'N° de commande: ' . $ticketData['orderId'], 0, 1, 'L');

        // Ticket Details
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Détails du Billet', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Catégorie: ' . $ticketData['categoryName'], 0, 1, 'L');
        $pdf->Cell(0, 10, 'Section: ' . $ticketData['sectionName'], 0, 1, 'L');
        $pdf->Cell(0, 10, 'Place: ' . $ticketData['seatNumber'], 0, 1, 'L');
        $pdf->Cell(0, 10, 'Prix: ' . $ticketData['price'] . '€', 0, 1, 'L');

        // Important Information
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Informations Importantes', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, "1. Ce billet est unique et personnel\n2. Présentez ce billet à l'entrée du stade\n3. Arrivez au moins 30 minutes avant le début du match\n4. Une pièce d'identité pourra vous être demandée", 0, 'L');

        // Generate unique filename
        $filename = 'ticket_' . $ticketData['orderId'] . '_' . $ticketData['seatNumber'] . '.pdf';
        $filepath = $ticketsDir . DIRECTORY_SEPARATOR . $filename;

        // Save PDF
        $pdf->Output($filepath, 'F');
        
        // Verify file was created
        if (!file_exists($filepath)) {
            throw new Exception("Failed to create PDF file");
        }

        // Set file permissions
        chmod($filepath, 0644);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'viewUrl' => 'view_ticket.php?filename=' . urlencode($filename)
        ];
        
    } catch (Exception $e) {
        error_log("Error generating PDF: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error generating PDF: ' . $e->getMessage()
        ];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Get and decode input data
    $rawData = file_get_contents('php://input');
    error_log("Received data: " . $rawData);
    
    $data = json_decode($rawData, true);
    
    if ($data) {
        $result = generateTicketPDF($data);
        error_log("PDF generation result: " . json_encode($result));
        echo json_encode($result);
    } else {
        $error = ['success' => false, 'message' => 'Invalid input data'];
        error_log("Error: Invalid input data. Raw data: " . $rawData);
        echo json_encode($error);
    }
}

// Handle direct PDF viewing
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['filename'])) {
    // Redirect to the new viewer page
    header('Location: view_ticket.php?filename=' . urlencode($_GET['filename']));
    exit;
}
?> 