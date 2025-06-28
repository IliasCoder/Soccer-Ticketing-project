<?php
require_once 'vendor/autoload.php';
require_once 'database.php';
require_once 'TicketGenerator.php';

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Récupérer l'ID de la commande depuis l'URL
$orderId = isset($_GET['order']) ? intval($_GET['order']) : null;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Récupérer les informations de la commande et des tickets
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email,
               t.id as ticket_id, t.match_id, t.category_id, t.section_id, t.seat_number, t.price,
               m.home_team, m.away_team, m.match_date, m.match_time, m.stadium
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN tickets t ON t.order_id = o.id
        JOIN matches m ON t.match_id = m.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tickets)) {
        throw new Exception("Commande non trouvée");
    }
    
    // Si on demande un téléchargement spécifique
    if (isset($_GET['download'])) {
        $ticketId = intval($_GET['download']);
        $ticketFound = false;
        
        foreach ($tickets as $ticket) {
            if ($ticket['ticket_id'] == $ticketId) {
                $ticketFound = true;
                $ticketData = [
                    'id' => $ticket['ticket_id'],
                    'match_name' => "{$ticket['home_team']} vs {$ticket['away_team']}",
                    'match_date' => $ticket['match_date'],
                    'match_time' => $ticket['match_time'],
                    'stadium' => $ticket['stadium'],
                    'category_id' => $ticket['category_id'],
                    'section_id' => $ticket['section_id'],
                    'seat_number' => $ticket['seat_number'],
                    'price' => $ticket['price']
                ];
                
                $generator = new TicketGenerator();
                $pdfContent = $generator->generateTicketPDF($ticketData);
                
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="ticket_' . $ticketId . '.pdf"');
                echo $pdfContent;
                exit;
            }
        }
        
        if (!$ticketFound) {
            throw new Exception("Ticket non trouvé");
        }
    }
    
    // Afficher la page HTML
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Téléchargement des Tickets</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .ticket-card {
                transition: transform 0.2s;
            }
            .ticket-card:hover {
                transform: translateY(-5px);
            }
        </style>
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="mb-4">Vos Tickets</h1>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Détails de la commande #<?php echo $orderId; ?></h5>
                            <p class="card-text">
                                <strong>Client :</strong> <?php echo htmlspecialchars($tickets[0]['username']); ?><br>
                                <strong>Email :</strong> <?php echo htmlspecialchars($tickets[0]['email']); ?><br>
                                <strong>Total :</strong> <?php echo number_format($tickets[0]['total_amount'], 2); ?> €
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($tickets as $ticket): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 ticket-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars("{$ticket['home_team']} vs {$ticket['away_team']}"); ?></h5>
                            <p class="card-text">
                                <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($ticket['match_date'])); ?><br>
                                <strong>Heure :</strong> <?php echo substr($ticket['match_time'], 0, 5); ?><br>
                                <strong>Stade :</strong> <?php echo htmlspecialchars($ticket['stadium']); ?><br>
                                <strong>Section :</strong> <?php echo htmlspecialchars($ticket['section_id']); ?><br>
                                <strong>Place :</strong> <?php echo htmlspecialchars($ticket['seat_number']); ?><br>
                                <strong>Prix :</strong> <?php echo number_format($ticket['price'], 2); ?> €
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="?order=<?php echo $orderId; ?>&download=<?php echo $ticket['ticket_id']; ?>" 
                               class="btn btn-primary w-100">
                                Télécharger le PDF
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4 class="alert-heading">Instructions :</h4>
                        <p>1. Téléchargez vos tickets en PDF en cliquant sur les boutons ci-dessus</p>
                        <p>2. Imprimez vos tickets ou gardez-les sur votre téléphone</p>
                        <p>3. Présentez-les à l'entrée du stade le jour du match</p>
                        <p>4. Une pièce d'identité pourra vous être demandée</p>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    if (isset($_GET['download'])) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erreur</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container py-5">
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <h4 class="alert-heading">Erreur</h4>
                            <p><?php echo htmlspecialchars($e->getMessage()); ?></p>
                            <hr>
                            <p class="mb-0">
                                <a href="javascript:history.back()" class="btn btn-outline-danger">Retour</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
?> 