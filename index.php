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

// Get current month and year
$current_month = date('m');
$current_year = date('Y');
$start_date = date('Y-m-01'); // First day of current month
$end_date = date('Y-m-t'); // Last day of current month

// Get total expenses
$total_expenses = $expense->getTotalExpenses($user_id);

// Get current month expenses
$current_month_expenses = $expense->getTotalExpenses($user_id, $start_date, $end_date);

// Get category totals
$category_totals = $expense->getCategoryTotals($user_id);

// Get monthly totals for the current year
$monthly_totals = $expense->getMonthlyTotals($user_id, $current_year);

// Prepare data for monthly chart
$months = Utils::getMonths();
$monthly_data = array_fill(0, 12, 0); // Initialize with zeros

// Check if monthly_totals is not null before using fetch
if ($monthly_totals) {
    while ($row = $monthly_totals->fetch(PDO::FETCH_ASSOC)) {
        $month_index = (int)$row['month'] - 1; // Adjust for 0-based array
        $monthly_data[$month_index] = (float)$row['total'];
    }
}

$monthly_chart_data = [
    'labels' => array_values($months),
    'data' => $monthly_data
];

// Prepare data for category chart
$category_labels = [];
$category_data = [];

while ($row = $category_totals->fetch(PDO::FETCH_ASSOC)) {
    $category_labels[] = $row['category_name'];
    $category_data[] = (float)$row['total'];
}

$category_chart_data = [
    'labels' => $category_labels,
    'data' => $category_data
];

// Get recent expenses
$recent_expenses = $expense->getAllByUser($user_id, 'expense_date', 'DESC');
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>! Here's an overview of your expenses.</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow dashboard-card h-100">
            <div class="card-body text-center">
                <div class="card-icon text-primary">
                    <i class="fas fa-wallet"></i>
                </div>
                <h5 class="card-title">Total Expenses</h5>
                <h2 class="mb-0"><?php echo Utils::formatCurrency($total_expenses); ?></h2>
                <p class="text-muted small">All time</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow dashboard-card h-100">
            <div class="card-body text-center">
                <div class="card-icon text-success">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h5 class="card-title">This Month</h5>
                <h2 class="mb-0"><?php echo Utils::formatCurrency($current_month_expenses); ?></h2>
                <p class="text-muted small"><?php echo Utils::getCurrentMonth(); ?> <?php echo Utils::getCurrentYear(); ?></p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow dashboard-card h-100">
            <div class="card-body text-center">
                <div class="card-icon text-danger">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h5 class="card-title">Categories</h5>
                <h2 class="mb-0"><?php echo count($category_labels); ?></h2>
                <p class="text-muted small">With expenses</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Monthly Expenses (<?php echo date('Y'); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyExpensesChart" data-chart='<?php echo json_encode($monthly_chart_data); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Expenses by Category</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryExpensesChart" data-chart='<?php echo json_encode($category_chart_data); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Expenses -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Expenses</h5>
                <a href="expenses.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 0;
                            while ($row = $recent_expenses->fetch(PDO::FETCH_ASSOC)) {
                                if ($count >= 5) break; // Show only 5 recent expenses
                                
                                echo "<tr>";
                                echo "<td>" . Utils::formatDate($row['expense_date']) . "</td>";
                                echo "<td><span class='badge bg-primary'>" . htmlspecialchars($row['category_name']) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td class='expense-amount'>" . Utils::formatCurrency($row['amount']) . "</td>";
                                echo "</tr>";
                                
                                $count++;
                            }
                            
                            if ($count == 0) {
                                echo "<tr><td colspan='4' class='text-center'>No expenses found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Expense Button -->
<div class="position-fixed bottom-0 end-0 p-3">
    <a href="expenses.php?action=add" class="btn btn-primary btn-lg rounded-circle shadow">
        <i class="fas fa-plus"></i>
    </a>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?> 