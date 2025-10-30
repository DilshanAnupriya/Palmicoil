<?php
require_once '../config/config.php';
requireLogin();

$db = getDB();
$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'create') {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $slug = generateSlug($_POST['slug'] ?: $name);
        $status = $_POST['status'];
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadImage($_FILES['image'], 'categories');
        }
        
        if (!empty($name)) {
            try {
                $stmt = $db->prepare("INSERT INTO categories (name, description, slug, image, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $slug, $image, $status]);
                $success = 'Category created successfully!';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = 'A category with this slug already exists.';
                } else {
                    $error = 'An error occurred while creating the category.';
                }
            }
        } else {
            $error = 'Category name is required.';
        }
    } elseif ($action == 'update') {
        $id = (int)$_POST['id'];
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $slug = generateSlug($_POST['slug'] ?: $name);
        $status = $_POST['status'];
        
        // Get current image
        $stmt = $db->prepare("SELECT image FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $currentCategory = $stmt->fetch();
        $image = $currentCategory['image'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadedImage = uploadImage($_FILES['image'], 'categories');
            if ($uploadedImage) {
                $image = $uploadedImage;
            }
        }
        
        if (!empty($name)) {
            try {
                $stmt = $db->prepare("UPDATE categories SET name = ?, description = ?, slug = ?, image = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $description, $slug, $image, $status, $id]);
                $success = 'Category updated successfully!';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = 'A category with this slug already exists.';
                } else {
                    $error = 'An error occurred while updating the category.';
                }
            }
        } else {
            $error = 'Category name is required.';
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if category has products
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetch()['count'];
    
    if ($productCount > 0) {
        $error = 'Cannot delete category. It has ' . $productCount . ' products associated with it.';
    } else {
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Category deleted successfully!';
    }
}

// Get all categories
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get category for editing
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Palm Oil Admin</title>
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
        .category-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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
                        <a class="nav-link active" href="categories.php">
                            <i class="fas fa-tags me-2"></i> Categories
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
                    <h2>Categories</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- Category Form -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><?php echo $editCategory ? 'Edit' : 'Add'; ?> Category</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'create'; ?>">
                                        <?php if ($editCategory): ?>
                                            <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                                        <?php endif; ?>
                                        
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="slug" class="form-label">Slug</label>
                                            <input type="text" class="form-control" id="slug" name="slug" 
                                                   value="<?php echo htmlspecialchars($editCategory['slug'] ?? ''); ?>">
                                            <div class="form-text">Leave empty to auto-generate</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="image" class="form-label">Image</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                            <?php if (!empty($editCategory['image'])): ?>
                                                <div class="mt-2">
                                                    <img src="<?php echo UPLOAD_URL . $editCategory['image']; ?>" 
                                                         alt="Current image" class="category-image">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($editCategory['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($editCategory['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success">
                                                <?php echo $editCategory ? 'Update' : 'Create'; ?>
                                            </button>
                                            <?php if ($editCategory): ?>
                                                <a href="categories.php" class="btn btn-secondary">Cancel</a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories List -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>All Categories</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($categories)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No categories found</h6>
                                            <p class="text-muted">Create your first category using the form.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Image</th>
                                                        <th>Name</th>
                                                        <th>Description</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($categories as $category): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if ($category['image']): ?>
                                                                    <img src="<?php echo UPLOAD_URL . $category['image']; ?>" 
                                                                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                                                         class="category-image">
                                                                <?php else: ?>
                                                                    <div class="category-image bg-light d-flex align-items-center justify-content-center">
                                                                        <i class="fas fa-image text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($category['slug']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $category['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                                    <?php echo ucfirst($category['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="categories.php?edit=<?php echo $category['id']; ?>" 
                                                                       class="btn btn-outline-primary">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <a href="categories.php?delete=<?php echo $category['id']; ?>" 
                                                                       class="btn btn-outline-danger"
                                                                       onclick="return confirm('Are you sure you want to delete this category?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </a>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        });
    </script>
</body>
</html>