<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $link_url = sanitizeInput($_POST['link_url']);
                $sort_order = (int)$_POST['sort_order'];
                $status = sanitizeInput($_POST['status']);
                
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/slideshow/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/slideshow/' . $filename;
                        
                        $stmt = $pdo->prepare("INSERT INTO slideshow_images (title, description, image_path, link_url, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $description, $image_path, $link_url, $sort_order, $status]);
                        
                        $success_message = "Slideshow image added successfully!";
                    } else {
                        $error_message = "Failed to upload image.";
                    }
                } else {
                    $error_message = "Please select an image file.";
                }
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $link_url = sanitizeInput($_POST['link_url']);
                $sort_order = (int)$_POST['sort_order'];
                $status = sanitizeInput($_POST['status']);
                
                // Handle file upload if new image is provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/slideshow/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/slideshow/' . $filename;
                        
                        $stmt = $pdo->prepare("UPDATE slideshow_images SET title = ?, description = ?, image_path = ?, link_url = ?, sort_order = ?, status = ? WHERE id = ?");
                        $stmt->execute([$title, $description, $image_path, $link_url, $sort_order, $status, $id]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE slideshow_images SET title = ?, description = ?, link_url = ?, sort_order = ?, status = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $link_url, $sort_order, $status, $id]);
                }
                
                $success_message = "Slideshow image updated successfully!";
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Get image path to delete file
                $stmt = $pdo->prepare("SELECT image_path FROM slideshow_images WHERE id = ?");
                $stmt->execute([$id]);
                $image = $stmt->fetch();
                
                if ($image && file_exists('../' . $image['image_path'])) {
                    unlink('../' . $image['image_path']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM slideshow_images WHERE id = ?");
                $stmt->execute([$id]);
                
                $success_message = "Slideshow image deleted successfully!";
                break;
        }
    }
}

// Fetch all slideshow images
$stmt = $pdo->query("SELECT * FROM slideshow_images ORDER BY sort_order ASC, created_at DESC");
$slideshow_images = $stmt->fetchAll();

// Get image for editing
$edit_image = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM slideshow_images WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_image = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slideshow Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Admin Panel</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="blogs.php">
                                <i class="fas fa-blog"></i> Blogs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="slideshow.php">
                                <i class="fas fa-images"></i> Slideshow
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="faqs.php">
                                <i class="fas fa-question-circle"></i> FAQs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="certifications.php">
                                <i class="fas fa-certificate"></i> Certifications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="strengths.php">
                                <i class="fas fa-star"></i> Strengths
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages.php">
                                <i class="fas fa-file-alt"></i> Pages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="messages.php">
                                <i class="fas fa-envelope"></i> Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt"></i> View Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Slideshow Management</h1>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo $edit_image ? 'Edit' : 'Add New'; ?> Slideshow Image</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $edit_image ? 'update' : 'add'; ?>">
                            <?php if ($edit_image): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_image['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo $edit_image ? htmlspecialchars($edit_image['title']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">Sort Order</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                               value="<?php echo $edit_image ? $edit_image['sort_order'] : '0'; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_image ? htmlspecialchars($edit_image['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image <?php echo $edit_image ? '(Leave empty to keep current)' : ''; ?></label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo !$edit_image ? 'required' : ''; ?>>
                                        <?php if ($edit_image && $edit_image['image_path']): ?>
                                            <div class="mt-2">
                                                <img src="../<?php echo htmlspecialchars($edit_image['image_path']); ?>" alt="Current image" style="max-width: 200px; height: auto;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="link_url" class="form-label">Link URL (Optional)</label>
                                        <input type="url" class="form-control" id="link_url" name="link_url" 
                                               value="<?php echo $edit_image ? htmlspecialchars($edit_image['link_url']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active" <?php echo ($edit_image && $edit_image['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($edit_image && $edit_image['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_image ? 'Update' : 'Add'; ?> Slideshow Image
                            </button>
                            <?php if ($edit_image): ?>
                                <a href="slideshow.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Slideshow Images List -->
                <div class="card">
                    <div class="card-header">
                        <h5>Slideshow Images</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($slideshow_images)): ?>
                            <p class="text-muted">No slideshow images found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Sort Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($slideshow_images as $image): ?>
                                            <tr>
                                                <td>
                                                    <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                         alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                                         style="width: 80px; height: 50px; object-fit: cover;">
                                                </td>
                                                <td><?php echo htmlspecialchars($image['title']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($image['description'], 0, 50)) . (strlen($image['description']) > 50 ? '...' : ''); ?></td>
                                                <td><?php echo $image['sort_order']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $image['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($image['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="slideshow.php?edit=<?php echo $image['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>