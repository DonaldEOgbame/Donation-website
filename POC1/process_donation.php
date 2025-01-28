<?php
session_start();
require 'includes/config.php';

function clean($data){
  return htmlspecialchars(strip_tags(trim($data)));
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $name = clean($_POST['name'] ?? '');
  $email = clean($_POST['email'] ?? '');
  $amount = clean($_POST['amount'] ?? '');
  $errors = [];

  // Validate Name
  if(!$name){
    $errors[] = 'Name is required.';
  } elseif(strlen($name) < 2){
    $errors[] = 'Name must be at least 2 characters.';
  }

  // Validate Email
  if(!$email){
    $errors[] = 'Email is required.';
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors[] = 'Invalid email format.';
  }

  // Validate Amount
  if(!$amount){
    $errors[] = 'Donation amount is required.';
  } elseif(!is_numeric($amount) || $amount < 100){
    $errors[] = 'Minimum donation is NGN 100.';
  }

  // Handle Validation Errors
  if($errors){
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: index.php');
    exit;
  }

  // Prepare Data for Paystack
  $postData = [
    'email' => $email,
    'amount' => $amount * 100, // Convert to kobo
    'metadata' => ['name' => $name],
    'callback_url' => $callback_url,
    'reference' => 'donate_' . uniqid()
  ];

  // Initialize cURL to Paystack
  $curl = curl_init('https://api.paystack.co/transaction/initialize');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
  curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $paystack_secret_key",
    "Content-Type: application/json"
  ]);
  $resp = curl_exec($curl);

  // Handle cURL Errors
  if(curl_errno($curl)){
    error_log('cURL Error: ' . curl_error($curl));
    $_SESSION['error'] = 'Payment initialization failed. Please try again.';
    header('Location: index.php');
    exit;
  }
  curl_close($curl);

  // Decode Paystack Response
  $decoded = json_decode($resp, true);
  if(!isset($decoded['status']) || !$decoded['status']){
    $_SESSION['error'] = 'Payment initialization error: ' . ($decoded['message'] ?? 'Unknown error.');
    header('Location: index.php');
    exit;
  }

  // Redirect to Paystack's Payment Page
  header('Location: ' . $decoded['data']['authorization_url']);
  exit;
}

// Redirect to form if accessed directly
header('Location: index.php');
exit;
?>
