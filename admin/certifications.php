<?php
require_once '../config/config.php';
requireLogin();

$db = getDB();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM certifications_awards WHERE id = ?");
    $stmt->execute([$id]);
    redirect(ADMIN_URL . '/certifications.php');
}

// Get all certifications
$stmt = $db->query("SELECT * FROM certifications_awards ORDER BY sort_order ASC, created_at DESC");
$certifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certifications & Awards - Palm Oil Admin</title>
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
        .cert-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #ddd;
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
                        <a class="nav-link active" href="certifications.php">
                            <i class="fas fa-certificate me-2"></i> Certifications
                        </a>
                        <a class="nav-link" href="faqs.php">
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
                        <h2>Certifications & Awards</h2>
                        <a href="certification-form.php" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i> Add Certification
                        </a>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($certifications)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No certifications found</h5>
                                    <p class="text-muted">Start by adding your first certification.</p>
                                    <a href="certification-form.php" class="btn btn-success">Add Certification</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Organization</th>
                                                <th>Order</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($certifications as $cert): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($cert['image']): ?>
                                                            <img src="<?php echo UPLOAD_URL . $cert['image']; ?>" 
                                                                 alt="<?php echo htmlspecialchars($cert['title']); ?>" 
                                                                 class="cert-logo">
                                                        <?php else: ?>
                                                            <div class="cert-logo bg-light d-flex align-items-center justify-content-center">
                                                                <i class="fas fa-certificate text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($cert['title']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $cert['type'] == 'certification' ? 'primary' : 'success'; ?>">
                                                            <?php echo ucfirst($cert['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($cert['issuing_organization'] ?? 'N/A'); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo $cert['sort_order']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $cert['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                            <?php echo ucfirst($cert['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="certification-form.php?id=<?php echo $cert['id']; ?>" 
                                                               class="btn btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="certifications.php?delete=<?php echo $cert['id']; ?>" 
                                                               class="btn btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this certification?')">
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>