<?php
function generateEmailTemplate($orderDetails) {
    $customerName = $orderDetails['customerName'];
    $orderId = $orderDetails['orderId'];
    $totalAmount = $orderDetails['totalAmount'];
    $items = $orderDetails['items'];
    
    $itemsHtml = '';
    foreach ($items as $item) {
        $itemsHtml .= "
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #eee;'>
                    <strong>{$item['matchName']}</strong><br>
                    <small>Date: {$item['matchDate']}</small><br>
                    <small>Heure: {$item['matchTime']}</small>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #eee;'>
                    {$item['categoryName']}<br>
                    Section: {$item['sectionName']}<br>
                    Place: {$item['seatNumber']}
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>
                    {$item['quantity']} x {$item['price']}€
                </td>
            </tr>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Confirmation de Commande</title>
    </head>
    <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; margin-top: 20px;'>
            <div style='text-align: center; padding: 20px;'>
                <h1 style='color: #333; margin-bottom: 10px;'>🎫 Confirmation de Commande</h1>
                <p style='color: #666;'>Merci pour votre achat!</p>
            </div>

            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                <h2 style='color: #333; margin-bottom: 15px;'>Détails de la Commande #$orderId</h2>
                <p><strong>Client:</strong> $customerName</p>
                <p><strong>Date:</strong> " . date('d/m/Y H:i') . "</p>
            </div>

            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                <thead>
                    <tr style='background-color: #f8f9fa;'>
                        <th style='padding: 12px; text-align: left;'>Match</th>
                        <th style='padding: 12px; text-align: left;'>Détails</th>
                        <th style='padding: 12px; text-align: right;'>Prix</th>
                    </tr>
                </thead>
                <tbody>
                    $itemsHtml
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='2' style='padding: 12px; text-align: right;'><strong>Total:</strong></td>
                        <td style='padding: 12px; text-align: right;'><strong>{$totalAmount}€</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div style='background-color: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                <h3 style='color: #333; margin-bottom: 15px;'>Instructions</h3>
                <p>1. Présentez votre confirmation de commande à l'entrée du stade</p>
                <p>2. Arrivez au moins 30 minutes avant le début du match</p>
                <p>3. Conservez votre ticket tout au long de l'événement</p>
            </div>

            <div style='text-align: center; padding: 20px; color: #666;'>
                <p>Pour toute question, contactez notre support:</p>
                <p>📧 support@billetterie-sportive.com</p>
                <p>📞 01 23 45 67 89</p>
            </div>
        </div>
    </body>
    </html>";
}
?> 