<?php
require_once 'config/database.php';
require_once 'includes/utils.php';

class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $created_at;
    public $updated_at;

    /**
     * Constructor with DB connection
     * @param PDO $db
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register a new user
     * @return bool
     */
    public function register() {
        // Sanitize inputs
        $this->name = Utils::cleanInput($this->name);
        $this->email = Utils::cleanInput($this->email);
        
        // Check if email already exists
        if ($this->emailExists()) {
            return false;
        }
        
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, password=:password";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if email exists
     * @return bool
     */
    public function emailExists() {
        // Query to check if email exists
        $query = "SELECT id, name, password
                  FROM " . $this->table_name . " 
                  WHERE email = ?
                  LIMIT 0,1";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the email
        $stmt->bindParam(1, $this->email);
        
        // Execute query
        $stmt->execute();
        
        // Get number of rows
        $num = $stmt->rowCount();
        
        // If email exists, assign values to object properties for easy access
        if ($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Assign values to object properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->password = $row['password'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Login user
     * @return bool
     */
    public function login() {
        // Check if email exists
        if ($this->emailExists()) {
            // Verify password
            if (password_verify($this->password, $this->password)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get user by ID
     * @param int $id
     * @return bool
     */
    public function getUserById($id) {
        // Query to get user by ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind ID
        $stmt->bindParam(1, $id);
        
        // Execute query
        $stmt->execute();
        
        // Get record
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Set properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Update user profile
     * @return bool
     */
    public function update() {
        // Sanitize inputs
        $this->name = Utils::cleanInput($this->name);
        $this->email = Utils::cleanInput($this->email);
        $this->id = Utils::cleanInput($this->id);
        
        // Query to update user
        $query = "UPDATE " . $this->table_name . "
                  SET name = :name, email = :email
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Change password
     * @param string $current_password
     * @param string $new_password
     * @return bool
     */
    public function changePassword($current_password, $new_password) {
        // Get user by ID
        $this->getUserById($this->id);
        
        // Verify current password
        if (password_verify($current_password, $this->password)) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update password
            $query = "UPDATE " . $this->table_name . "
                      SET password = :password
                      WHERE id = :id";
            
            // Prepare query
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $this->id);
            
            // Execute query
            if ($stmt->execute()) {
                return true;
            }
        }
        
        return false;
    }
}
?> 