<?php
// Include header
require_once 'includes/header.php';

// Include database and models
require_once 'config/database.php';
require_once 'models/Expense.php';
require_once 'models/Category.php';
require_once 'includes/utils.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$expense = new Expense($db);
$category = new Category($db);

// Get user ID from session
$user_id = $_SESSION["user_id"];

// Set default action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle actions
switch ($action) {
    case 'add':
        // Display add expense form
        include 'views/expense_form.php';
        break;
        
    case 'edit':
        // Check if ID parameter exists
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // Get expense ID
            $expense_id = $_GET['id'];
            
            // Get expense details
            if ($expense->getById($expense_id)) {
                // Check if expense belongs to the logged-in user
                if ($expense->user_id == $user_id) {
                    // Display edit expense form
                    include 'views/expense_form.php';
                } else {
                    // Redirect with error
                    header("Location: expenses.php?error=unauthorized");
                    exit();
                }
            } else {
                // Redirect with error
                header("Location: expenses.php?error=invalid_id");
                exit();
            }
        } else {
            // Redirect with error
            header("Location: expenses.php?error=invalid_id");
            exit();
        }
        break;
        
    case 'delete':
        // Check if ID parameter exists
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // Get expense ID
            $expense_id = $_GET['id'];
            
            // Set expense properties
            $expense->id = $expense_id;
            $expense->user_id = $user_id;
            
            // Delete expense
            if ($expense->delete()) {
                // Redirect with success message
                header("Location: expenses.php?success=expense_deleted");
                exit();
            } else {
                // Redirect with error
                header("Location: expenses.php?error=delete_failed");
                exit();
            }
        } else {
            // Redirect with error
            header("Location: expenses.php?error=invalid_id");
            exit();
        }
        break;
        
    case 'save':
        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Sanitize and validate inputs
            $expense->user_id = $user_id;
            $expense->category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
            $expense->amount = isset($_POST['amount']) ? $_POST['amount'] : '';
            $expense->description = isset($_POST['description']) ? $_POST['description'] : '';
            $expense->expense_date = isset($_POST['expense_date']) ? $_POST['expense_date'] : '';
            
            // Check if it's an update or create
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update existing expense
                $expense->id = $_POST['id'];
                
                // Update expense
                if ($expense->update()) {
                    // Redirect with success message
                    header("Location: expenses.php?success=expense_updated");
                    exit();
                } else {
                    // Redirect with error
                    header("Location: expenses.php?error=update_failed");
                    exit();
                }
            } else {
                // Create new expense
                if ($expense->create()) {
                    // Redirect with success message
                    header("Location: expenses.php?success=expense_added");
                    exit();
                } else {
                    // Redirect with error
                    header("Location: expenses.php?error=create_failed");
                    exit();
                }
            }
        }
        break;
        
    default:
        // Display expenses list
        // Check for filters
        $filter_category = isset($_GET['category_id']) ? $_GET['category_id'] : null;
        $filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        
        // Get expenses based on filters
        if ($filter_category) {
            $expenses = $expense->getByCategory($user_id, $filter_category);
        } elseif ($filter_start_date && $filter_end_date) {
            $expenses = $expense->getByDateRange($user_id, $filter_start_date, $filter_end_date);
        } else {
            $expenses = $expense->getAllByUser($user_id);
        }
        
        // Get all categories for filter dropdown
        $categories = $category->getAll();
        
        // Include expenses list view
        include 'views/expense_list.php';
        break;
}

// Include footer
require_once 'includes/footer.php';
?> 