<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Catégories de tickets avec prix spécifiques
$categories = [
    [
        'id' => 'economic',
        'name' => 'Economic',
        'base_price' => 15.00,
        'price_multiplier' => 1.0,
        'description' => 'Places économiques avec vue limitée'
    ],
    [
        'id' => 'standard',
        'name' => 'Standard',
        'base_price' => 25.00,
        'price_multiplier' => 1.5,
        'description' => 'Places standard avec bonne vue'
    ],
    [
        'id' => 'vip',
        'name' => 'VIP',
        'base_price' => 50.00,
        'price_multiplier' => 2.5,
        'description' => 'Places VIP avec services exclusifs et meilleure vue'
    ]
];

echo json_encode($categories);
?>
