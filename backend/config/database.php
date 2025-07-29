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

/**
 * Establishes and returns a new MySQLi database connection.
 *
 * @return mysqli Returns a MySQLi object on successful connection.
 * @throws Exception if the connection fails.
 */
function getDbConnection(): mysqli {
    global $dbHost, $dbUser, $dbPass, $dbName;

    // Enable error reporting for MySQLi
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        // Set the charset to UTF-8 for proper character handling
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (mysqli_sql_exception $e) {
        // Log the error (in a real app, don't show detailed error to user)
        error_log("Database connection failed: " . $e->getMessage());
        // For development, you can die with the error. In production, show a generic message.
        die("Connection to database failed: " . $e->getMessage());
    }
}

// Example usage (for testing connection - remove in production)
// try {
//     $conn = getDbConnection();
//     echo "Database connected successfully!";
//     $conn->close();
// } catch (Exception $e) {
//     echo $e->getMessage();
// }

?>
