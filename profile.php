<?php
// Include header
require_once 'includes/header.php';

// Include database and models
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'includes/utils.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get user ID from session
$user_id = $_SESSION["user_id"];

// Get user details
$user->getUserById($user_id);

// Set default action
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($action == 'update_profile') {
        // Update profile
        $user->id = $user_id;
        $user->name = isset($_POST['name']) ? $_POST['name'] : '';
        $user->email = isset($_POST['email']) ? $_POST['email'] : '';
        
        // Update user
        if ($user->update()) {
            // Update session variables
            $_SESSION["user_name"] = $user->name;
            $_SESSION["user_email"] = $user->email;
            
            // Redirect with success message
            header("Location: profile?success=profile_updated");
            exit();
        } else {
            // Redirect with error
            header("Location: profile?error=update_failed");
            exit();
        }
    } elseif ($action == 'change_password') {
        // Change password
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            header("Location: profile?action=change_password&error=empty_fields");
            exit();
        }
        
        if ($new_password != $confirm_password) {
            header("Location: profile?action=change_password&error=password_mismatch");
            exit();
        }
        
        if (strlen($new_password) < 6) {
            header("Location: profile?action=change_password&error=password_length");
            exit();
        }
        
        // Change password
        $user->id = $user_id;
        if ($user->changePassword($current_password, $new_password)) {
            // Redirect with success message
            header("Location: profile?success=password_changed");
            exit();
        } else {
            // Redirect with error
            header("Location: profile?action=change_password&error=current_password");
            exit();
        }
    }
}

// Display appropriate view based on action
switch ($action) {
    case 'edit_profile':
        // Display edit profile form
        ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="profile">Profile</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
                    </ol>
                </nav>
                <h2><i class="fas fa-user-edit me-2"></i>Edit Profile</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <form action="profile?action=update_profile" method="post">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user->name); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="profile" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;
        
    case 'change_password':
        // Display change password form
        $error = isset($_GET['error']) ? $_GET['error'] : '';
        $error_message = '';
        
        switch ($error) {
            case 'empty_fields':
                $error_message = 'Please fill in all fields.';
                break;
            case 'password_mismatch':
                $error_message = 'New password and confirm password do not match.';
                break;
            case 'password_length':
                $error_message = 'Password must be at least 6 characters long.';
                break;
            case 'current_password':
                $error_message = 'Current password is incorrect.';
                break;
        }
        ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="profile">Profile</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                    </ol>
                </nav>
                <h2><i class="fas fa-key me-2"></i>Change Password</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <form action="profile?action=change_password" method="post">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Password must be at least 6 characters long.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="profile" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;
        
    default:
        // Display profile view
        ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <h2><i class="fas fa-user-circle me-2"></i>My Profile</h2>
                <p class="text-muted">View and manage your account information.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <div class="profile-header text-center">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($user->name); ?></h3>
                            <p class="text-muted"><?php echo htmlspecialchars($user->email); ?></p>
                        </div>
                        
                        <hr>
                        
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <h5>Account Information</h5>
                                <p><strong>Member Since:</strong> <?php echo Utils::formatDate($user->created_at); ?></p>
                                <p><strong>Last Updated:</strong> <?php echo Utils::formatDate($user->updated_at); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h5>Account Management</h5>
                                <div class="d-grid gap-2">
                                    <a href="profile?action=edit_profile" class="btn btn-primary mb-2">
                                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                                    </a>
                                    <a href="profile?action=change_password" class="btn btn-secondary">
                                        <i class="fas fa-key me-1"></i> Change Password
                                    </a>
                                </div>
                            </div>
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