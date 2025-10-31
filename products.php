<?php
require_once 'config/database.php';
require_once 'config/config.php';

$db = getDB();

// Check if database connection is successful
if (!$db) {
    die("Database connection failed. Please check your database configuration.");
}

// Get categories for filter
$categoriesQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories = $db->query($categoriesQuery)->fetchAll(PDO::FETCH_ASSOC);

// Get filter parameters
$categoryFilter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name';

// Build query
$whereConditions = ["p.status = 'active'"];
$params = [];

if ($categoryFilter) {
    $whereConditions[] = "p.category_id = :category_id";
    $params['category_id'] = $categoryFilter;
}

if ($search) {
    $whereConditions[] = "(p.name LIKE :search OR p.description LIKE :search)";
    $params['search'] = "%$search%";
}

$whereClause = implode(' AND ', $whereConditions);

// Sort options
$sortOptions = [
    'name' => 'p.name ASC',
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'newest' => 'p.created_at DESC'
];

$orderBy = $sortOptions[$sort] ?? $sortOptions['name'];

$query = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $whereClause 
    ORDER BY $orderBy
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - Palm Oil Company</title>
    <meta name="description" content="Explore our premium palm oil products. High-quality, sustainable palm oil for various applications.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html">
                <i class="fas fa-leaf text-success me-2"></i>PalmOil Co.
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="hero-section text-white text-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Our Premium Products</h1>
                    <p class="lead mb-0">Discover our range of high-quality palm oil products, sustainably sourced and carefully processed for excellence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filters and Search -->
            <div class="row mb-5">
                <div class="col-lg-12">
                    <div class="bg-light rounded-custom p-4">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Products</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" placeholder="Search products...">
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sort" class="form-label">Sort By</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name A-Z</option>
                                    <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                                    <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row products-container">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p class="text-muted">Try adjusting your search criteria or browse all products.</p>
                        <a href="products.php" class="btn btn-success">View All Products</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="product-card card h-100 position-relative">
                                <?php if ($product['featured']): ?>
                                    <div class="product-badge">Featured</div>
                                <?php endif; ?>
                                
                                <div class="position-relative">
                                    <img src="<?= !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/palm-oil-default.jpg' ?>" 
                                         class="product-image card-img-top" 
                                         alt="<?= htmlspecialchars($product['name']) ?>">
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <span class="badge bg-success-custom rounded-pill">
                                            <?= htmlspecialchars($product['category_name']) ?>
                                        </span>
                                    </div>
                                    
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text text-muted flex-grow-1">
                                        <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                                        <a href="product-detail.php?id=<?= $product['id'] ?>" 
                                           class="btn btn-outline-success">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Results Info -->
            <?php if (!empty($products)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="text-center text-muted">
                            <p>Showing <?= count($products) ?> product(s)
                                <?php if ($search): ?>
                                    for "<?= htmlspecialchars($search) ?>"
                                <?php endif; ?>
                                <?php if ($categoryFilter): ?>
                                    in <?= htmlspecialchars($categories[array_search($categoryFilter, array_column($categories, 'id'))]['name'] ?? 'Selected Category') ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-primary-custom text-white py-5">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h3 class="mb-3">Need Custom Solutions?</h3>
                    <p class="lead mb-4">We offer customized palm oil products tailored to your specific requirements. Contact our team for bulk orders and special formulations.</p>
                    <a href="contact.html" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us Today
                    </a>
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


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>