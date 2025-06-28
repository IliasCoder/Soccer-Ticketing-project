function processPayment(customerInfo, cartItems, totalAmount) {
    // Show loading indicator
    showLoadingIndicator();

    // Prepare payment data
    const paymentData = {
        customerInfo: customerInfo,
        cartItems: cartItems,
        totalAmount: totalAmount
    };

    // Send payment request
    fetch('payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingIndicator();
        
        if (data.success) {
            // Generate PDF ticket
            fetch('generate_ticket_pdf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    orderId: data.orderId,
                    matchId: cartItems[0].matchId,
                    matchName: cartItems[0].matchName,
                    matchDate: cartItems[0].matchDate,
                    matchTime: cartItems[0].matchTime,
                    venue: cartItems[0].venue,
                    customerName: customerInfo.username,
                    categoryName: cartItems[0].categoryName,
                    sectionName: cartItems[0].sectionName,
                    seatNumber: cartItems[0].seatNumber,
                    price: cartItems[0].price
                })
            })
            .then(response => response.json())
            .then(pdfData => {
                if (pdfData.success) {
                    // Redirect to the PDF viewer
                    window.location.href = pdfData.viewUrl;
                } else {
                    showNotification('Erreur lors de la génération du PDF: ' + pdfData.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Erreur: ' + error.message, 'error');
                console.error('PDF generation error:', error);
            });
        } else {
            showNotification('Erreur: ' + (data.message || 'Une erreur est survenue lors du paiement.'), 'error');
        }
    })
    .catch(error => {
        hideLoadingIndicator();
        showNotification('Erreur: ' + error.message, 'error');
        console.error('Payment error:', error);
    });
} 
