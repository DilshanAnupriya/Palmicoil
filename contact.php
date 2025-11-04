<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

// Helper: fetch settings by key
function getSetting($db, $key, $default = '') {
    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row && isset($row['setting_value']) && $row['setting_value'] !== '' ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Fetch page content for Contact
$contactPage = null;
try {
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = 'contact' AND status = 'published' LIMIT 1");
    $stmt->execute();
    $contactPage = $stmt->fetch();
} catch (Exception $e) {
    $contactPage = null;
}

// Fetch FAQs
$faqs = [];
try {
    $stmt = $db->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC");
    $faqs = $stmt->fetchAll();
} catch (Exception $e) {
    $faqs = [];
}

// Settings
$contactAddress = getSetting($db, 'contact_address', "123 Palm Street\nOil City, PC 12345\nUnited States");
$contactPhone   = getSetting($db, 'contact_phone', '+1 (555) 123-4567');
$contactEmail   = getSetting($db, 'contact_email', 'info@palmoilco.com');
$businessHours  = getSetting($db, 'business_hours', "Monday - Friday: 8:00 AM - 6:00 PM\nSaturday: 9:00 AM - 4:00 PM\nSunday: Closed");

// Build Google Maps URL from address
$mapsQuery = urlencode(str_replace("\n", ' ', $contactAddress));
$mapsUrl = "https://maps.google.com/maps?q=" . $mapsQuery;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Palm Oil Company</title>
    <meta name="description" content="Get in touch with our palm oil experts. Contact us for inquiries, bulk orders, and custom solutions.">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Loading Animation -->
    <div id="loading-screen" class="loading-screen">
        <div class="loading-content">
            <div class="palm-loader">
                <div class="palm-leaf"></div>
                <div class="palm-leaf"></div>
                <div class="palm-leaf"></div>
            </div>
            <h3 class="loading-text">Loading Contact...</h3>
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
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blogs.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="contact-hero text-white text-center position-relative overflow-hidden">
        <div class="hero-background"></div>
        <div class="hero-overlay"></div>
        <div class="hero-particles"></div>
        <div class="container position-relative z-index-3">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="display-3 fw-bold mb-4 fade-in-up">Get In Touch</h1>
                    <p class="lead mb-5 fade-in-up" style="animation-delay: 0.2s;">
                        <?php
                        if ($contactPage && !empty($contactPage['content'])) {
                            echo $contactPage['content'];
                        } else {
                            echo 'Connect with our palm oil experts for inquiries, bulk orders, partnerships, and custom solutions. We\'re committed to providing exceptional service and sustainable products.';
                        }
                        ?>
                    </p>
                    <div class="hero-cta-buttons fade-in-up" style="animation-delay: 0.4s;">
                        <a href="#contactForm" class="btn btn-golden btn-lg me-3 mb-3">
                            <i class="fas fa-envelope me-2"></i>Send Message
                        </a>
                        <a href="tel:<?= htmlspecialchars($contactPhone) ?>" class="btn btn-outline-light btn-lg mb-3">
                            <i class="fas fa-phone me-2"></i>Call Now
                        </a>
                    </div>
                    <div class="hero-stats mt-5 fade-in-up" style="animation-delay: 0.6s;">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <div class="stat-item">
                                    <h3 class="golden-text mb-1">24/7</h3>
                                    <p class="mb-0">Customer Support</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stat-item">
                                    <h3 class="golden-text mb-1">&lt;24h</h3>
                                    <p class="mb-0">Response Time</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stat-item">
                                    <h3 class="golden-text mb-1">50+</h3>
                                    <p class="mb-0">Countries Served</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="section-padding">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-modern text-center p-4 h-100 scroll-animate" data-animation="fadeInUp">
                        <div class="feature-icon-modern mb-3">
                            <i class="fas fa-map-marker-alt fa-3x"></i>
                        </div>
                        <h5 class="golden-text">Our Location</h5>
                        <p class="text-muted mb-0" style="white-space: pre-line;">
                            <?= htmlspecialchars($contactAddress) ?>
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-modern text-center p-4 h-100 scroll-animate" data-animation="fadeInUp" data-delay="0.2">
                        <div class="feature-icon-modern mb-3">
                            <i class="fas fa-phone fa-3x"></i>
                        </div>
                        <h5 class="golden-text">Phone</h5>
                        <p class="text-muted mb-0">
                            Phone: <?= htmlspecialchars($contactPhone) ?>
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-modern text-center p-4 h-100 scroll-animate" data-animation="fadeInUp" data-delay="0.4">
                        <div class="feature-icon-modern mb-3">
                            <i class="fas fa-envelope fa-3x"></i>
                        </div>
                        <h5 class="golden-text">Email</h5>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($contactEmail) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Form and Map -->
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-8 mb-5">
                    <div class="contact-form-modern scroll-animate" data-animation="fadeInLeft">
                        <div class="form-header mb-4">
                            <h3 class="golden-text mb-2">Send us a Message</h3>
                            <p class="text-muted">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                        </div>
                        <form id="contactForm" action="contact-handler.php" method="POST" class="modern-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name *</nlabel>
                                    <input type="text" class="form-control modern-input" id="firstName" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control modern-input" id="lastName" name="last_name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control modern-input" id="email" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control modern-input" id="phone" name="phone">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="company" class="form-label">Company Name</label>
                                <input type="text" class="form-control modern-input" id="company" name="company">
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <select class="form-select modern-input" id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="product">Product Information</option>
                                    <option value="bulk">Bulk Order</option>
                                    <option value="partnership">Partnership</option>
                                    <option value="support">Technical Support</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control modern-input" id="message" name="message" rows="6" 
                                          placeholder="Please provide details about your inquiry..." required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check modern-checkbox">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" value="1">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for updates and special offers
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-golden btn-lg btn-hover-effect">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Business Hours and Additional Info -->
                <div class="col-lg-4">
                    <div class="info-card-modern p-4 mb-4 scroll-animate" data-animation="fadeInRight">
                        <h5 class="mb-3 golden-text">
                            <i class="fas fa-clock green-text me-2"></i>Business Hours
                        </h5>
                        <?php $hoursLines = preg_split('/\r\n|\r|\n/', $businessHours); ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($hoursLines as $line): ?>
                                <li class="d-flex justify-content-between mb-2 info-item">
                                    <span><?= htmlspecialchars($line) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="info-card-modern p-4 mb-4 scroll-animate" data-animation="fadeInRight" data-delay="0.2">
                        <h5 class="mb-3 golden-text">
                            <i class="fas fa-shipping-fast green-text me-2"></i>Quick Response
                        </h5>
                        <p class="mb-2 info-item">
                            <i class="fas fa-check green-text me-2"></i>
                            Email responses within 24 hours
                        </p>
                        <p class="mb-2 info-item">
                            <i class="fas fa-check green-text me-2"></i>
                            Phone support during business hours
                        </p>
                        <p class="mb-0 info-item">
                            <i class="fas fa-check green-text me-2"></i>
                            Emergency contact available
                        </p>
                    </div>

                    <div class="info-card-modern p-4 scroll-animate" data-animation="fadeInRight" data-delay="0.4">
                        <h5 class="mb-3 golden-text">
                            <i class="fas fa-handshake green-text me-2"></i>Partnership
                        </h5>
                        <p class="mb-3">Interested in becoming a distributor or partner? We'd love to hear from you.</p>
                        <a href="mailto:<?= htmlspecialchars($contactEmail) ?>" class="btn btn-green btn-hover-effect">
                            <i class="fas fa-envelope me-2"></i>Partnership Inquiry
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-0">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-12">
                    <div class="map-container-modern position-relative" style="height: 500px;">
                        <!-- Google Maps Embed -->
                        <iframe 
                            src="https://www.google.com/maps?q=<?= urlencode(str_replace("\n", ' ', $contactAddress)) ?>&output=embed"
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            class="google-map">
                        </iframe>
                        
                        <!-- Map Overlay with Contact Info -->
                        <div class="map-info-overlay position-absolute top-0 start-0 m-4 scroll-animate" data-animation="fadeInLeft">
                            <div class="map-info-card p-4">
                                <h5 class="golden-text mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>Visit Our Office
                                </h5>
                                <p class="mb-2"><strong>Address:</strong></p>
                                <p class="mb-3 text-muted" style="white-space: pre-line;">
                                    <?= htmlspecialchars($contactAddress) ?>
                                </p>
                                <div class="d-flex flex-column gap-2">
                                    <a href="<?= htmlspecialchars($mapsUrl) ?>" 
                                       target="_blank" 
                                       class="btn btn-golden btn-sm">
                                        <i class="fas fa-directions me-2"></i>Get Directions
                                    </a>
                                    <a href="tel:<?= htmlspecialchars($contactPhone) ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-phone me-2"></i>Call Us
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map Controls -->
                        <div class="map-controls position-absolute bottom-0 end-0 m-4">
                            <div class="btn-group-vertical" role="group">
                                <button type="button" class="btn btn-light btn-sm map-control-btn" onclick="toggleMapType()">
                                    <i class="fas fa-layer-group"></i>
                                </button>
                                <button type="button" class="btn btn-light btn-sm map-control-btn" onclick="centerMap()">
                                    <i class="fas fa-crosshairs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section-padding bg-gradient-light" style="margin-top: 80px; margin-bottom: 40px;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="golden-text scroll-animate" data-animation="fadeInUp">Frequently Asked Questions</h3>
                    <p class="text-muted scroll-animate" data-animation="fadeInUp" data-delay="0.2">Quick answers to common questions</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion modern-accordion scroll-animate" id="faqAccordion" data-animation="fadeInUp" data-delay="0.4">
                        <?php if (!empty($faqs)): ?>
                            <?php $i = 0; foreach ($faqs as $faq): $i++; $collapseId = 'faq' . $i; ?>
                                <div class="accordion-item modern-accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button modern-accordion-button" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#<?= $collapseId ?>">
                                            <?= htmlspecialchars($faq['question']) ?>
                                        </button>
                                    </h2>
                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $i === 1 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body modern-accordion-body">
                                            <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No FAQs available yet. Please add FAQs from the admin dashboard.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script>
        // Map controls placeholders – customization via main.js if needed
        function toggleMapType() { /* handled by iframe limitations */ }
        function centerMap() { /* handled by iframe limitations */ }
    </script>
</body>
</html>