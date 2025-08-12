<?php
// backend/controllers/AuthController.php

require_once __DIR__ . '/../models/User.php'; // Include the User model

header('Content-Type: application/json');

/**
 * Handles user signup requests.
 * Reads data from the request body, validates it, creates a new user,
 * and sends back a JSON response.
 */
function handleSignup() {
    // Get raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $data = array_map('trim', $data);

    // Basic validation
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['employeeId']) || empty($data['email']) || empty($data['department']) || empty($data['password']) || empty($data['confirmPassword'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        http_response_code(400); // Bad Request
        return;
    }

    if ($data['password'] !== $data['confirmPassword']) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        http_response_code(400); // Bad Request
        return;
    }

    if (strlen($data['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
        http_response_code(400); // Bad Request
        return;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        http_response_code(400);
        return;
    }

    $userModel = new User();

    // Check if email or employee ID already exists
    if ($userModel->findByEmail($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        http_response_code(409); // Conflict
        return;
    }
    if ($userModel->findByEmployeeId($data['employeeId'])) {
        echo json_encode(['success' => false, 'message' => 'Employee ID already registered.']);
        http_response_code(409); // Conflict
        return;
    }

    // Hash the password securely
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // Determine if the user is an admin based on department
    $isAdmin = ($data['department'] === 'Administration');

    // Create the user
    $success = $userModel->create(
        $data['firstName'],
        $data['middleName'] ?? null, // Middle name is optional
        $data['lastName'],
        $data['employeeId'],
        $data['email'],
        $hashedPassword,
        $data['department'],
        $isAdmin
    );

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
        http_response_code(201); // Created
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
        http_response_code(500); // Internal Server Error
    }
}

/**
 * Handles user login requests.
 * Reads data from the request body, validates credentials,
 * and sends back a JSON response with user info or an error.
 */
function handleLogin() {
    // Get raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $data = array_map('trim', $data);

    // Basic validation
    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Please provide email and password.']);
        http_response_code(400); // Bad Request
        return;
    }

    $userModel = new User();
    $user = $userModel->findByEmail($data['email']);

    // Verify user existence and password
    if ($user && password_verify($data['password'], $user['password_hash'])) {
        // Login successful
        // Prepare user data to send back to frontend (EXCLUDE password_hash!)
        $responseUser = [
            'fullName' => trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']),
            'firstName' => $user['first_name'],
            'middleName' => $user['middle_name'],
            'lastName' => $user['last_name'],
            'employeeId' => $user['employee_id'],
            'department' => $user['department'],
            'isAdmin' => (bool)$user['is_admin'], // Ensure boolean type
        ];

        echo json_encode(['success' => true, 'message' => 'Login successful!', 'user' => $responseUser]);
        http_response_code(200); // OK
    } else {
        // Invalid credentials
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        http_response_code(401); // Unauthorized
    }
}
?>
