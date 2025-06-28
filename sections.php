
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Sections du stade
$sections = [
    [
        'id' => 'north',
        'name' => 'Tribune Nord',
        'seats' => 'A1,A2,A3,A4,A5,B1,B2,B3,B4,B5,C1,C2,C3,C4,C5',
        'description' => 'Tribune Nord - Ambiance garantie'
    ],
    [
        'id' => 'south',
        'name' => 'Tribune Sud',
        'seats' => 'D1,D2,D3,D4,D5,E1,E2,E3,E4,E5,F1,F2,F3,F4,F5',
        'description' => 'Tribune Sud - Vue panoramique'
    ],
    [
        'id' => 'east',
        'name' => 'Tribune Est',
        'seats' => 'G1,G2,G3,G4,G5,H1,H2,H3,H4,H5,I1,I2,I3,I4,I5',
        'description' => 'Tribune Est - Proche des vestiaires'
    ],
    [
        'id' => 'west',
        'name' => 'Tribune Ouest',
        'seats' => 'J1,J2,J3,J4,J5,K1,K2,K3,K4,K5,L1,L2,L3,L4,L5',
        'description' => 'Tribune Ouest - Vue latérale'
    ],
    [
        'id' => 'center',
        'name' => 'Tribune Centrale',
        'seats' => 'M1,M2,M3,M4,M5,N1,N2,N3,N4,N5,O1,O2,O3,O4,O5',
        'description' => 'Tribune Centrale - Meilleure vue'
    ]
];

echo json_encode($sections);
?>
