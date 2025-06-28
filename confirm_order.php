<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {
        $_SESSION['order_data'] = $data;
        echo json_encode(['success' => true, 'message' => 'Données sauvegardées']);
        exit;
    }
}

$orderData = $_SESSION['order_data'] ?? null;
if (!$orderData) {
    header('Location: index.html');
    exit;
}

require_once 'database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Récupérer les informations des matches
    $matchIds = array_column($orderData['cartItems'], 'matchId');
    $placeholders = str_repeat('?,', count($matchIds) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM matches WHERE id IN ($placeholders)");
    $stmt->execute($matchIds);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $matchesById = [];
    foreach ($matches as $match) {
        $matchesById[$match['id']] = $match;
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Commande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ticket-preview {
            border: 2px dashed #ccc;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
        .confirmation-header {
            background-color: #1a73e8;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .price-summary {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="confirmation-header">
        <div class="container">
            <h1>Confirmation de votre commande</h1>
            <p class="lead">Veuillez vérifier les informations avant de procéder au paiement</p>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Informations client -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Informations Client</h3>
            </div>
            <div class="card-body">
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($orderData['customerInfo']['username']); ?></p>
                <p><strong>Email :</strong> <?php echo htmlspecialchars($orderData['customerInfo']['email']); ?></p>
                <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($orderData['customerInfo']['phone']); ?></p>
            </div>
        </div>

        <!-- Tickets -->
        <h3 class="mb-4">Vos Tickets</h3>
        <?php foreach ($orderData['cartItems'] as $item): 
            $match = $matchesById[$item['matchId']] ?? null;
            if (!$match) continue;
        ?>
        <div class="ticket-preview">
            <div class="row">
                <div class="col-md-8">
                    <h4><?php echo htmlspecialchars($match['home_team'] . ' vs ' . $match['away_team']); ?></h4>
                    <p><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($match['match_date'])); ?></p>
                    <p><strong>Heure :</strong> <?php echo substr($match['match_time'], 0, 5); ?></p>
                    <p><strong>Stade :</strong> <?php echo htmlspecialchars($match['stadium']); ?></p>
                    <p><strong>Section :</strong> <?php echo htmlspecialchars($item['sectionId']); ?></p>
                    <p><strong>Place :</strong> <?php echo htmlspecialchars($item['seatNumber']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <h5 class="text-primary"><?php echo number_format($item['price'], 2); ?> €</h5>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Résumé des prix -->
        <div class="price-summary">
            <div class="row">
                <div class="col-md-8">
                    <h4>Total à payer</h4>
                </div>
                <div class="col-md-4 text-end">
                    <h4 class="text-primary"><?php echo number_format($orderData['totalAmount'], 2); ?> €</h4>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="mt-4 text-center">
            <button type="button" class="btn btn-secondary me-3" onclick="window.history.back()">Modifier la commande</button>
            <button type="button" class="btn btn-primary" onclick="proceedToPayment()">Procéder au paiement</button>
        </div>
    </div>

    <script>
    function proceedToPayment() {
        const orderData = <?php echo json_encode($orderData); ?>;
        
        fetch('payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.downloadUrl;
            } else {
                alert('Erreur: ' + (data.error || 'Une erreur est survenue'));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors du paiement');
        });
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} catch (Exception $e) {
    // En cas d'erreur, afficher un message d'erreur
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container py-5">
            <div class="alert alert-danger">
                <h4 class="alert-heading">Erreur</h4>
                <p><?php echo htmlspecialchars($e->getMessage()); ?></p>
                <hr>
                <p class="mb-0">
                    <button class="btn btn-outline-danger" onclick="window.history.back()">Retour</button>
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?> 