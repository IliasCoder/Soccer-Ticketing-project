<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('vendor/autoload.php');

try {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Test PDF');
    $pdf->SetAuthor('Test Author');
    $pdf->SetTitle('Test Document');

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add content
    $pdf->Cell(0, 10, 'Test PDF Generation', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Current time: ' . date('Y-m-d H:i:s'), 0, 1, 'C');

    // Create test directory if it doesn't exist
    if (!file_exists('test')) {
        mkdir('test', 0777, true);
    }

    // Save PDF
    $pdf->Output('test/test.pdf', 'F');
    
    echo json_encode([
        'success' => true,
        'message' => 'PDF generated successfully',
        'path' => 'test/test.pdf'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error generating PDF: ' . $e->getMessage()
    ]);
}
?> 