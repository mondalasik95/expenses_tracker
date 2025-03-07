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

// Get filter parameters
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$filter_month = isset($_GET['month']) ? $_GET['month'] : null;
$filter_category = isset($_GET['category_id']) ? $_GET['category_id'] : null;

// Set date range based on filters
if ($filter_month) {
    $start_date = $filter_year . '-' . $filter_month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
} else {
    $start_date = $filter_year . '-01-01';
    $end_date = $filter_year . '-12-31';
}

// Get total expenses for the period
$total_expenses = $expense->getTotalExpenses($user_id, $start_date, $end_date);

// Get category totals for the period
$category_totals = $expense->getCategoryTotals($user_id, $start_date, $end_date);

// Get monthly totals for the year
$monthly_totals = $expense->getMonthlyTotals($user_id, $filter_year);

// Get all categories for filter dropdown
$categories = $category->getAll();

// Get expenses based on filters
if ($filter_category) {
    $expenses = $expense->getByCategory($user_id, $filter_category);
} else {
    $expenses = $expense->getByDateRange($user_id, $start_date, $end_date);
}

// Prepare data for monthly chart
$months = Utils::getMonths();
$monthly_data = array_fill(0, 12, 0); // Initialize with zeros

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

if ($category_totals) {
    while ($row = $category_totals->fetch(PDO::FETCH_ASSOC)) {
        $category_labels[] = $row['category_name'];
        $category_data[] = (float)$row['total'];
    }
}

$category_chart_data = [
    'labels' => $category_labels,
    'data' => $category_data
];

// Get available years for filter
$current_year = date('Y');
$years = range($current_year - 5, $current_year);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-chart-bar me-2"></i>Expense Reports</h2>
        <p class="text-muted">Analyze your spending patterns and track your expenses over time.</p>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="reports.php" method="get">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year">
                                <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>" <?php echo ($year == $filter_year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="month" class="form-label">Month</label>
                            <select class="form-select" id="month" name="month">
                                <option value="">All Months</option>
                                <?php foreach ($months as $key => $month): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($key == $filter_month) ? 'selected' : ''; ?>>
                                    <?php echo $month; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                <?php
                                // Reset the pointer to the beginning
                                $categories->execute();
                                while ($cat = $categories->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($cat['id'] == $filter_category) ? 'selected' : '';
                                    echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow dashboard-card h-100">
            <div class="card-body text-center">
                <div class="card-icon text-primary">
                    <i class="fas fa-wallet"></i>
                </div>
                <h5 class="card-title">Total Expenses</h5>
                <h2 class="mb-0"><?php echo Utils::formatCurrency($total_expenses); ?></h2>
                <p class="text-muted small">
                    <?php
                    if ($filter_month) {
                        echo $months[$filter_month] . ' ' . $filter_year;
                    } else {
                        echo 'Year ' . $filter_year;
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow dashboard-card h-100">
            <div class="card-body text-center">
                <div class="card-icon text-success">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h5 class="card-title">Categories</h5>
                <h2 class="mb-0"><?php echo count($category_labels); ?></h2>
                <p class="text-muted small">With expenses</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow dashboard-card h-100">
            <div class="card-body text-center">
                <div class="card-icon text-danger">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h5 class="card-title">Average Monthly</h5>
                <h2 class="mb-0">
                    <?php
                    $avg_monthly = 0;
                    $non_zero_months = 0;
                    
                    foreach ($monthly_data as $amount) {
                        if ($amount > 0) {
                            $avg_monthly += $amount;
                            $non_zero_months++;
                        }
                    }
                    
                    if ($non_zero_months > 0) {
                        $avg_monthly = $avg_monthly / $non_zero_months;
                    }
                    
                    echo Utils::formatCurrency($avg_monthly);
                    ?>
                </h2>
                <p class="text-muted small">For <?php echo $filter_year; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Monthly Expenses (<?php echo $filter_year; ?>)</h5>
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

<!-- Expense Details -->
<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Expense Details</h5>
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
                            $total_amount = 0;
                            $count = 0;
                            
                            while ($row = $expenses->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . Utils::formatDate($row['expense_date']) . "</td>";
                                echo "<td><span class='badge bg-primary'>" . htmlspecialchars($row['category_name']) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td class='expense-amount'>" . Utils::formatCurrency($row['amount']) . "</td>";
                                echo "</tr>";
                                
                                $total_amount += $row['amount'];
                                $count++;
                            }
                            
                            if ($count == 0) {
                                echo "<tr><td colspan='4' class='text-center'>No expenses found</td></tr>";
                            }
                            ?>
                        </tbody>
                        <?php if ($count > 0): ?>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="expense-amount"><?php echo Utils::formatCurrency($total_amount); ?></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?> 