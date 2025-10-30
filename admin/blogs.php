<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = sanitizeInput($_POST['title']);
                $slug = sanitizeInput($_POST['slug']);
                $excerpt = sanitizeInput($_POST['excerpt']);
                $content = $_POST['content']; // Allow HTML content
                $author = sanitizeInput($_POST['author']);
                $status = sanitizeInput($_POST['status']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                $meta_title = sanitizeInput($_POST['meta_title']);
                $meta_description = sanitizeInput($_POST['meta_description']);
                $tags = sanitizeInput($_POST['tags']);
                
                // Handle file upload
                $image_path = '';
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/blogs/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/blogs/' . $filename;
                    } else {
                        $error_message = "Failed to upload image.";
                    }
                }
                
                if (!isset($error_message)) {
                    $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
                    
                    $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, excerpt, content, featured_image, author, status, featured, published_at, meta_title, meta_description, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $slug, $excerpt, $content, $image_path, $author, $status, $featured, $published_at, $meta_title, $meta_description, $tags]);
                    
                    $success_message = "Blog post added successfully!";
                }
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $title = sanitizeInput($_POST['title']);
                $slug = sanitizeInput($_POST['slug']);
                $excerpt = sanitizeInput($_POST['excerpt']);
                $content = $_POST['content']; // Allow HTML content
                $author = sanitizeInput($_POST['author']);
                $status = sanitizeInput($_POST['status']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                $meta_title = sanitizeInput($_POST['meta_title']);
                $meta_description = sanitizeInput($_POST['meta_description']);
                $tags = sanitizeInput($_POST['tags']);
                
                // Get current blog data
                $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
                $stmt->execute([$id]);
                $current_blog = $stmt->fetch();
                
                $image_path = $current_blog['featured_image'];
                
                // Handle file upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/blogs/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                        // Delete old image if exists
                        if ($current_blog['featured_image'] && file_exists('../' . $current_blog['featured_image'])) {
                            unlink('../' . $current_blog['featured_image']);
                        }
                        $image_path = 'uploads/blogs/' . $filename;
                    } else {
                        $error_message = "Failed to upload image.";
                    }
                }
                
                if (!isset($error_message)) {
                    $published_at = $current_blog['published_at'];
                    if ($status === 'published' && !$published_at) {
                        $published_at = date('Y-m-d H:i:s');
                    } elseif ($status !== 'published') {
                        $published_at = null;
                    }
                    
                    $stmt = $pdo->prepare("UPDATE blogs SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, author = ?, status = ?, featured = ?, published_at = ?, meta_title = ?, meta_description = ?, tags = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $slug, $excerpt, $content, $image_path, $author, $status, $featured, $published_at, $meta_title, $meta_description, $tags, $id]);
                    
                    $success_message = "Blog post updated successfully!";
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Get blog data to delete image
                $stmt = $pdo->prepare("SELECT featured_image FROM blogs WHERE id = ?");
                $stmt->execute([$id]);
                $blog = $stmt->fetch();
                
                if ($blog && $blog['featured_image'] && file_exists('../' . $blog['featured_image'])) {
                    unlink('../' . $blog['featured_image']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
                $stmt->execute([$id]);
                
                $success_message = "Blog post deleted successfully!";
                break;
        }
    }
}

// Fetch all blogs
$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll();

// Get blog for editing
$edit_blog = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_blog = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
                            <a class="nav-link" href="slideshow.php">
                                <i class="fas fa-images"></i> Slideshow
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="blogs.php">
                                <i class="fas fa-blog"></i> Blogs
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
                    <h1 class="h2">Blog Management</h1>
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
                        <h5><?php echo $edit_blog ? 'Edit' : 'Add New'; ?> Blog Post</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $edit_blog ? 'update' : 'add'; ?>">
                            <?php if ($edit_blog): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_blog['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['title']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['slug']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Excerpt</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required><?php echo $edit_blog ? htmlspecialchars($edit_blog['excerpt']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?php echo $edit_blog ? htmlspecialchars($edit_blog['content']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image <?php echo $edit_blog ? '(Leave empty to keep current)' : ''; ?></label>
                                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*" <?php echo !$edit_blog ? 'required' : ''; ?>>
                                        <?php if ($edit_blog && $edit_blog['featured_image']): ?>
                                            <div class="mt-2">
                                                <img src="../<?php echo htmlspecialchars($edit_blog['featured_image']); ?>" alt="Current image" style="max-width: 200px; height: auto;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="author" class="form-label">Author</label>
                                        <input type="text" class="form-control" id="author" name="author" 
                                               value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['author']) : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="draft" <?php echo ($edit_blog && $edit_blog['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo ($edit_blog && $edit_blog['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                                   <?php echo ($edit_blog && $edit_blog['featured']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="featured">
                                                Featured Post
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags (JSON format)</label>
                                        <input type="text" class="form-control" id="tags" name="tags" 
                                               value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['tags']) : ''; ?>" 
                                               placeholder='["tag1", "tag2"]'>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                               value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['meta_title']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?php echo $edit_blog ? htmlspecialchars($edit_blog['meta_description']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $edit_blog ? 'Update' : 'Add'; ?> Blog Post
                                </button>
                                <?php if ($edit_blog): ?>
                                    <a href="blogs.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Blog List -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Blog Posts</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Featured</th>
                                        <th>Views</th>
                                        <th>Published</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blogs as $blog): ?>
                                        <tr>
                                            <td><?php echo $blog['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($blog['title']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($blog['slug']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($blog['author']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $blog['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($blog['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($blog['featured']): ?>
                                                    <i class="fas fa-star text-warning"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($blog['views']); ?></td>
                                            <td>
                                                <?php if ($blog['published_at']): ?>
                                                    <?php echo date('M j, Y', strtotime($blog['published_at'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Not published</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="blogs.php?edit=<?php echo $blog['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize TinyMCE for content editor
        tinymce.init({
            selector: '#content',
            height: 400,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });

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
    </script>
</body>
</html>