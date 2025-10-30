<?php
require_once '../config/config.php';
requireLogin();

$db = getDB();
$isEdit = isset($_GET['id']);
$faq = null;
$error = '';
$success = '';

if ($isEdit) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    $faq = $stmt->fetch();
    
    if (!$faq) {
        redirect(ADMIN_URL . '/faqs.php');
    }
}

if ($_POST) {
    $question = sanitizeInput($_POST['question']);
    $answer = $_POST['answer']; // Allow HTML in answer
    $display_order = (int)$_POST['display_order'];
    $status = $_POST['status'];
    
    if (!empty($question) && !empty($answer)) {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("
                    UPDATE faqs 
                    SET question = ?, answer = ?, display_order = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$question, $answer, $display_order, $status, $id]);
                $success = 'FAQ updated successfully!';
            } else {
                $stmt = $db->prepare("
                    INSERT INTO faqs (question, answer, display_order, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$question, $answer, $display_order, $status]);
                $success = 'FAQ added successfully!';
            }
            
            // Refresh data if editing
            if ($isEdit) {
                $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
                $stmt->execute([$id]);
                $faq = $stmt->fetch();
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Get next display order for new entries
$nextOrder = 1;
if (!$isEdit) {
    $stmt = $db->query("SELECT MAX(display_order) as max_order FROM faqs");
    $result = $stmt->fetch();
    $nextOrder = ($result['max_order'] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> FAQ - Palm Oil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: #2E7D32;
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h5>🌴 Palm Oil Admin</h5>
                        <small>Welcome, <?php echo $_SESSION['admin_username']; ?></small>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box me-2"></i> Products
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags me-2"></i> Categories
                        </a>
                        <a class="nav-link" href="blogs.php">
                            <i class="fas fa-blog me-2"></i> Blogs
                        </a>
                        <a class="nav-link" href="slideshow.php">
                            <i class="fas fa-images me-2"></i> Slideshow
                        </a>
                        <a class="nav-link" href="strengths.php">
                            <i class="fas fa-star me-2"></i> Our Strengths
                        </a>
                        <a class="nav-link" href="certifications.php">
                            <i class="fas fa-certificate me-2"></i> Certifications
                        </a>
                        <a class="nav-link active" href="faqs.php">
                            <i class="fas fa-question-circle me-2"></i> FAQs
                        </a>
                        <a class="nav-link" href="pages.php">
                            <i class="fas fa-file-alt me-2"></i> Pages
                        </a>
                        <a class="nav-link" href="messages.php">
                            <i class="fas fa-envelope me-2"></i> Messages
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i> View Site
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><?php echo $isEdit ? 'Edit' : 'Add'; ?> FAQ</h2>
                        <a href="faqs.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to FAQs
                        </a>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="question" class="form-label">Question *</label>
                                            <input type="text" class="form-control" id="question" name="question" 
                                                   value="<?php echo htmlspecialchars($faq['question'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="answer" class="form-label">Answer *</label>
                                            <textarea class="form-control" id="answer" name="answer" rows="6" required><?php echo htmlspecialchars($faq['answer'] ?? ''); ?></textarea>
                                            <small class="form-text text-muted">You can use basic HTML tags for formatting.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="display_order" class="form-label">Display Order</label>
                                            <input type="number" class="form-control" id="display_order" name="display_order" 
                                                   value="<?php echo $faq['display_order'] ?? $nextOrder; ?>" min="1">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($faq['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($faq['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i> <?php echo $isEdit ? 'Update' : 'Add'; ?> FAQ
                                    </button>
                                    <a href="faqs.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>