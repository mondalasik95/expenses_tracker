<?php
// Include header
require_once 'includes/header.php';

// Include database and models
require_once 'config/database.php';
require_once 'models/Category.php';
require_once 'models/Expense.php';
require_once 'includes/utils.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$category = new Category($db);
$expense = new Expense($db);

// Get user ID from session
$user_id = $_SESSION["user_id"];

// Set default action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle actions
switch ($action) {
    case 'add':
        // Display add category form
        $form_title = 'Add New Category';
        $button_text = 'Add Category';
        $id = '';
        $name = '';
        $description = '';
        include 'views/category_form.php';
        break;
        
    case 'edit':
        // Check if ID parameter exists
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // Get category ID
            $category_id = $_GET['id'];
            
            // Get category details
            if ($category->getById($category_id)) {
                // Display edit category form
                $form_title = 'Edit Category';
                $button_text = 'Update Category';
                $id = $category->id;
                $name = $category->name;
                $description = $category->description;
                include 'views/category_form.php';
            } else {
                // Redirect with error
                header("Location: categories.php?error=invalid_id");
                exit();
            }
        } else {
            // Redirect with error
            header("Location: categories.php?error=invalid_id");
            exit();
        }
        break;
        
    case 'delete':
        // Check if ID parameter exists
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // Get category ID
            $category_id = $_GET['id'];
            
            // Set category properties
            $category->id = $category_id;
            
            // Check if category has expenses
            if ($category->hasExpenses()) {
                // Redirect with error
                header("Location: categories.php?error=category_has_expenses");
                exit();
            }
            
            // Delete category
            if ($category->delete()) {
                // Redirect with success message
                header("Location: categories.php?success=category_deleted");
                exit();
            } else {
                // Redirect with error
                header("Location: categories.php?error=delete_failed");
                exit();
            }
        } else {
            // Redirect with error
            header("Location: categories.php?error=invalid_id");
            exit();
        }
        break;
        
    case 'save':
        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Sanitize and validate inputs
            $category->name = isset($_POST['name']) ? $_POST['name'] : '';
            $category->description = isset($_POST['description']) ? $_POST['description'] : '';
            
            // Check if it's an update or create
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update existing category
                $category->id = $_POST['id'];
                
                // Update category
                if ($category->update()) {
                    // Redirect with success message
                    header("Location: categories.php?success=category_updated");
                    exit();
                } else {
                    // Redirect with error
                    header("Location: categories.php?error=update_failed");
                    exit();
                }
            } else {
                // Create new category
                if ($category->create()) {
                    // Redirect with success message
                    header("Location: categories.php?success=category_added");
                    exit();
                } else {
                    // Redirect with error
                    header("Location: categories.php?error=create_failed");
                    exit();
                }
            }
        }
        break;
        
    default:
        // Display categories list
        $categories = $category->getAll();
        
        // Include categories list view
        ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <h2><i class="fas fa-tags me-2"></i>Categories</h2>
                <p class="text-muted">Manage expense categories to organize your spending.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">All Categories</h5>
                        <a href="categories.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Category
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 0;
                                    
                                    while ($row = $categories->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td><span class='badge bg-primary'>" . htmlspecialchars($row['name']) . "</span></td>";
                                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                        echo "<td>" . Utils::formatDate($row['created_at']) . "</td>";
                                        echo "<td>
                                                <a href='categories.php?action=edit&id=" . $row['id'] . "' class='btn btn-action btn-sm btn-outline-primary me-1' data-bs-toggle='tooltip' title='Edit'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                                <a href='#' class='btn btn-action btn-sm btn-outline-danger delete-category' data-id='" . $row['id'] . "' data-bs-toggle='tooltip' title='Delete'>
                                                    <i class='fas fa-trash'></i>
                                                </a>
                                              </td>";
                                        echo "</tr>";
                                        
                                        $count++;
                                    }
                                    
                                    if ($count == 0) {
                                        echo "<tr><td colspan='4' class='text-center'>No categories found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;
}

// Include footer
require_once 'includes/footer.php';
?> 