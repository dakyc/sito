<?php
// process_payment.php

// Includi Stripe PHP library
require 'vendor/autoload.php'; // Assicurati di aver installato la libreria Stripe via Composer

\Stripe\Stripe::setApiKey('sk_test_your_secret_key'); // Inserisci la tua chiave segreta di Stripe

// Recupera i dati POST
$data = json_decode(file_get_contents('php://input'), true);
$paymentMethodId = $data['paymentMethodId'];
$amount = $data['amount'];
$cardholderName = $data['cardholderName']; // Aggiunto per ottenere il nome del titolare della carta

// Funzione per inviare i dettagli via email
function sendEmail($subject, $message) {
    $to = 'chiurloden@gmail.com'; // La tua email
    mail($to, $subject, $message);
}

// Crea il pagamento
try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'eur',
        'payment_method' => $paymentMethodId,
        'confirmation_method' => 'manual',
        'confirm' => true,
    ]);

    // Se il pagamento è riuscito, invia i dettagli via email
    if ($paymentIntent->status === 'succeeded') {
        $subject = 'Dettagli del pagamento riuscito';
        $message = "Pagamento ricevuto:\n";
        $message .= "Nome del titolare: $cardholderName\n";
        $message .= "Importo: " . ($amount / 100) . "€\n"; // Converte in euro
        $message .= "ID del pagamento: " . $paymentIntent->id . "\n";
        $message .= "Metodo di pagamento: " . $paymentMethodId . "\n"; // Opzionale: includi l'ID del metodo di pagamento

        // Invia l'email
        sendEmail($subject, $message);

        // Restituisci una risposta JSON positiva
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Pagamento non riuscito.');
    }
} catch (Exception $e) {
    // Invia i dettagli dell'errore via email
    $subject = 'Errore nel pagamento';
    $message = "Errore nel pagamento:\n";
    $message .= "Nome del titolare: $cardholderName\n";
    $message .= "Importo: " . ($amount / 100) . "€\n"; // Converte in euro
    $message .= "Errore: " . $e->getMessage() . "\n";

    // Invia l'email
    sendEmail($subject, $message);

    // Gestisci gli errori
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
