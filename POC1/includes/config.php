<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Database connection
$dbPath = $_ENV['DB_PATH'] ?? 'storage/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create donations table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS donations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        amount REAL NOT NULL,
        transaction_id TEXT NOT NULL,
        payment_status TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    error_log('DB Connection Error: ' . $e->getMessage());
    die('Database connection failed.');
}

// Paystack credentials
$paystack_secret_key = $_ENV['PAYSTACK_SECRET_KEY'] ?? '';
$paystack_public_key = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '';
$callback_url = $_ENV['CALLBACK_URL'] ?? '';
$paystack_webhook_secret = $_ENV['PAYSTACK_WEBHOOK_SECRET'] ?? '';
?>
