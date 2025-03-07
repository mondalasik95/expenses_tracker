<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="categories">Categories</a></li>
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
                <form action="categories?action=save" method="post">
                    <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="categories" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary"><?php echo $button_text; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 