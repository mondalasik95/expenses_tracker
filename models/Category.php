<?php
require_once 'config/database.php';
require_once 'includes/utils.php';

class Category {
    // Database connection and table name
    private $conn;
    private $table_name = "categories";

    // Object properties
    public $id;
    public $name;
    public $description;
    public $created_at;

    /**
     * Constructor with DB connection
     * @param PDO $db
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all categories
     * @return PDOStatement
     */
    public function getAll() {
        // Query to select all categories
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get category by ID
     * @param int $id
     * @return bool
     */
    public function getById($id) {
        // Query to get category by ID
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
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Create a new category
     * @return bool
     */
    public function create() {
        // Sanitize inputs
        $this->name = Utils::cleanInput($this->name);
        $this->description = Utils::cleanInput($this->description);
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, description=:description";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Update category
     * @return bool
     */
    public function update() {
        // Sanitize inputs
        $this->id = Utils::cleanInput($this->id);
        $this->name = Utils::cleanInput($this->name);
        $this->description = Utils::cleanInput($this->description);
        
        // Query to update category
        $query = "UPDATE " . $this->table_name . "
                  SET name = :name, description = :description
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Delete category
     * @return bool
     */
    public function delete() {
        // Query to delete category
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize ID
        $this->id = Utils::cleanInput($this->id);
        
        // Bind ID
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if category has expenses
     * @return bool
     */
    public function hasExpenses() {
        // Query to check if category has expenses
        $query = "SELECT COUNT(*) as count FROM expenses WHERE category_id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind ID
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get record
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'] > 0;
    }
}
?> 