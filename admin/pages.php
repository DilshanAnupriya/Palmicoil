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
            case 'add':
            case 'edit':
                $title = sanitizeInput($_POST['title']);
                $slug = generateSlug($_POST['slug'] ?: $title);
                $content = $_POST['content']; // Allow HTML content
                $meta_title = sanitizeInput($_POST['meta_title']);
                $meta_description = sanitizeInput($_POST['meta_description']);
                $status = sanitizeInput($_POST['status']);
                
                if ($_POST['action'] == 'add') {
                    $stmt = $db->prepare("INSERT INTO pages (title, slug, content, meta_title, meta_description, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$title, $slug, $content, $meta_title, $meta_description, $status]);
                    $success = "Page added successfully!";
                } else {
                    $id = (int)$_POST['id'];
                    $stmt = $db->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, meta_title = ?, meta_description = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $slug, $content, $meta_title, $meta_description, $status, $id]);
                    $success = "Page updated successfully!";
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM pages WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Page deleted successfully!";
                break;
        }
    }
}

// Get page for editing
$editPage = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $editPage = $stmt->fetch();
}

// Get all pages
$stmt = $db->query("SELECT * FROM pages ORDER BY created_at DESC");
$pages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages - Admin Panel</title>
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
                            <a class="nav-link text-white active bg-success" href="pages.php">
                                <i class="fas fa-file-alt me-2"></i>Pages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="messages.php">
                                <i class="fas fa-envelope me-2"></i>Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="settings.php">
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
                    <h1 class="h2">Manage Pages</h1>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Page Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $editPage ? 'edit' : 'plus'; ?> me-2"></i>
                            <?php echo $editPage ? 'Edit Page' : 'Add New Page'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?php echo $editPage ? 'edit' : 'add'; ?>">
                            <?php if ($editPage): ?>
                                <input type="hidden" name="id" value="<?php echo $editPage['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Page Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo $editPage ? htmlspecialchars($editPage['title']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">URL Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo $editPage ? htmlspecialchars($editPage['slug']) : ''; ?>"
                                               placeholder="Leave empty to auto-generate from title">
                                        <div class="form-text">URL-friendly version of the title</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Page Content *</label>
                                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo $editPage ? htmlspecialchars($editPage['content']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="published" <?php echo ($editPage && $editPage['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                            <option value="draft" <?php echo ($editPage && $editPage['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                               value="<?php echo $editPage ? htmlspecialchars($editPage['meta_title']) : ''; ?>"
                                               maxlength="60">
                                        <div class="form-text">SEO title (max 60 characters)</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" 
                                                  rows="3" maxlength="160"><?php echo $editPage ? htmlspecialchars($editPage['meta_description']) : ''; ?></textarea>
                                        <div class="form-text">SEO description (max 160 characters)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $editPage ? 'Update Page' : 'Add Page'; ?>
                                </button>
                                <?php if ($editPage): ?>
                                    <a href="pages.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Pages List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>All Pages
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pages)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No pages found. Create your first page above.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Slug</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pages as $page): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($page['title']); ?></strong>
                                                </td>
                                                <td>
                                                    <code><?php echo htmlspecialchars($page['slug']); ?></code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $page['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($page['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($page['created_at'])); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($page['updated_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?edit=<?php echo $page['id']; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deletePage(<?php echo $page['id']; ?>, '<?php echo htmlspecialchars($page['title']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
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

    <!-- Delete Form (hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            document.getElementById('slug').value = slug;
        });

        function deletePage(id, title) {
            if (confirm(`Are you sure you want to delete the page "${title}"?`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>