<?php
require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Initialize arrays for fallback
$slideshow_images = [];
$featured_products = [];

if ($pdo) {
    try {
        // Fetch slideshow images
        $stmt = $pdo->query("SELECT * FROM slideshow_images WHERE status = 'active' ORDER BY sort_order ASC");
        $slideshow_images = $stmt->fetchAll();

        // Fetch featured products
        $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 'active' ORDER BY p.created_at DESC LIMIT 6");
        $featured_products = $stmt->fetchAll();
    } catch (Exception $e) {
        // If database queries fail, use empty arrays (fallback to static content)
        error_log("Database query error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌴 Palm Oil Co. - Premium Golden Quality Palm Oil Products</title>
    <meta name="description" content="Leading supplier of premium quality palm oil products. Sustainable and high-quality palm oil with golden excellence for all your needs.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Loading Animation -->
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand animate-pulse" href="index.html">
                🌴 Palm Oil Co.
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blogs.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Background Slideshow -->
        <div class="hero-slideshow">
            <?php if (!empty($slideshow_images)): ?>
                <?php foreach ($slideshow_images as $index => $slide): ?>
                    <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
                         style="background-image: url('<?php echo htmlspecialchars($slide['image_path']); ?>');">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback slides when database is not available -->
                <div class="hero-slide active" style="background-image: linear-gradient(135deg, #2c5530 0%, #4a7c59 50%, #6b8e23 100%);">
                </div>
                <div class="hero-slide" style="background-image: linear-gradient(135deg, #8b4513 0%, #cd853f 50%, #daa520 100%);">
                </div>
                <div class="hero-slide" style="background-image: linear-gradient(135deg, #556b2f 0%, #6b8e23 50%, #9acd32 100%);">
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Navigation Dots -->
        <div class="slideshow-nav">
            <?php if (!empty($slideshow_images)): ?>
                <?php foreach ($slideshow_images as $index => $slide): ?>
                    <div class="nav-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback navigation dots -->
                <div class="nav-dot active" data-slide="0"></div>
                <div class="nav-dot" data-slide="1"></div>
                <div class="nav-dot" data-slide="2"></div>
            <?php endif; ?>
        </div>
        
        <div class="container position-relative" style="z-index: 2;">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title animate-fade-up">
                        Premium <span style="color: var(--gold-primary);">Golden</span> Palm Oil
                    </h1>
                    <p class="hero-subtitle animate-fade-up" style="animation-delay: 0.2s;">
                        Sustainable excellence meets golden quality. We provide the finest palm oil products with a commitment to environmental responsibility and premium standards.
                    </p>
                    <div class="d-flex gap-3 animate-fade-up" style="animation-delay: 0.4s;">
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i> Explore Products
                        </a>
                        <a href="contact.html" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-phone me-2"></i> Get Quote
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 hero-image animate-float">
                    <div class="text-center">
                        <svg width="400" height="400" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                            <!-- Palm Tree SVG -->
                            <defs>
                                <linearGradient id="palmGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#228B22;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#FFD700;stop-opacity:1" />
                                </linearGradient>
                                <linearGradient id="trunkGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#8B4513;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#D2691E;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Palm Tree Trunk -->
                            <rect x="180" y="200" width="40" height="150" fill="url(#trunkGradient)" rx="20"/>
                            
                            <!-- Palm Leaves -->
                            <ellipse cx="200" cy="180" rx="80" ry="20" fill="url(#palmGradient)" transform="rotate(-30 200 180)"/>
                            <ellipse cx="200" cy="180" rx="80" ry="20" fill="url(#palmGradient)" transform="rotate(0 200 180)"/>
                            <ellipse cx="200" cy="180" rx="80" ry="20" fill="url(#palmGradient)" transform="rotate(30 200 180)"/>
                            <ellipse cx="200" cy="180" rx="80" ry="20" fill="url(#palmGradient)" transform="rotate(60 200 180)"/>
                            <ellipse cx="200" cy="180" rx="80" ry="20" fill="url(#palmGradient)" transform="rotate(-60 200 180)"/>
                            
                            <!-- Palm Fruits -->
                            <circle cx="170" cy="190" r="8" fill="#FFD700"/>
                            <circle cx="185" cy="195" r="8" fill="#FFA500"/>
                            <circle cx="200" cy="190" r="8" fill="#FFD700"/>
                            <circle cx="215" cy="195" r="8" fill="#FFA500"/>
                            <circle cx="230" cy="190" r="8" fill="#FFD700"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section bg-light-green">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title scroll-animate">Why Choose Our Golden Palm Oil?</h2>
                    <p class="section-subtitle scroll-animate">We are committed to providing the highest quality palm oil products with sustainable practices and golden excellence.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center scroll-animate">
                        <div class="feature-icon">
                            <i class="fas fa-leaf fa-4x" style="color: var(--green-primary);"></i>
                        </div>
                        <h4>100% Sustainable</h4>
                        <p class="text-muted">Environmentally responsible palm oil production with certified sustainable farming practices and zero deforestation commitment.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center scroll-animate" style="animation-delay: 0.2s;">
                        <div class="feature-icon">
                            <i class="fas fa-award fa-4x" style="color: var(--gold-primary);"></i>
                        </div>
                        <h4>Golden Quality</h4>
                        <p class="text-muted">Premium golden standards with rigorous testing, quality control processes, and international certifications for excellence.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center scroll-animate" style="animation-delay: 0.4s;">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast fa-4x" style="color: var(--green-secondary);"></i>
                        </div>
                        <h4>Lightning Delivery</h4>
                        <p class="text-muted">Ultra-fast and reliable worldwide delivery with premium packaging, temperature control, and real-time tracking.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Strengths Section -->
    <section class="section bg-white">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title scroll-animate">Our Strengths</h2>
                    <p class="section-subtitle scroll-animate">Discover what makes us the leading choice for premium palm oil products worldwide.</p>
                </div>
            </div>
            <div class="row g-4">
                <?php
                // Fetch strengths from database
                $strengths = [];
                if ($pdo) {
                    try {
                        $strengths_query = "SELECT * FROM our_strengths WHERE status = 'active' ORDER BY sort_order ASC";
                        $strengths_stmt = $pdo->prepare($strengths_query);
                        $strengths_stmt->execute();
                        $strengths = $strengths_stmt->fetchAll();
                    } catch (Exception $e) {
                        $strengths = [];
                    }
                }
                
                if (!empty($strengths)): ?>
                    <?php foreach ($strengths as $index => $strength): ?>
                        <div class="col-lg-6 col-md-6">
                            <div class="feature-card d-flex align-items-start scroll-animate" style="animation-delay: <?php echo $index * 0.2; ?>s;">
                                <div class="feature-icon me-4">
                                    <i class="<?php echo htmlspecialchars($strength['icon']); ?> fa-3x" style="color: var(--gold-primary);"></i>
                                </div>
                                <div>
                                    <h4 class="mb-3"><?php echo htmlspecialchars($strength['title']); ?></h4>
                                    <p class="text-muted"><?php echo htmlspecialchars($strength['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback content when database is not available -->
                    <div class="col-lg-6 col-md-6">
                        <div class="feature-card d-flex align-items-start scroll-animate">
                            <div class="feature-icon me-4">
                                <i class="fas fa-industry fa-3x" style="color: var(--gold-primary);"></i>
                            </div>
                            <div>
                                <h4 class="mb-3">Advanced Processing</h4>
                                <p class="text-muted">State-of-the-art processing facilities with cutting-edge technology ensuring the highest quality palm oil production.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="feature-card d-flex align-items-start scroll-animate" style="animation-delay: 0.2s;">
                            <div class="feature-icon me-4">
                                <i class="fas fa-globe fa-3x" style="color: var(--gold-primary);"></i>
                            </div>
                            <div>
                                <h4 class="mb-3">Global Reach</h4>
                                <p class="text-muted">Extensive distribution network spanning over 50 countries with reliable supply chain management.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="feature-card d-flex align-items-start scroll-animate" style="animation-delay: 0.4s;">
                            <div class="feature-icon me-4">
                                <i class="fas fa-certificate fa-3x" style="color: var(--gold-primary);"></i>
                            </div>
                            <div>
                                <h4 class="mb-3">Quality Assurance</h4>
                                <p class="text-muted">Rigorous quality control processes and international certifications ensuring premium standards.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="feature-card d-flex align-items-start scroll-animate" style="animation-delay: 0.6s;">
                            <div class="feature-icon me-4">
                                <i class="fas fa-leaf fa-3x" style="color: var(--gold-primary);"></i>
                            </div>
                            <div>
                                <h4 class="mb-3">Sustainability Focus</h4>
                                <p class="text-muted">Committed to sustainable practices with zero deforestation and environmental responsibility.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="section">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title scroll-animate">Golden Featured Products</h2>
                    <p class="section-subtitle scroll-animate">Discover our most popular premium palm oil products with golden quality standards.</p>
                </div>
            </div>
            <div class="row g-4">
                <?php if (empty($featured_products)): ?>
                    <!-- Fallback products when database is not available -->
                    <div class="col-lg-4 col-md-6">
                        <div class="product-card scroll-animate">
                            <div class="product-image">
                                <i class="fas fa-oil-can fa-4x" style="color: var(--white);"></i>
                            </div>
                            <div class="product-info">
                                <h5 class="product-title">Premium Crude Palm Oil</h5>
                                <p class="text-muted mb-3">High-quality crude palm oil perfect for industrial and commercial applications.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="product-price">Contact for Price</span>
                                    <a href="#" class="btn btn-secondary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-card scroll-animate" style="animation-delay: 0.2s;">
                            <div class="product-image">
                                <i class="fas fa-oil-can fa-4x" style="color: var(--white);"></i>
                            </div>
                            <div class="product-info">
                                <h5 class="product-title">Refined Palm Oil</h5>
                                <p class="text-muted mb-3">Premium refined palm oil with golden clarity for food and cosmetic industries.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="product-price">Contact for Price</span>
                                    <a href="#" class="btn btn-secondary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-card scroll-animate" style="animation-delay: 0.4s;">
                            <div class="product-image">
                                <i class="fas fa-oil-can fa-4x" style="color: var(--white);"></i>
                            </div>
                            <div class="product-info">
                                <h5 class="product-title">Palm Kernel Oil</h5>
                                <p class="text-muted mb-3">Specialty palm kernel oil with exceptional properties for premium applications.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="product-price">Contact for Price</span>
                                    <a href="#" class="btn btn-secondary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_products as $index => $product): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product-card scroll-animate" style="animation-delay: <?php echo $index * 0.2; ?>s;">
                                <div class="product-image">
                                    <?php if ($product['image']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-oil-can fa-4x" style="color: var(--white);"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($product['short_description'] ?: substr($product['description'], 0, 100) . '...'); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if ($product['price']): ?>
                                            <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="product-price">Contact for Price</span>
                                        <?php endif; ?>
                                        <a href="product-detail.php?slug=<?php echo htmlspecialchars($product['slug']); ?>" class="btn btn-secondary btn-sm">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5">
                <a href="products.php" class="btn btn-primary btn-lg scroll-animate">
                    <i class="fas fa-eye me-2"></i> Explore All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="section bg-light-gold">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="scroll-animate">
                        <h3 class="display-4 fw-bold" style="color: var(--green-dark);">15+</h3>
                        <p class="lead">Years of Excellence</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="scroll-animate" style="animation-delay: 0.1s;">
                        <h3 class="display-4 fw-bold" style="color: var(--gold-dark);">50+</h3>
                        <p class="lead">Countries Served</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="scroll-animate" style="animation-delay: 0.2s;">
                        <h3 class="display-4 fw-bold" style="color: var(--green-dark);">1M+</h3>
                        <p class="lead">Tons Delivered</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="scroll-animate" style="animation-delay: 0.3s;">
                        <h3 class="display-4 fw-bold" style="color: var(--gold-dark);">99%</h3>
                        <p class="lead">Customer Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Certifications & Awards Section -->
    <section class="section bg-light-green">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title scroll-animate">Certifications & Awards</h2>
                    <p class="section-subtitle scroll-animate">Recognized excellence through prestigious certifications and industry awards that validate our commitment to quality.</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                // Fetch certifications from database
                $certifications = [];
                if ($pdo) {
                    try {
                        $certifications_query = "SELECT * FROM certifications_awards WHERE status = 'active' ORDER BY sort_order ASC";
                        $certifications_stmt = $pdo->prepare($certifications_query);
                        $certifications_stmt->execute();
                        $certifications = $certifications_stmt->fetchAll();
                    } catch (Exception $e) {
                        $certifications = [];
                    }
                }
                
                if (!empty($certifications)): ?>
                    <?php foreach ($certifications as $index => $certification): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="certification-card text-center scroll-animate" style="animation-delay: <?php echo $index * 0.2; ?>s;">
                                <div class="certification-logo mb-3">
                                    <?php if ($certification['image']): ?>
                                        <img src="<?php echo htmlspecialchars($certification['image']); ?>" alt="<?php echo htmlspecialchars($certification['title']); ?>" style="max-height: 80px; max-width: 120px; object-fit: contain;">
                                    <?php else: ?>
                                        <i class="fas fa-award fa-4x" style="color: var(--gold-primary);"></i>
                                    <?php endif; ?>
                                </div>
                                <h5 class="certification-name"><?php echo htmlspecialchars($certification['title']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars($certification['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback content when database is not available -->
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="certification-card text-center scroll-animate">
                            <div class="certification-logo mb-3">
                                <i class="fas fa-certificate fa-4x" style="color: var(--gold-primary);"></i>
                            </div>
                            <h5 class="certification-name">ISO 9001:2015</h5>
                            <p class="text-muted small">Quality Management System certification ensuring consistent quality standards.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="certification-card text-center scroll-animate" style="animation-delay: 0.2s;">
                            <div class="certification-logo mb-3">
                                <i class="fas fa-leaf fa-4x" style="color: var(--green-primary);"></i>
                            </div>
                            <h5 class="certification-name">RSPO Certified</h5>
                            <p class="text-muted small">Roundtable on Sustainable Palm Oil certification for responsible production.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="certification-card text-center scroll-animate" style="animation-delay: 0.4s;">
                            <div class="certification-logo mb-3">
                                <i class="fas fa-shield-alt fa-4x" style="color: var(--gold-primary);"></i>
                            </div>
                            <h5 class="certification-name">HACCP</h5>
                            <p class="text-muted small">Hazard Analysis Critical Control Points for food safety management.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="certification-card text-center scroll-animate" style="animation-delay: 0.6s;">
                            <div class="certification-logo mb-3">
                                <i class="fas fa-trophy fa-4x" style="color: var(--gold-primary);"></i>
                            </div>
                            <h5 class="certification-name">Excellence Award</h5>
                            <p class="text-muted small">Industry recognition for outstanding quality and sustainable practices.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Vegetable Cooking Oil FAQs Section -->
    <section class="section bg-white">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title scroll-animate">Vegetable Cooking Oil FAQs</h2>
                    <p class="section-subtitle scroll-animate">Get answers to the most frequently asked questions about our premium vegetable cooking oil products.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <?php
                        // Fetch FAQs from database
                        $faqs = [];
                        if ($pdo) {
                            try {
                                $faqs_query = "SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC";
                                $faqs_stmt = $pdo->prepare($faqs_query);
                                $faqs_stmt->execute();
                                $faqs = $faqs_stmt->fetchAll();
                            } catch (Exception $e) {
                                $faqs = [];
                            }
                        }
                        
                        if (!empty($faqs)): ?>
                            <?php foreach ($faqs as $index => $faq): ?>
                                <div class="accordion-item scroll-animate" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                            <i class="fas fa-question-circle me-3" style="color: var(--gold-primary);"></i>
                                            <?php echo htmlspecialchars($faq['question']); ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback content when database is not available -->
                            <div class="accordion-item scroll-animate">
                                <h2 class="accordion-header" id="heading0">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse0" aria-expanded="true" aria-controls="collapse0">
                                        <i class="fas fa-question-circle me-3" style="color: var(--gold-primary);"></i>
                                        What makes your palm oil different from others?
                                    </button>
                                </h2>
                                <div id="collapse0" class="accordion-collapse collapse show" aria-labelledby="heading0" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="text-muted mb-0">Our palm oil is produced using sustainable practices with RSPO certification. We maintain the highest quality standards through advanced processing techniques and rigorous quality control measures, ensuring a premium golden quality product.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item scroll-animate" style="animation-delay: 0.1s;">
                                <h2 class="accordion-header" id="heading1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                                        <i class="fas fa-question-circle me-3" style="color: var(--gold-primary);"></i>
                                        Is your palm oil suitable for cooking at high temperatures?
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="heading1" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="text-muted mb-0">Yes, our refined palm oil has a high smoke point making it excellent for high-temperature cooking, deep frying, and industrial food processing applications.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item scroll-animate" style="animation-delay: 0.2s;">
                                <h2 class="accordion-header" id="heading2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                        <i class="fas fa-question-circle me-3" style="color: var(--gold-primary);"></i>
                                        What certifications do you have for quality assurance?
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="text-muted mb-0">We hold multiple certifications including ISO 9001:2015, RSPO certification, HACCP, and various international quality standards that ensure our products meet the highest industry requirements.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item scroll-animate" style="animation-delay: 0.3s;">
                                <h2 class="accordion-header" id="heading3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                        <i class="fas fa-question-circle me-3" style="color: var(--gold-primary);"></i>
                                        Do you offer bulk orders and custom packaging?
                                    </button>
                                </h2>
                                <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="text-muted mb-0">Absolutely! We specialize in bulk orders and offer custom packaging solutions to meet your specific requirements. Contact our sales team for personalized quotes and packaging options.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <div class="container">
                <div class="row align-items-center text-center">
                    <div class="col-lg-8 mx-auto">
                        <h3 class="display-5 fw-bold mb-4 scroll-animate">Ready to Experience Golden Excellence?</h3>
                        <p class="lead mb-4 scroll-animate" style="animation-delay: 0.2s;">
                            Join thousands of satisfied customers worldwide. Contact us today for bulk orders, custom requirements, and premium palm oil solutions.
                        </p>
                        <div class="d-flex gap-3 justify-content-center scroll-animate" style="animation-delay: 0.4s;">
                            <a href="contact.html" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-envelope me-2"></i> Get Instant Quote
                            </a>
                            <a href="products.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-phone me-2"></i> Call Now
                            </a>
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
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="contact.html">Contact</a></li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
    
    <script>
        // Loading animation
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loading').classList.add('hidden');
            }, 1000);
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-animate').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>