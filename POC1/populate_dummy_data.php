<?php
require 'includes/config.php';

// Define an array of dummy donations
$dummyDonations = [
    [
        'name' => 'Amina Okafor',
        'email' => 'amina.okafor@example.com',
        'amount' => 5000.00,
        'transaction_id' => 'txn_dummy_001',
        'payment_status' => 'success'
    ],
    [
        'name' => 'Kwame Nkrumah',
        'email' => 'kwame.nkrumah@example.com',
        'amount' => 7500.50,
        'transaction_id' => 'txn_dummy_002',
        'payment_status' => 'success'
    ],
    [
        'name' => 'Fatima Ibrahim',
        'email' => 'fatima.ibrahim@example.com',
        'amount' => 3000.75,
        'transaction_id' => 'txn_dummy_003',
        'payment_status' => 'success'
    ],
    [
        'name' => 'Juma Mwinyi',
        'email' => 'juma.mwinyi@example.com',
        'amount' => 4500.25,
        'transaction_id' => 'txn_dummy_004',
        'payment_status' => 'success'
    ],
    [
        'name' => 'Zara Suleiman',
        'email' => 'zara.suleiman@example.com',
        'amount' => 6000.00,
        'transaction_id' => 'txn_dummy_005',
        'payment_status' => 'success'
    ],
];

try {
    $stmt = $pdo->prepare("INSERT INTO donations (name, email, amount, transaction_id, payment_status) VALUES (?, ?, ?, ?, ?)");

    foreach ($dummyDonations as $donation) {
        $stmt->execute([
            $donation['name'],
            $donation['email'],
            $donation['amount'],
            $donation['transaction_id'],
            $donation['payment_status']
        ]);
    }

    echo "Dummy donations have been successfully inserted into the database.";
} catch (PDOException $e) {
    echo "Error inserting dummy data: " . $e->getMessage();
}
?>
