<?php
// backend/models/User.php

require_once __DIR__ . '/../config/database.php'; // Include the database connection function

class User {
    private $conn;

    public function __construct() {
        // Establish database connection when a User object is created
        $this->conn = getDbConnection();
    }

    /**
     * Finds a user by their email address.
     *
     * @param string $email The email address of the user.
     * @return array|null The user's data as an associative array, or null if not found.
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT id, first_name, middle_name, last_name, employee_id, email, password_hash, department, is_admin FROM users WHERE email = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }

        $stmt->bind_param("s", $email); // 's' denotes string type
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return user data
        } else {
            return null; // User not found
        }
    }

    /**
     * Finds a user by their employee ID.
     *
     * @param string $employeeId The employee ID of the user.
     * @return array|null The user's data as an associative array, or null if not found.
     */
    public function findByEmployeeId(string $employeeId): ?array {
        $sql = "SELECT id FROM users WHERE employee_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }

        $stmt->bind_param("s", $employeeId); // 's' denotes string type
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return user data (only ID is needed for existence check)
        } else {
            return null; // User not found
        }
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
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        // 's' for string, 'i' for integer (for is_admin, which will be 0 or 1)
        $is_admin_int = $isAdmin ? 1 : 0;
        $stmt->bind_param("sssssssi", $firstName, $middleName, $lastName, $employeeId, $email, $passwordHash, $department, $is_admin_int);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            return false;
        }
    }

    // Close the database connection when the object is destroyed
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
