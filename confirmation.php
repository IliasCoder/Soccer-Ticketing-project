<?php
require_once 'config.php';
require_once 'database.php';

// Récupérer l'ID de la commande depuis l'URL
$orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;

if (!$orderId) {
    die("Commande non trouvée");
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Récupérer les informations de la commande et des tickets
    $stmt = $conn->prepare("
        SELECT 
            o.id as order_id,
            o.total_amount,
            o.created_at,
            u.username,
            u.email,
            t.seat_number,
            m.home_team,
            m.away_team,
            m.match_date,
            m.stadium,
            s.name as section_name,
            c.name as category_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN tickets t ON t.order_id = o.id
        JOIN matches m ON t.match_id = m.id
        JOIN sections s ON t.section_id = s.id
        JOIN ticket_categories c ON t.category_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tickets)) {
        die("Commande non trouvée");
    }

    $orderInfo = $tickets[0];
    $matchDate = new DateTime($orderInfo['match_date']);
} catch (Exception $e) {
    die("Une erreur est survenue: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Commande</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .success-icon {
            color: #27ae60;
            font-size: 48px;
            margin-bottom: 20px;
        }

        .order-details {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .ticket-list {
            margin-top: 20px;
        }

        .ticket-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: white;
        }

        .ticket-item h3 {
            color: #2c3e50;
            margin-top: 0;
        }

        .whatsapp-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background-color: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            text-decoration: none;
        }

        .whatsapp-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .whatsapp-button i {
            color: white;
            font-size: 32px;
        }

        .whatsapp-tooltip {
            position: absolute;
            right: 75px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            display: none;
            white-space: nowrap;
        }

        .whatsapp-button:hover .whatsapp-tooltip {
            display: block;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-check-circle success-icon"></i>
            <h1>Commande Confirmée</h1>
            <p>Merci pour votre achat !</p>
        </div>

        <div class="order-details">
            <h2>Détails de la commande</h2>
            <p><strong>N° de commande:</strong> #<?php echo $orderInfo['order_id']; ?></p>
            <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($orderInfo['created_at'])); ?></p>
            <p><strong>Client:</strong> <?php echo htmlspecialchars($orderInfo['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($orderInfo['email']); ?></p>
            <p><strong>Montant total:</strong> <?php echo number_format($orderInfo['total_amount'], 2); ?>€</p>
        </div>

        <div class="ticket-list">
            <h2>Vos billets</h2>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-item">
                    <h3><?php echo htmlspecialchars($ticket['home_team'] . ' vs ' . $ticket['away_team']); ?></h3>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['match_date'])); ?></p>
                    <p><strong>Stade:</strong> <?php echo htmlspecialchars($ticket['stadium']); ?></p>
                    <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($ticket['category_name']); ?></p>
                    <p><strong>Section:</strong> <?php echo htmlspecialchars($ticket['section_name']); ?></p>
                    <p><strong>Place:</strong> <?php echo htmlspecialchars($ticket['seat_number']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="index.html" class="back-button">Retour à l'accueil</a>
    </div>

    <?php
    // Préparer le message WhatsApp
    $matchName = $orderInfo['home_team'] . ' vs ' . $orderInfo['away_team'];
    $matchDate = date('d/m/Y H:i', strtotime($orderInfo['match_date']));
    
    $ticketDetails = "🎫 Billet de match confirmé !\n\n";
    $ticketDetails .= "Match: " . $matchName . "\n";
    $ticketDetails .= "Date: " . $matchDate . "\n";
    $ticketDetails .= "Stade: " . $orderInfo['stadium'] . "\n";
    $ticketDetails .= "Section: " . $orderInfo['section_name'] . "\n";
    $ticketDetails .= "Catégorie: " . $orderInfo['category_name'] . "\n";
    $ticketDetails .= "N° de commande: #" . $orderInfo['order_id'] . "\n\n";
    $ticketDetails .= "📍 Rendez-vous au stade !";
    
    $encodedMessage = urlencode($ticketDetails);
    ?>

    <a href="https://wa.me/?text=<?php echo $encodedMessage; ?>" target="_blank" class="whatsapp-button">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-tooltip">Partager sur WhatsApp</span>
    </a>
</body>
</html> 