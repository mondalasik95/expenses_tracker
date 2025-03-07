<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-list me-2"></i>Expenses</h2>
        <p class="text-muted">Manage your expenses and track your spending.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Filters</h5>
                <a href="expenses.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Expense
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category-filter" class="form-label">Filter by Category</label>
                        <select class="form-select" id="category-filter">
                            <option value="">All Categories</option>
                            <?php
                            while ($cat = $categories->fetch(PDO::FETCH_ASSOC)) {
                                $selected = (isset($filter_category) && $filter_category == $cat['id']) ? 'selected' : '';
                                echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <form id="date-range-form">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $filter_start_date ?? ''; ?>">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $filter_end_date ?? ''; ?>">
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <?php
                    if (isset($filter_category)) {
                        echo 'Expenses by Category';
                    } elseif (isset($filter_start_date) && isset($filter_end_date)) {
                        echo 'Expenses from ' . Utils::formatDate($filter_start_date) . ' to ' . Utils::formatDate($filter_end_date);
                    } else {
                        echo 'All Expenses';
                    }
                    ?>
                </h5>
                <div>
                    <!-- Export Buttons -->
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="export?type=csv<?php echo isset($filter_category) ? '&category_id=' . $filter_category : ''; ?><?php echo (isset($filter_start_date) && isset($filter_end_date)) ? '&start_date=' . $filter_start_date . '&end_date=' . $filter_end_date : ''; ?>">
                                    <i class="fas fa-file-csv me-1"></i> Export as CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="export?type=pdf<?php echo isset($filter_category) ? '&category_id=' . $filter_category : ''; ?><?php echo (isset($filter_start_date) && isset($filter_end_date)) ? '&start_date=' . $filter_start_date . '&end_date=' . $filter_end_date : ''; ?>">
                                    <i class="fas fa-file-pdf me-1"></i> Export as PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="expenses?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Expense
                    </a>
                </div>
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
                                <th>Actions</th>
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
                                echo "<td>
                                        <a href='expenses.php?action=edit&id=" . $row['id'] . "' class='btn btn-action btn-sm btn-outline-primary me-1' data-bs-toggle='tooltip' title='Edit'>
                                            <i class='fas fa-edit'></i>
                                        </a>
                                        <a href='#' class='btn btn-action btn-sm btn-outline-danger delete-expense' data-id='" . $row['id'] . "' data-bs-toggle='tooltip' title='Delete'>
                                            <i class='fas fa-trash'></i>
                                        </a>
                                      </td>";
                                echo "</tr>";
                                
                                $total_amount += $row['amount'];
                                $count++;
                            }
                            
                            if ($count == 0) {
                                echo "<tr><td colspan='5' class='text-center'>No expenses found</td></tr>";
                            }
                            ?>
                        </tbody>
                        <?php if ($count > 0): ?>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="expense-amount"><?php echo Utils::formatCurrency($total_amount); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 