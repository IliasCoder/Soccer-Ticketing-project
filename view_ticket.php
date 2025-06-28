<?php
if (!isset($_GET['filename'])) {
    die('No ticket specified');
}

$filename = basename($_GET['filename']); // Sanitize filename
$filepath = __DIR__ . DIRECTORY_SEPARATOR . 'tickets' . DIRECTORY_SEPARATOR . $filename;

if (!file_exists($filepath) || pathinfo($filepath, PATHINFO_EXTENSION) !== 'pdf') {
    die('PDF not found');
}

// If direct PDF request
if (isset($_GET['direct'])) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Cache-Control: public, max-age=0, must-revalidate');
    readfile($filepath);
    exit;
}

// Get the absolute URL for sharing
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host . dirname($_SERVER['PHP_SELF']);
$pdfUrl = $baseUrl . '/tickets/' . urlencode($filename);

// For debugging
error_log("File path: " . $filepath);
error_log("PDF URL: " . $pdfUrl);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Billet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .header {
            background-color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .back-button {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #f0f0f0;
        }

        .whatsapp-button {
            background-color: #25D366;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .whatsapp-button:hover {
            background-color: #128C7E;
        }

        .whatsapp-button i {
            font-size: 18px;
        }

        .pdf-container {
            margin-top: 60px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .pdf-viewer {
            width: 100%;
            max-width: 1000px;
            height: calc(100vh - 100px);
            border: none;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .header {
                padding: 10px 15px;
            }

            .whatsapp-button {
                padding: 6px 12px;
            }

            .pdf-container {
                padding: 10px;
            }

            .pdf-viewer {
                height: calc(100vh - 80px);
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Retour
        </a>
        <a href="#" class="whatsapp-button" id="whatsappShare">
            <i class="fab fa-whatsapp"></i>
            Partager sur WhatsApp
        </a>
    </div>
    <div class="pdf-container">
        <iframe src="tickets/<?php echo htmlspecialchars($filename); ?>" class="pdf-viewer"></iframe>
    </div>

    <script>
    // Check if running on mobile
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        // For mobile devices
        document.getElementById('whatsappShare').href = 
            `whatsapp://send?text=Voici mon billet ! ${encodeURIComponent('<?php echo $pdfUrl; ?>')}`;
    } else {
        // For desktop devices
        document.getElementById('whatsappShare').href = 
            `https://web.whatsapp.com/send?text=Voici mon billet ! ${encodeURIComponent('<?php echo $pdfUrl; ?>')}`;
    }
    </script>
</body>
</html> 