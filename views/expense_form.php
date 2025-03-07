<?php
// Get all categories for dropdown
$categories = $category->getAll();

// Set form title and button text based on action
$form_title = ($action == 'edit') ? 'Edit Expense' : 'Add New Expense';
$button_text = ($action == 'edit') ? 'Update Expense' : 'Add Expense';

// Set form values for edit mode
$id = isset($expense->id) ? $expense->id : '';
$category_id = isset($expense->category_id) ? $expense->category_id : '';
$amount = isset($expense->amount) ? $expense->amount : '';
$description = isset($expense->description) ? $expense->description : '';
$expense_date = isset($expense->expense_date) ? $expense->expense_date : date('Y-m-d');
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="expenses">Expenses</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $form_title; ?></li>
            </ol>
        </nav>
        <h2><i class="fas fa-<?php echo ($action == 'edit') ? 'edit' : 'plus-circle'; ?> me-2"></i><?php echo $form_title; ?></h2>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="card border-0 shadow">
            <div class="card-body p-4">
                <form action="expenses?action=save" method="post">
                    <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            while ($cat = $categories->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($cat['id'] == $category_id) ? 'selected' : '';
                                echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" value="<?php echo $amount; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $description; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo $expense_date; ?>" required>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="expenses" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary"><?php echo $button_text; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 