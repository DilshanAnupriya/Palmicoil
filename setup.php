<?php
/**
 * Palm Oil Website Setup Script
 * This script helps with the initial setup of the website
 */

// Check if setup is already completed
if (file_exists('config/.setup_complete')) {
    die('Setup has already been completed. Delete config/.setup_complete to run setup again.');
}

$errors = [];
$success = [];

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    $errors[] = 'PHP 7.4 or higher is required. Current version: ' . PHP_VERSION;
} else {
    $success[] = 'PHP version check passed: ' . PHP_VERSION;
}

// Check required PHP extensions
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "Required PHP extension '$ext' is not loaded";
    } else {
        $success[] = "PHP extension '$ext' is available";
    }
}

// Check if uploads directory exists and is writable
if (!is_dir('uploads')) {
    if (mkdir('uploads', 0755, true)) {
        $success[] = 'Created uploads directory';
    } else {
        $errors[] = 'Failed to create uploads directory';
    }
} else {
    $success[] = 'Uploads directory exists';
}

if (is_dir('uploads') && !is_writable('uploads')) {
    $errors[] = 'Uploads directory is not writable';
} else if (is_dir('uploads')) {
    $success[] = 'Uploads directory is writable';
}

// Handle form submission
if ($_POST) {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $site_url = $_POST['site_url'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    
    // Validate inputs
    if (empty($db_name)) $errors[] = 'Database name is required';
    if (empty($db_user)) $errors[] = 'Database username is required';
    if (empty($site_url)) $errors[] = 'Site URL is required';
    if (empty($admin_email)) $errors[] = 'Admin email is required';
    
    if (empty($errors)) {
        try {
            // Test database connection
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Update database configuration
            $db_config = file_get_contents('config/database.php');
            $db_config = str_replace("'localhost'", "'$db_host'", $db_config);
            $db_config = str_replace("'palm_oil_db'", "'$db_name'", $db_config);
            $db_config = str_replace("'root'", "'$db_user'", $db_config);
            $db_config = str_replace("''", "'$db_pass'", $db_config);
            file_put_contents('config/database.php', $db_config);
            
            // Update site configuration
            $site_config = file_get_contents('config/config.php');
            $site_config = str_replace('http://localhost/palm-oil-website', $site_url, $site_config);
            file_put_contents('config/config.php', $site_config);
            
            // Check if tables exist, if not import schema
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) == 0) {
                // Import database schema
                $schema = file_get_contents('database/schema.sql');
                $pdo->exec($schema);
                $success[] = 'Database schema imported successfully';
            } else {
                $success[] = 'Database tables already exist';
            }
            
            // Update admin email in settings
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_email'");
            $stmt->execute([$admin_email]);
            
            // Mark setup as complete
            file_put_contents('config/.setup_complete', date('Y-m-d H:i:s'));
            
            $success[] = 'Setup completed successfully!';
            $success[] = 'You can now access the admin panel at: ' . $site_url . '/admin/login.php';
            $success[] = 'Default admin credentials: admin / admin123';
            
        } catch (Exception $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palm Oil Website - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .setup-container { max-width: 800px; margin: 50px auto; }
        .status-item { padding: 5px 0; }
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container setup-container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Palm Oil Website Setup</h2>
            </div>
            <div class="card-body">
                
                <!-- System Check -->
                <h4>System Requirements Check</h4>
                <?php foreach ($success as $msg): ?>
                    <div class="status-item status-success">✓ <?= htmlspecialchars($msg) ?></div>
                <?php endforeach; ?>
                
                <?php foreach ($errors as $error): ?>
                    <div class="status-item status-error">✗ <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
                
                <hr>
                
                <?php if (empty($errors) && !file_exists('config/.setup_complete')): ?>
                <!-- Setup Form -->
                <h4>Configuration</h4>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Database Configuration</h5>
                            <div class="mb-3">
                                <label class="form-label">Database Host</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Database Name</label>
                                <input type="text" class="form-control" name="db_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Database Username</label>
                                <input type="text" class="form-control" name="db_user" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Database Password</label>
                                <input type="password" class="form-control" name="db_pass">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Site Configuration</h5>
                            <div class="mb-3">
                                <label class="form-label">Site URL</label>
                                <input type="url" class="form-control" name="site_url" 
                                       value="http://<?= $_SERVER['HTTP_HOST'] ?><?= dirname($_SERVER['REQUEST_URI']) ?>" required>
                                <small class="form-text text-muted">Full URL to your website (without trailing slash)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Admin Email</label>
                                <input type="email" class="form-control" name="admin_email" required>
                                <small class="form-text text-muted">Email for admin notifications</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">Complete Setup</button>
                    </div>
                </form>
                
                <?php elseif (file_exists('config/.setup_complete')): ?>
                <div class="alert alert-success">
                    <h4>Setup Complete!</h4>
                    <p>Your Palm Oil website has been set up successfully.</p>
                    <div class="mt-3">
                        <a href="index.html" class="btn btn-primary">View Website</a>
                        <a href="admin/login.php" class="btn btn-secondary">Admin Panel</a>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="alert alert-danger">
                    <h4>Setup Cannot Continue</h4>
                    <p>Please fix the system requirement errors above before proceeding with the setup.</p>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</body>
</html>