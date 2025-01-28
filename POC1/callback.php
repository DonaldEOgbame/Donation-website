<?php
session_start();
require 'includes/config.php';

function clean($data){
  return htmlspecialchars(strip_tags(trim($data)));
}

function verify_signature($payload, $signature, $secret){
    $computed_signature = hash_hmac('sha512', $payload, $secret);
    return hash_equals($computed_signature, $signature);
}

$secret = $paystack_webhook_secret;

// Handle Webhook (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

    // Verify Webhook Signature
    if ($secret && !verify_signature($payload, $signature, $secret)) {
        error_log('Webhook signature verification failed.');
        http_response_code(403);
        exit('Forbidden');
    }

    $decoded = json_decode($payload, true);

    if(isset($decoded['event']) && $decoded['event'] === 'charge.success'){
        $data = $decoded['data'];
        $transaction_id = $data['id'];
        $payment_status = $data['status'];
        $amount = $data['amount'] / 100; // Convert kobo to NGN
        $email = clean($data['customer']['email']);
        $name = clean($data['metadata']['name'] ?? 'Anonymous');

        try{
            // Check if transaction_id already exists to prevent duplicates
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE transaction_id = ?");
            $checkStmt->execute([$transaction_id]);
            $exists = $checkStmt->fetchColumn();

            if(!$exists){
                // Insert the donation as it doesn't exist
                $stmt = $pdo->prepare("INSERT INTO donations(name, email, amount, transaction_id, payment_status) VALUES(?,?,?,?,?)");
                $stmt->execute([$name, $email, $amount, $transaction_id, $payment_status]);
            }
        } catch(PDOException $e){
            error_log('DB Insert Error: ' . $e->getMessage());
            http_response_code(500);
            exit('Internal Server Error');
        }
    }

    // Respond with 200 OK to acknowledge receipt
    http_response_code(200);
    exit('OK');
}

// Handle User Redirection (GET Request)
if(isset($_GET['reference'])){
    $ref = clean($_GET['reference']);

    // Initialize cURL to verify the transaction
    $verifyCurl = curl_init("https://api.paystack.co/transaction/verify/$ref");
    curl_setopt($verifyCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($verifyCurl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $paystack_secret_key",
        "Content-Type: application/json"
    ]);

    $resp = curl_exec($verifyCurl);

    if(curl_errno($verifyCurl)){
        error_log('Verification cURL Error: ' . curl_error($verifyCurl));
        $_SESSION['error'] = 'Payment verification failed. Please contact support.';
        header('Location: index.php');
        exit;
    }

    curl_close($verifyCurl);

    $decoded = json_decode($resp, true);

    if(isset($decoded['status']) && $decoded['status'] === true && isset($decoded['data']['status']) && $decoded['data']['status'] === 'success'){
        $transaction_id = $decoded['data']['id'];
        $payment_status = $decoded['data']['status'];
        $amount = $decoded['data']['amount'] / 100; // Convert kobo to NGN
        $email = clean($decoded['data']['customer']['email']);
        $name = clean($decoded['data']['metadata']['name'] ?? 'Anonymous');

        try{
            // Check if transaction_id already exists to prevent duplicates
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE transaction_id = ?");
            $checkStmt->execute([$transaction_id]);
            $exists = $checkStmt->fetchColumn();

            if(!$exists){
                // Insert the donation as it doesn't exist
                $stmt = $pdo->prepare("INSERT INTO donations(name, email, amount, transaction_id, payment_status) VALUES(?,?,?,?,?)");
                $stmt->execute([$name, $email, $amount, $transaction_id, $payment_status]);
            }
        } catch(PDOException $e){
            error_log('DB Insert Error: ' . $e->getMessage());
            $_SESSION['error'] = 'Could not record donation. Please contact support.';
            header('Location: index.php');
            exit;
        }

        $_SESSION['success'] = 'Thank you for your generous donation! Together, we are making a difference in Africa.';
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Payment was not successful. Please try again.';
        header('Location: index.php');
        exit;
    }
}

$_SESSION['error'] = 'No payment reference provided.';
header('Location: index.php');
exit;
?>
