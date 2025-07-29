<?php
// backend/public/index.php

// Set headers for CORS (Cross-Origin Resource Sharing)
// This is crucial for your frontend (running on file:// or localhost:port) to communicate with your backend.
// In a production environment, you would restrict 'Access-Control-Allow-Origin' to your specific frontend URL.
header("Access-Control-Allow-Origin: *"); // Allows requests from any origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allowed request headers
header("Content-Type: application/json"); // Default content type for API responses

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include necessary configuration and routes
require_once __DIR__ . '/../config/database.php'; // Database connection
require_once __DIR__ . '/../routes/auth_routes.php'; // Authentication routes

// Simple Router
// Get the requested URI (e.g., /api/auth/signup)
// We remove any base path if the script is not at the root (like /medical-health-records/backend/public)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Determine the base path for routing
// This helps if your project is not directly in www/ but in a subfolder like www/medical-health-records/
// Adjust this if your actual WampServer setup changes the base URL
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptName); // e.g., /medical-health-records/backend/public
$basePath = str_replace('\\', '/', $scriptDir); // Ensure forward slashes

// Remove the base path from the request URI to get the actual route
if (strpos($requestUri, $basePath) === 0) {
    $route = substr($requestUri, strlen($basePath));
} else {
    $route = $requestUri; // Fallback if base path logic doesn't apply
}

// Clean up route: remove trailing slash, handle empty route for root
$route = rtrim($route, '/');
if (empty($route)) {
    $route = '/'; // Represent the root of the API
}


// Dispatch the request based on method and route
// This array defines our API routes. Each entry maps a method and a path to a handler function.
// The handlers are defined in routes/auth_routes.php, which we included above.
$routes = [
    'POST' => [
        '/auth/signup' => 'handleSignup', // Maps POST /auth/signup to handleSignup() function
        '/auth/login' => 'handleLogin',   // Maps POST /auth/login to handleLogin() function
    ],
    // Add other methods (GET, PUT, DELETE) and their routes here as needed
];

// Find the handler for the current request
if (isset($routes[$requestMethod])) {
    if (array_key_exists($route, $routes[$requestMethod])) {
        // Call the corresponding handler function
        $handler = $routes[$requestMethod][$route];
        $handler(); // Execute the function (e.g., handleSignup() or handleLogin())
    } else {
        // Route not found for the given method
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API Endpoint Not Found']);
    }
} else {
    // Method not allowed for any route
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>
