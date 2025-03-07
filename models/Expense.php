<?php
require_once 'config/database.php';
require_once 'includes/utils.php';

class Expense {
    // Database connection and table name
    private $conn;
    private $table_name = "expenses";

    // Object properties
    public $id;
    public $user_id;
    public $category_id;
    public $amount;
    public $description;
    public $expense_date;
    public $created_at;
    public $updated_at;

    // Additional properties for joins
    public $category_name;

    /**
     * Constructor with DB connection
     * @param PDO $db
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new expense
     * @return bool
     */
    public function create() {
        // Sanitize inputs
        $this->user_id = Utils::cleanInput($this->user_id);
        $this->category_id = Utils::cleanInput($this->category_id);
        $this->amount = Utils::cleanInput($this->amount);
        $this->description = Utils::cleanInput($this->description);
        $this->expense_date = Utils::cleanInput($this->expense_date);
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, category_id=:category_id, amount=:amount, 
                      description=:description, expense_date=:expense_date";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":expense_date", $this->expense_date);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get all expenses for a user
     * @param int $user_id
     * @param string $order_by
     * @param string $order_direction
     * @return PDOStatement
     */
    public function getAllByUser($user_id, $order_by = 'expense_date', $order_direction = 'DESC') {
        // Query to select all expenses for a user with category name
        $query = "SELECT e.*, c.name as category_name 
                  FROM " . $this->table_name . " e
                  LEFT JOIN categories c ON e.category_id = c.id
                  WHERE e.user_id = :user_id
                  ORDER BY e." . $order_by . " " . $order_direction;
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user ID
        $stmt->bindParam(':user_id', $user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get expense by ID
     * @param int $id
     * @return bool
     */
    public function getById($id) {
        // Query to get expense by ID with category name
        $query = "SELECT e.*, c.name as category_name 
                  FROM " . $this->table_name . " e
                  LEFT JOIN categories c ON e.category_id = c.id
                  WHERE e.id = ? LIMIT 0,1";
        
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
            $this->user_id = $row['user_id'];
            $this->category_id = $row['category_id'];
            $this->amount = $row['amount'];
            $this->description = $row['description'];
            $this->expense_date = $row['expense_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->category_name = $row['category_name'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Update expense
     * @return bool
     */
    public function update() {
        // Sanitize inputs
        $this->id = Utils::cleanInput($this->id);
        $this->category_id = Utils::cleanInput($this->category_id);
        $this->amount = Utils::cleanInput($this->amount);
        $this->description = Utils::cleanInput($this->description);
        $this->expense_date = Utils::cleanInput($this->expense_date);
        
        // Query to update expense
        $query = "UPDATE " . $this->table_name . "
                  SET category_id = :category_id, amount = :amount, 
                      description = :description, expense_date = :expense_date
                  WHERE id = :id AND user_id = :user_id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':expense_date', $this->expense_date);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Delete expense
     * @return bool
     */
    public function delete() {
        // Query to delete expense
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND user_id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize ID
        $this->id = Utils::cleanInput($this->id);
        
        // Bind ID and user_id
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get expenses by category for a user
     * @param int $user_id
     * @param int $category_id
     * @return PDOStatement
     */
    public function getByCategory($user_id, $category_id) {
        // Query to select expenses by category for a user
        $query = "SELECT e.*, c.name as category_name 
                  FROM " . $this->table_name . " e
                  LEFT JOIN categories c ON e.category_id = c.id
                  WHERE e.user_id = :user_id AND e.category_id = :category_id
                  ORDER BY e.expense_date DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':category_id', $category_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get expenses by date range for a user
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return PDOStatement
     */
    public function getByDateRange($user_id, $start_date, $end_date) {
        // Query to select expenses by date range for a user
        $query = "SELECT e.*, c.name as category_name 
                  FROM " . $this->table_name . " e
                  LEFT JOIN categories c ON e.category_id = c.id
                  WHERE e.user_id = :user_id 
                  AND e.expense_date BETWEEN :start_date AND :end_date
                  ORDER BY e.expense_date DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get monthly total expenses for a user
     * @param int $user_id
     * @param int $year
     * @return array
     */
    public function getMonthlyTotals($user_id, $year) {
        // Query to get monthly totals
        $query = "SELECT MONTH(expense_date) as month, SUM(amount) as total
                  FROM " . $this->table_name . "
                  WHERE user_id = :user_id AND YEAR(expense_date) = :year
                  GROUP BY MONTH(expense_date)
                  ORDER BY MONTH(expense_date)";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get category totals for a user
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return PDOStatement
     */
    public function getCategoryTotals($user_id, $start_date = null, $end_date = null) {
        // Base query
        $query = "SELECT c.name as category_name, SUM(e.amount) as total
                  FROM " . $this->table_name . " e
                  LEFT JOIN categories c ON e.category_id = c.id
                  WHERE e.user_id = :user_id";
        
        // Add date range if provided
        if ($start_date && $end_date) {
            $query .= " AND e.expense_date BETWEEN :start_date AND :end_date";
        }
        
        // Complete query
        $query .= " GROUP BY e.category_id
                    ORDER BY total DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user ID
        $stmt->bindParam(':user_id', $user_id);
        
        // Bind date range if provided
        if ($start_date && $end_date) {
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
        }
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get total expenses for a user
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return float
     */
    public function getTotalExpenses($user_id, $start_date = null, $end_date = null) {
        // Base query
        $query = "SELECT SUM(amount) as total
                  FROM " . $this->table_name . "
                  WHERE user_id = :user_id";
        
        // Add date range if provided
        if ($start_date && $end_date) {
            $query .= " AND expense_date BETWEEN :start_date AND :end_date";
        }
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user ID
        $stmt->bindParam(':user_id', $user_id);
        
        // Bind date range if provided
        if ($start_date && $end_date) {
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
        }
        
        // Execute query
        $stmt->execute();
        
        // Get record
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? $row['total'] : 0;
    }
}
?> 