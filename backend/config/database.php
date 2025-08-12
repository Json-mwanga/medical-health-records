<?php
// backend/config/database.php

// Load environment variables (you might need a library like 'dotenv' or manually parse)
// For simplicity in a WAMP setup, we'll assume direct access to these if they're set
// in Apache config or if you manually load them.
// A more robust solution for production would use a Composer package like 'vlucas/phpdotenv'.

// For now, let's manually define the connection details from what would be in .env
// In a real application, you'd load these securely.
// If you have a .env parser, replace these with getenv() calls.
$dbHost = getenv('DB_HOST') ?: 'localhost'; // Default to 'localhost' if not set
$dbUser = getenv('DB_USER') ?: 'root';     // Default to 'root'
$dbPass = getenv('DB_PASSWORD') ?: '';     // Default to empty password
$dbName = getenv('DB_NAME') ?: 'medical_records_db'; // Default database name
$charset = 'utf8mb4';

$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Log the error and return a generic message
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}
?>
