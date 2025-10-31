<?php
require_once 'config/database.php';
require_once 'config/config.php';

$db = getDB();

// Get product ID
$productId = $_GET['id'] ?? 0;

if (!$productId) {
    header('Location: products.php');
    exit;
}

// Get product details
$query = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = :id AND p.status = 'active'
";

$stmt = $db->prepare($query);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get related products
$relatedQuery = "
    SELECT * FROM products 
    WHERE category_id = :category_id AND id != :id AND status = 'active' 
    ORDER BY RAND() 
    LIMIT 3
";

$relatedStmt = $db->prepare($relatedQuery);
$relatedStmt->execute([
    'category_id' => $product['category_id'],
    'id' => $productId
]);
$relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Palm Oil Company</title>
    <meta name="description" content="<?= htmlspecialchars(substr($product['description'], 0, 160)) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($product['meta_keywords']) ?>">
    
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
                        <a class="nav-link" href="products.php">Products</a>
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

    <!-- Breadcrumb -->
    <section class="bg-light py-3" style="margin-top: 76px;">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <li class="breadcrumb-item">
                        <a href="products.php?category=<?= $product['category_id'] ?>">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Product Detail -->
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6 mb-4">
                    <div class="product-images">
                        <img src="<?= !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/palm-oil-default.jpg' ?>" 
                             class="img-fluid product-detail-image product-main-image w-100" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        
                        <!-- Thumbnail Gallery (if multiple images were available) -->
                        <div class="product-gallery mt-3">
                            <img src="<?= !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/palm-oil-default.jpg' ?>" 
                                 class="gallery-thumb active" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                            <!-- Additional thumbnails would go here -->
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <?php if ($product['featured']): ?>
                            <span class="badge bg-success mb-3">Featured Product</span>
                        <?php endif; ?>
                        
                        <h1 class="h2 mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                        
                        <div class="mb-3">
                            <span class="badge bg-light text-dark">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </span>
                        </div>
                        
                        <div class="product-price mb-4">
                            <span class="h3 text-success fw-bold">$<?= number_format($product['price'], 2) ?></span>
                            <span class="text-muted ms-2">per unit</span>
                        </div>
                        
                        <div class="product-description mb-4">
                            <h5>Description</h5>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>

                        <!-- Product Specifications -->
                        <div class="product-specs mb-4">
                            <h5>Specifications</h5>
                            <div class="spec-item">
                                <span class="fw-medium">SKU:</span>
                                <span><?= htmlspecialchars($product['sku']) ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="fw-medium">Category:</span>
                                <span><?= htmlspecialchars($product['category_name']) ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="fw-medium">Status:</span>
                                <span class="badge bg-success">In Stock</span>
                            </div>
                        </div>

                        <!-- Quantity and Add to Cart -->
                        <div class="product-actions">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Quantity</label>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary qty-btn qty-minus" type="button">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center qty-input" value="1" min="1">
                                        <button class="btn btn-outline-secondary qty-btn qty-plus" type="button">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-success btn-lg flex-fill">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                        </button>
                                        <button class="btn btn-outline-success btn-lg">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact for Bulk Orders -->
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Bulk Orders:</strong> Contact us for special pricing on large quantities.
                            <a href="contact.html" class="alert-link">Get Quote</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Details Tabs -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                    data-bs-target="#description" type="button" role="tab">
                                Description
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" 
                                    data-bs-target="#specifications" type="button" role="tab">
                                Specifications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" 
                                    data-bs-target="#shipping" type="button" role="tab">
                                Shipping & Returns
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content bg-white p-4 rounded-bottom shadow-sm" id="productTabsContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel">
                            <h5>Product Description</h5>
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                            
                            <h6 class="mt-4">Key Features:</h6>
                            <ul>
                                <li>Premium quality palm oil</li>
                                <li>Sustainably sourced</li>
                                <li>Rigorous quality testing</li>
                                <li>Multiple packaging options available</li>
                                <li>Suitable for various applications</li>
                            </ul>
                        </div>
                        
                        <div class="tab-pane fade" id="specifications" role="tabpanel">
                            <h5>Technical Specifications</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-medium">Product Code:</td>
                                            <td><?= htmlspecialchars($product['sku']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Category:</td>
                                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Origin:</td>
                                            <td>Sustainable Plantations</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Processing:</td>
                                            <td>Cold Pressed</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-medium">Shelf Life:</td>
                                            <td>24 months</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Storage:</td>
                                            <td>Cool, dry place</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Certification:</td>
                                            <td>RSPO Certified</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Packaging:</td>
                                            <td>Various sizes available</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="shipping" role="tabpanel">
                            <h5>Shipping Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Shipping Options:</h6>
                                    <ul>
                                        <li>Standard Shipping (5-7 business days)</li>
                                        <li>Express Shipping (2-3 business days)</li>
                                        <li>Overnight Shipping (next business day)</li>
                                        <li>Bulk/Freight Shipping for large orders</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Return Policy:</h6>
                                    <ul>
                                        <li>30-day return policy</li>
                                        <li>Items must be unopened and unused</li>
                                        <li>Original packaging required</li>
                                        <li>Return shipping costs may apply</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3>Related Products</h3>
                    <p class="text-muted">You might also be interested in these products</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="product-card card h-100">
                            <img src="<?= !empty($relatedProduct['image']) ? 'uploads/products/' . $relatedProduct['image'] : 'assets/images/palm-oil-default.jpg' ?>" 
                                 class="product-image card-img-top" 
                                 alt="<?= htmlspecialchars($relatedProduct['name']) ?>">
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($relatedProduct['name']) ?></h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?= htmlspecialchars(substr($relatedProduct['description'], 0, 100)) ?>...
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div class="product-price">$<?= number_format($relatedProduct['price'], 2) ?></div>
                                    <a href="product-detail.php?id=<?= $relatedProduct['id'] ?>" 
                                       class="btn btn-outline-success">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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