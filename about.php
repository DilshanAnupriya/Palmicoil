<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

// Fetch page content for About
$aboutPage = null;
try {
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = 'about' AND status = 'published' LIMIT 1");
    $stmt->execute();
    $aboutPage = $stmt->fetch();
} catch (Exception $e) {
    $aboutPage = null;
}

// Helpers to safely output HTML content
function safeHTML($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $aboutPage && !empty($aboutPage['meta_title']) ? safeHTML($aboutPage['meta_title']) : 'About Us - Golden Palm Oil Company' ?></title>
    <meta name="description" content="<?= $aboutPage && !empty($aboutPage['meta_description']) ? safeHTML($aboutPage['meta_description']) : 'Learn about our commitment to sustainable palm oil production, our history, values, and dedication to quality.' ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Loading Animation -->
    <div id="loading" class="loading-screen">
        <div class="loading-content">
            <div class="palm-loader">
                <svg width="80" height="80" viewBox="0 0 100 100">
                    <path d="M50 10 Q30 30 20 50 Q30 70 50 90 Q70 70 80 50 Q70 30 50 10" 
                          fill="var(--gold-primary)" class="palm-leaf"/>
                    <circle cx="50" cy="50" r="8" fill="var(--green-primary)" class="palm-center"/>
                </svg>
            </div>
            <div class="loading-text">Golden Palm Oil</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand animate-pulse" href="index.php">
                <img src="assets/white Logo (1)_page-0001.jpg" alt="Palmic Oil" class="navbar-logo">
                <span class="company-name">Palmic Oil</span>
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
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blogs.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Hero Section -->
    <section class="hero-section about-hero">
        <div class="hero-background"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6 scroll-animate animate-fade-right">
                    <div class="hero-content">
                        <h1 class="hero-title display-3 fw-bold mb-4">
                            About <span class="text-gradient">Us</span>
                        </h1>
                        <p class="hero-subtitle lead mb-4">
                            <?php if ($aboutPage && !empty($aboutPage['excerpt'])): ?>
                                <?= $aboutPage['excerpt'] ?>
                            <?php else: ?>
                                For over two decades, we have been at the forefront of sustainable palm oil production, 
                                committed to delivering premium quality products while protecting our environment and 
                                supporting local communities.
                            <?php endif; ?>
                        </p>
                        
                        <a href="#our-story" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-down me-2"></i>Discover Our Journey
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Stats (static for now) -->
    <section class="modern-impact-section section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5 scroll-animate">
                    <div class="section-badge">Our Impact</div>
                    <h2 class="section-title">Our <span class="text-gradient">Impact Numbers</span></h2>
                    <p class="section-subtitle">Measurable excellence across every aspect of our operations</p>
                </div>
            </div>
            <!-- Keep existing static impact cards -->
        </div>
    </section>

    <!-- Our Story -->
    <section id="our-story" class="modern-story-section section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5 m scroll-animate">
                    <div class="section-header">
                        <span class="section-badge">Our Journey</span>
                        <h2 class="section-title">The <span class="text-gradient">Story</span> Behind Our Success</h2>
                        <p class="section-subtitle">From humble beginnings to global leadership in sustainable palm oil production</p>
                    </div>
                </div>
            </div>
            
            <div class="row justify-content-center align-items-center mb-5">
                <div class="col-lg-8 col-xl-7 scroll-animate animate-fade-left">
                    <div class="modern-story-content">
                        <div class="story-text">
                            <h3 class="story-heading">From Vision to Reality</h3>
                            <?php if ($aboutPage && !empty($aboutPage['content'])): ?>
                                <?= $aboutPage['content'] ?>
                            <?php else: ?>
                                <p class="story-paragraph">
                                    Founded in 1999, Golden Palm began as a small family business with a vision to produce 
                                    the highest quality palm oil while maintaining environmental responsibility. What started 
                                    as a single plantation has grown into a global operation spanning multiple countries.
                                </p>
                                <p class="story-paragraph">
                                    Our journey has been marked by continuous innovation, sustainable practices, and an 
                                    unwavering commitment to quality. We've invested heavily in research and development 
                                    to ensure our products meet the highest international standards.
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Keep timeline static for now -->
                        <div class="modern-timeline">
                            <div class="timeline-header">
                                <h4>Key Milestones</h4>
                            </div>
                            <div class="timeline-items">
                                <div class="modern-timeline-item scroll-animate" data-delay="100">
                                    <div class="timeline-marker">
                                        <div class="timeline-dot"></div>
                                        <div class="timeline-line"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-year">1999</div>
                                        <div class="timeline-title">Company Founded</div>
                                        <div class="timeline-description">Started with a single plantation and a vision for sustainable production</div>
                                    </div>
                                </div>
                                
                                <div class="modern-timeline-item scroll-animate" data-delay="200">
                                    <div class="timeline-marker">
                                        <div class="timeline-dot"></div>
                                        <div class="timeline-line"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-year">2005</div>
                                        <div class="timeline-title">RSPO Certification</div>
                                        <div class="timeline-description">Achieved certification for sustainable palm oil practices</div>
                                    </div>
                                </div>
                                
                                <div class="modern-timeline-item scroll-animate" data-delay="300">
                                    <div class="timeline-marker">
                                        <div class="timeline-dot"></div>
                                        <div class="timeline-line"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-year">2010</div>
                                        <div class="timeline-title">International Expansion</div>
                                        <div class="timeline-description">Expanded operations to serve global markets</div>
                                    </div>
                                </div>
                                
                                <div class="modern-timeline-item scroll-animate" data-delay="400">
                                    <div class="timeline-marker">
                                        <div class="timeline-dot"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-year">2020</div>
                                        <div class="timeline-title">Carbon Neutral Goal</div>
                                        <div class="timeline-description">Committed to achieving carbon neutrality by 2030</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section: keep existing static layout -->
    <section class="modern-values-section section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5 scroll-animate">
                    <div class="values-header">
                        <span class="section-badge">Our Foundation</span>
                        <h2 class="section-title">Core <span class="text-gradient">Values</span> That Drive Us</h2>
                        <p class="section-subtitle">The principles that guide everything we do and shape our commitment to excellence</p>
                    </div>
                </div>
            </div>
            <!-- Keep values cards static for now -->
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>