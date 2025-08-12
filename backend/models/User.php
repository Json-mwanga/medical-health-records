<?php
// backend/models/User.php

require_once __DIR__ . '/../config/database.php'; // Provides $pdo

class User {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Finds a user by their email address.
     *
     * @param string $email The email address of the user.
     * @return array|null The user's data as an associative array, or null if not found.
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT id, first_name, middle_name, last_name, employee_id, email, password_hash, department, is_admin FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Finds a user by their employee ID.
     *
     * @param string $employeeId The employee ID of the user.
     * @return array|null The user's data as an associative array, or null if not found.
     */
    public function findByEmployeeId(string $employeeId): ?array {
        $sql = "SELECT id FROM users WHERE employee_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$employeeId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Creates a new user in the database.
     *
     * @param string $firstName
     * @param string|null $middleName
     * @param string $lastName
     * @param string $employeeId
     * @param string $email
     * @param string $passwordHash The securely hashed password.
     * @param string $department
     * @param bool $isAdmin
     * @return bool True on success, false on failure.
     */
    public function create(string $firstName, ?string $middleName, string $lastName, string $employeeId, string $email, string $passwordHash, string $department, bool $isAdmin): bool {
        $sql = "INSERT INTO users (first_name, middle_name, last_name, employee_id, email, password_hash, department, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $is_admin_int = $isAdmin ? 1 : 0;
        return $stmt->execute([$firstName, $middleName, $lastName, $employeeId, $email, $passwordHash, $department, $is_admin_int]);
    }
}
?>
