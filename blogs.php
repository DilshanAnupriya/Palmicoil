<?php
require_once 'config/config.php';

// Initialize database connection
$db = getDB();

// Handle database connection errors
if (!$db) {
    // Fallback data when database is not available
    $blogs = [];
    $featured_blogs = [];
    $total_posts = 0;
    $total_pages = 1;
} else {

// Pagination settings
$posts_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search)) {
    $search_condition = " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $search_params = ["%$search%", "%$search%", "%$search%"];
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM blogs WHERE status = 'published'" . $search_condition;
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($search_params);
$total_posts = $count_stmt->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page);

// Fetch blogs
$sql = "SELECT * FROM blogs WHERE status = 'published'" . $search_condition . " ORDER BY featured DESC, published_at DESC LIMIT " . (int)$posts_per_page . " OFFSET " . (int)$offset;
$stmt = $db->prepare($sql);
$stmt->execute($search_params);
$blogs = $stmt->fetchAll();

// Get featured blogs for sidebar
$featured_stmt = $db->prepare("SELECT * FROM blogs WHERE status = 'published' AND featured = 1 ORDER BY published_at DESC LIMIT 3");
$featured_stmt->execute();
$featured_blogs = $featured_stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Palmicoil</title>
    <meta name="description" content="Read the latest insights, news, and updates from Palmicoil about palm oil industry, sustainability, and innovation.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
    
    <style>
        .blog-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('assets/1800x1200_palm_oil_in_glass_bowl_other.webp') center/cover no-repeat;
            color: white;
            padding: 220px 0;
            min-height: 60vh;
            position: relative;
        }
        
        .blog-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
        }
        
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .blog-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .blog-meta {
            font-size: 0.9rem;
            color: #666;
        }
        
        .blog-excerpt {
            color: #555;
            line-height: 1.6;
        }
        
        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ffc107;
            color: #000;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .sidebar-blog {
            border-left: 3px solid #2c5530;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        
        .sidebar-blog h6 {
            color: #2c5530;
            margin-bottom: 5px;
        }
        
        .sidebar-blog small {
            color: #666;
        }
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .pagination .page-link {
            color: #2c5530;
            border-color: #2c5530;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #2c5530;
            border-color: #2c5530;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
   <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand animate-pulse" href="index.html">
                <img src="assets/white Logo (1)_page-0001.jpg" alt="Palmic Oil" class="navbar-logo">
                <span class="company-name">Palmic Oil</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link " href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="blogs.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Hero Section -->
    <section class="blog-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Our Blogs</h1>
                    <p class="lead mb-4">Stay updated with the latest insights, news, and innovations in the palm oil industry. Discover sustainable practices, market trends, and expert knowledge.</p>
                </div>
                <div class="col-lg-4">
                    <div class="search-box">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control border-0" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <?php if (!empty($search)): ?>
                        <div class="mb-4">
                            <h5>Search Results for "<?php echo htmlspecialchars($search); ?>"</h5>
                            <p class="text-muted"><?php echo $total_posts; ?> article(s) found</p>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($blogs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>No articles found</h4>
                            <p class="text-muted">
                                <?php if (!empty($search)): ?>
                                    Try adjusting your search terms or <a href="blogs.php">browse all articles</a>.
                                <?php else: ?>
                                    Check back soon for new content!
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($blogs as $blog): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card blog-card position-relative">
                                        <?php if ($blog['featured']): ?>
                                            <div class="featured-badge">
                                                <i class="fas fa-star"></i> Featured
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($blog['featured_image']): ?>
                                            <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="card-img-top">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <div class="blog-meta mb-2">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($blog['author']); ?>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($blog['published_at'])); ?>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-eye"></i> <?php echo number_format($blog['views']); ?> views
                                            </div>
                                            
                                            <h5 class="card-title">
                                                <a href="blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>" class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($blog['title']); ?>
                                                </a>
                                            </h5>
                                            
                                            <p class="blog-excerpt"><?php echo htmlspecialchars($blog['excerpt']); ?></p>
                                            
                                            <a href="blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>" class="btn btn-outline-success">
                                                Read More <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Blog pagination" class="mt-5">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 100px;">
                        <!-- Featured Articles -->
                        <?php if (!empty($featured_blogs)): ?>
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-star"></i> Featured Articles</h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($featured_blogs as $featured): ?>
                                        <div class="sidebar-blog">
                                            <h6>
                                                <a href="blog-detail.php?slug=<?php echo urlencode($featured['slug']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($featured['title']); ?>
                                                </a>
                                            </h6>
                                            <small>
                                                <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($featured['published_at'])); ?>
                                                <span class="mx-1">•</span>
                                                <i class="fas fa-eye"></i> <?php echo number_format($featured['views']); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Categories/Tags -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-tags"></i> Popular Topics</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-outline-success">Sustainability</span>
                                    <span class="badge bg-outline-success">Quality Standards</span>
                                    <span class="badge bg-outline-success">Health Benefits</span>
                                    <span class="badge bg-outline-success">Market Trends</span>
                                    <span class="badge bg-outline-success">Innovation</span>
                                    <span class="badge bg-outline-success">Processing</span>
                                </div>
                            </div>
                        </div>

                        <!-- Newsletter Signup -->
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-envelope"></i> Stay Updated</h6>
                            </div>
                            <div class="card-body">
                                <p class="small">Subscribe to our newsletter for the latest industry insights and updates.</p>
                                <form>
                                    <div class="mb-3">
                                        <input type="email" class="form-control" placeholder="Your email address" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-paper-plane"></i> Subscribe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
        <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">🌴 Palm Oil Co.</h5>
                    <p class="text-light mb-4">Leading supplier of premium golden quality palm oil products with an unwavering commitment to sustainability, excellence, and customer satisfaction.</p>
                    <div class="social-links">
                        <a href="#" class="animate-pulse"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="animate-pulse" style="animation-delay: 0.1s;"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="animate-pulse" style="animation-delay: 0.2s;"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="animate-pulse" style="animation-delay: 0.3s;"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="mb-3">Premium Products</h6>
                    <ul class="list-unstyled">
                        <li><a href="#">Golden Crude Palm Oil</a></li>
                        <li><a href="#">Premium Refined Palm Oil</a></li>
                        <li><a href="#">Specialty Palm Kernel Oil</a></li>
                        <li><a href="#">Custom Blends</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2" style="color: var(--gold-primary);"></i> 123 Golden Palm Street, Oil City</li>
                        <li><i class="fas fa-phone me-2" style="color: var(--gold-primary);"></i> +1-234-567-8900</li>
                        <li><i class="fas fa-envelope me-2" style="color: var(--gold-primary);"></i> info@palmicoil.com</li>
                        <li><i class="fas fa-clock me-2" style="color: var(--gold-primary);"></i> 24/7 Customer Support</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: var(--gold-primary);">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 Palm Oil Company. All rights reserved. Golden Excellence Since 2009.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#" class="me-3">Terms of Service</a>
                    <a href="#">Sustainability</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>