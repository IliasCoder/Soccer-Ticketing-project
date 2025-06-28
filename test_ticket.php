<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('generate_ticket_pdf.php');

$testData = [
    'orderId' => '12345',
    'matchId' => '1',
    'matchName' => 'PSG vs Marseille',
    'matchDate' => '15/12/2024',
    'matchTime' => '20:00',
    'venue' => 'Parc des Princes',
    'customerName' => 'John Doe',
    'categoryName' => 'VIP',
    'sectionName' => 'Tribune Nord',
    'seatNumber' => 'A123',
    'price' => '150.00'
];

$result = generateTicketPDF($testData);
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?> 