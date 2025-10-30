<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Check if admin is logged in
requireLogin();

$db = getDB();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                $settings = [
                    'site_name' => sanitizeInput($_POST['site_name']),
                    'site_description' => sanitizeInput($_POST['site_description']),
                    'contact_email' => sanitizeInput($_POST['contact_email']),
                    'contact_phone' => sanitizeInput($_POST['contact_phone']),
                    'contact_address' => sanitizeInput($_POST['contact_address']),
                    'business_hours' => sanitizeInput($_POST['business_hours']),
                    'facebook_url' => sanitizeInput($_POST['facebook_url']),
                    'twitter_url' => sanitizeInput($_POST['twitter_url']),
                    'linkedin_url' => sanitizeInput($_POST['linkedin_url']),
                    'instagram_url' => sanitizeInput($_POST['instagram_url']),
                    'meta_keywords' => sanitizeInput($_POST['meta_keywords']),
                    'google_analytics' => $_POST['google_analytics'], // Allow HTML/JS
                    'footer_text' => sanitizeInput($_POST['footer_text'])
                ];

                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                    $stmt->execute([$value, $key]);
                }

                $success = "Settings updated successfully!";
                break;

            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                // Validate current password
                $stmt = $db->prepare("SELECT password FROM admin_users WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $admin = $stmt->fetch();

                if (!password_verify($current_password, $admin['password'])) {
                    $error = "Current password is incorrect.";
                } elseif (strlen($new_password) < 6) {
                    $error = "New password must be at least 6 characters long.";
                } elseif ($new_password !== $confirm_password) {
                    $error = "New passwords do not match.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                    $success = "Password changed successfully!";
                }
                break;
        }
    }
}

// Get current settings
$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">
                            <i class="fas fa-leaf text-success me-2"></i>Admin Panel
                        </h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="products.php">
                                <i class="fas fa-box me-2"></i>Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="pages.php">
                                <i class="fas fa-file-alt me-2"></i>Pages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="messages.php">
                                <i class="fas fa-envelope me-2"></i>Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active bg-success" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Settings</h1>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Settings Tabs -->
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-cog me-2"></i>General
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                            <i class="fas fa-address-book me-2"></i>Contact Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                            <i class="fas fa-share-alt me-2"></i>Social Media
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                            <i class="fas fa-search me-2"></i>SEO
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                            <i class="fas fa-shield-alt me-2"></i>Security
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="settingsTabContent">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">General Settings</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" 
                                               value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="site_description" class="form-label">Site Description</label>
                                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="footer_text" class="form-label">Footer Text</label>
                                        <input type="text" class="form-control" id="footer_text" name="footer_text" 
                                               value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Save General Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                               value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                               value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="contact_address" class="form-label">Contact Address</label>
                                        <textarea class="form-control" id="contact_address" name="contact_address" rows="3"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="business_hours" class="form-label">Business Hours</label>
                                        <textarea class="form-control" id="business_hours" name="business_hours" rows="3"><?php echo htmlspecialchars($settings['business_hours'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <!-- Hidden fields for other settings -->
                                    <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                                    <input type="hidden" name="site_description" value="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>">
                                    <input type="hidden" name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>">
                                    <input type="hidden" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                                    <input type="hidden" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                                    <input type="hidden" name="linkedin_url" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
                                    <input type="hidden" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                                    <input type="hidden" name="meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>">
                                    <input type="hidden" name="google_analytics" value="<?php echo htmlspecialchars($settings['google_analytics'] ?? ''); ?>">
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Save Contact Info
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="tab-pane fade" id="social" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Social Media Links</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    
                                    <div class="mb-3">
                                        <label for="facebook_url" class="form-label">
                                            <i class="fab fa-facebook text-primary me-2"></i>Facebook URL
                                        </label>
                                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                               value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="twitter_url" class="form-label">
                                            <i class="fab fa-twitter text-info me-2"></i>Twitter URL
                                        </label>
                                        <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                               value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="linkedin_url" class="form-label">
                                            <i class="fab fa-linkedin text-primary me-2"></i>LinkedIn URL
                                        </label>
                                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                               value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="instagram_url" class="form-label">
                                            <i class="fab fa-instagram text-danger me-2"></i>Instagram URL
                                        </label>
                                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                               value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                                    </div>
                                    
                                    <!-- Hidden fields for other settings -->
                                    <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                                    <input type="hidden" name="site_description" value="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>">
                                    <input type="hidden" name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>">
                                    <input type="hidden" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                                    <input type="hidden" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                                    <input type="hidden" name="contact_address" value="<?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?>">
                                    <input type="hidden" name="business_hours" value="<?php echo htmlspecialchars($settings['business_hours'] ?? ''); ?>">
                                    <input type="hidden" name="meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>">
                                    <input type="hidden" name="google_analytics" value="<?php echo htmlspecialchars($settings['google_analytics'] ?? ''); ?>">
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Save Social Media Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">SEO & Analytics</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    
                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                               value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>">
                                        <div class="form-text">Comma-separated keywords for SEO</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="google_analytics" class="form-label">Google Analytics Code</label>
                                        <textarea class="form-control" id="google_analytics" name="google_analytics" rows="5"><?php echo htmlspecialchars($settings['google_analytics'] ?? ''); ?></textarea>
                                        <div class="form-text">Paste your Google Analytics tracking code here</div>
                                    </div>
                                    
                                    <!-- Hidden fields for other settings -->
                                    <input type="hidden" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                                    <input type="hidden" name="site_description" value="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>">
                                    <input type="hidden" name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>">
                                    <input type="hidden" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                                    <input type="hidden" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                                    <input type="hidden" name="contact_address" value="<?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?>">
                                    <input type="hidden" name="business_hours" value="<?php echo htmlspecialchars($settings['business_hours'] ?? ''); ?>">
                                    <input type="hidden" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                                    <input type="hidden" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                                    <input type="hidden" name="linkedin_url" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
                                    <input type="hidden" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Save SEO Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="form-text">Password must be at least 6 characters long</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>