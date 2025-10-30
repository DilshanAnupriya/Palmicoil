<?php
require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Get blog slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: blogs.php');
    exit();
}

// Fetch blog post
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit();
}

// Update view count
$update_views = $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
$update_views->execute([$blog['id']]);

// Get related blogs (same tags or recent posts)
$related_stmt = $pdo->prepare("SELECT * FROM blogs WHERE id != ? AND status = 'published' ORDER BY published_at DESC LIMIT 3");
$related_stmt->execute([$blog['id']]);
$related_blogs = $related_stmt->fetchAll();

// Parse tags
$tags = [];
if (!empty($blog['tags'])) {
    $tags = json_decode($blog['tags'], true) ?: [];
}

// Get previous and next blog posts
$prev_stmt = $pdo->prepare("SELECT id, title, slug FROM blogs WHERE published_at < ? AND status = 'published' ORDER BY published_at DESC LIMIT 1");
$prev_stmt->execute([$blog['published_at']]);
$prev_blog = $prev_stmt->fetch();

$next_stmt = $pdo->prepare("SELECT id, title, slug FROM blogs WHERE published_at > ? AND status = 'published' ORDER BY published_at ASC LIMIT 1");
$next_stmt->execute([$blog['published_at']]);
$next_blog = $next_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['meta_title'] ?: $blog['title']); ?> - Palmicoil</title>
    <meta name="description" content="<?php echo htmlspecialchars($blog['meta_description'] ?: $blog['excerpt']); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($blog['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($blog['excerpt']); ?>">
    <meta property="og:image" content="<?php echo $blog['featured_image'] ? 'https://' . $_SERVER['HTTP_HOST'] . '/' . $blog['featured_image'] : ''; ?>">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="article">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($blog['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($blog['excerpt']); ?>">
    <meta name="twitter:image" content="<?php echo $blog['featured_image'] ? 'https://' . $_SERVER['HTTP_HOST'] . '/' . $blog['featured_image'] : ''; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
    
    <style>
        .blog-header {
            background: linear-gradient(135deg, #2c5530 0%, #3e7b3e 100%);
            color: white;
            padding: 100px 0 50px;
        }
        
        .blog-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        
        .blog-content h2 {
            color: #2c5530;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .blog-content h3 {
            color: #3e7b3e;
            margin-top: 1.5rem;
            margin-bottom: 0.8rem;
        }
        
        .blog-content ul, .blog-content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        
        .blog-content li {
            margin-bottom: 0.5rem;
        }
        
        .blog-meta {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            background: #2c5530;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .share-buttons {
            position: sticky;
            top: 100px;
        }
        
        .share-btn {
            display: block;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }
        
        .share-btn:hover {
            transform: scale(1.1);
            color: white;
        }
        
        .share-facebook { background: #3b5998; }
        .share-twitter { background: #1da1f2; }
        .share-linkedin { background: #0077b5; }
        .share-whatsapp { background: #25d366; }
        
        .related-blog {
            transition: transform 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .related-blog:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .related-blog img {
            height: 150px;
            object-fit: cover;
        }
        
        .navigation-links {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin: 40px 0;
        }
        
        .nav-link-item {
            flex: 1;
            text-decoration: none;
            color: #333;
            padding: 20px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        
        .nav-link-item:hover {
            background: white;
            color: #2c5530;
        }
        
        .tag-badge {
            background: #e9f7ef;
            color: #2c5530;
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            margin: 2px;
            display: inline-block;
        }
        
        .tag-badge:hover {
            background: #2c5530;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="index.php">
                <i class="fas fa-leaf me-2"></i>Palmicoil
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
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

    <!-- Blog Header -->
    <section class="blog-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <?php if ($blog['featured']): ?>
                        <span class="badge bg-warning text-dark mb-3">
                            <i class="fas fa-star"></i> Featured Article
                        </span>
                    <?php endif; ?>
                    
                    <h1 class="display-5 fw-bold mb-4"><?php echo htmlspecialchars($blog['title']); ?></h1>
                    <p class="lead mb-4"><?php echo htmlspecialchars($blog['excerpt']); ?></p>
                    
                    <div class="d-flex justify-content-center align-items-center gap-4 text-light">
                        <div>
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($blog['author']); ?>
                        </div>
                        <div>
                            <i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($blog['published_at'])); ?>
                        </div>
                        <div>
                            <i class="fas fa-eye"></i> <?php echo number_format($blog['views']); ?> views
                        </div>
                        <div>
                            <i class="fas fa-clock"></i> <?php echo ceil(str_word_count(strip_tags($blog['content'])) / 200); ?> min read
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Share Buttons (Desktop) -->
                <div class="col-lg-1 d-none d-lg-block">
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="share-btn share-facebook" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($blog['title']); ?>" 
                           target="_blank" class="share-btn share-twitter" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="share-btn share-linkedin" title="Share on LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($blog['title'] . ' - ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="share-btn share-whatsapp" title="Share on WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Featured Image -->
                    <?php if ($blog['featured_image']): ?>
                        <div class="mb-4">
                            <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($blog['title']); ?>" 
                                 class="img-fluid rounded shadow">
                        </div>
                    <?php endif; ?>

                    <!-- Blog Meta -->
                    <div class="blog-meta">
                        <div class="author-info">
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($blog['author'], 0, 1)); ?>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($blog['author']); ?></h6>
                                <small class="text-muted">Published on <?php echo date('F j, Y', strtotime($blog['published_at'])); ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Blog Content -->
                    <div class="blog-content">
                        <?php echo $blog['content']; ?>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($tags)): ?>
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="mb-3">Tags:</h6>
                            <div>
                                <?php foreach ($tags as $tag): ?>
                                    <a href="blogs.php?search=<?php echo urlencode($tag); ?>" class="tag-badge">
                                        <?php echo htmlspecialchars($tag); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Share Buttons (Mobile) -->
                    <div class="d-lg-none mt-4 pt-4 border-top">
                        <h6 class="mb-3">Share this article:</h6>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                               target="_blank" class="btn btn-primary btn-sm">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($blog['title']); ?>" 
                               target="_blank" class="btn btn-info btn-sm text-white">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                               target="_blank" class="btn btn-primary btn-sm">
                                <i class="fab fa-linkedin-in"></i> LinkedIn
                            </a>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <?php if ($prev_blog || $next_blog): ?>
                        <div class="navigation-links">
                            <div class="d-flex">
                                <?php if ($prev_blog): ?>
                                    <a href="blog-detail.php?slug=<?php echo urlencode($prev_blog['slug']); ?>" class="nav-link-item text-start">
                                        <div class="small text-muted mb-1">
                                            <i class="fas fa-chevron-left"></i> Previous Article
                                        </div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($prev_blog['title']); ?></div>
                                    </a>
                                <?php else: ?>
                                    <div class="nav-link-item"></div>
                                <?php endif; ?>

                                <?php if ($next_blog): ?>
                                    <a href="blog-detail.php?slug=<?php echo urlencode($next_blog['slug']); ?>" class="nav-link-item text-end">
                                        <div class="small text-muted mb-1">
                                            Next Article <i class="fas fa-chevron-right"></i>
                                        </div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($next_blog['title']); ?></div>
                                    </a>
                                <?php else: ?>
                                    <div class="nav-link-item"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="sticky-top" style="top: 100px;">
                        <!-- Back to Blog -->
                        <div class="mb-4">
                            <a href="blogs.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-arrow-left"></i> Back to Blog
                            </a>
                        </div>

                        <!-- Related Articles -->
                        <?php if (!empty($related_blogs)): ?>
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-newspaper"></i> Related Articles</h6>
                                </div>
                                <div class="card-body p-0">
                                    <?php foreach ($related_blogs as $related): ?>
                                        <div class="related-blog">
                                            <a href="blog-detail.php?slug=<?php echo urlencode($related['slug']); ?>" class="text-decoration-none">
                                                <?php if ($related['featured_image']): ?>
                                                    <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                                         class="w-100">
                                                <?php endif; ?>
                                                <div class="p-3">
                                                    <h6 class="text-dark mb-2"><?php echo htmlspecialchars($related['title']); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($related['published_at'])); ?>
                                                    </small>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

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
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="text-success mb-3">
                        <i class="fas fa-leaf me-2"></i>Palmicoil
                    </h5>
                    <p>Leading the way in sustainable palm oil production with quality, innovation, and environmental responsibility.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="about.php" class="text-light text-decoration-none">About</a></li>
                        <li><a href="products.php" class="text-light text-decoration-none">Products</a></li>
                        <li><a href="blogs.php" class="text-light text-decoration-none">Blog</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h6>Contact Info</h6>
                    <p class="mb-1"><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</p>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i> info@palmicoil.com</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Palm Street, City, Country</p>
                </div>
                <div class="col-lg-3 mb-4">
                    <h6>Follow Us</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 Palmicoil. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-light text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>