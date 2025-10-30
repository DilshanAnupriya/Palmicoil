-- Palm Oil Website Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS idfzgvte_palmicoil_db;
USE idfzgvte_palmicoil_db;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager') DEFAULT 'manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    slug VARCHAR(100) UNIQUE NOT NULL,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2),
    category_id INT,
    image VARCHAR(255),
    gallery TEXT, -- JSON array of image paths
    specifications TEXT, -- JSON object for product specs
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    meta_title VARCHAR(200),
    meta_description VARCHAR(300),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Pages table for CMS
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    page_type ENUM('about', 'contact', 'custom') DEFAULT 'custom',
    status ENUM('published', 'draft') DEFAULT 'draft',
    meta_title VARCHAR(200),
    meta_description VARCHAR(300),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table for site configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'image', 'boolean') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Slideshow images table
CREATE TABLE IF NOT EXISTS slideshow_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    link_url VARCHAR(255),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blogs table
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255),
    author VARCHAR(100) DEFAULT 'Admin',
    status ENUM('published', 'draft', 'archived') DEFAULT 'draft',
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    meta_title VARCHAR(200),
    meta_description VARCHAR(300),
    tags TEXT, -- JSON array of tags
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, email, password, role) VALUES 
('admin', 'admin@palmicoil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES 
('site_title', 'Palm Oil Company', 'text'),
('site_description', 'Premium quality palm oil products', 'textarea'),
('contact_email', 'info@palmicoil.com', 'text'),
('contact_phone', '+1-234-567-8900', 'text'),
('contact_address', '123 Palm Street, Oil City, PC 12345', 'textarea'),
('company_logo', '', 'image'),
('hero_title', 'Premium Palm Oil Products', 'text'),
('hero_subtitle', 'Sustainable and high-quality palm oil for all your needs', 'textarea');

-- Insert sample categories
INSERT INTO categories (name, description, slug) VALUES 
('Crude Palm Oil', 'Raw palm oil extracted from palm fruit', 'crude-palm-oil'),
('Refined Palm Oil', 'Processed and refined palm oil products', 'refined-palm-oil'),
('Palm Kernel Oil', 'Oil extracted from palm kernels', 'palm-kernel-oil'),
('Specialty Products', 'Specialized palm oil derivatives', 'specialty-products');

-- Insert sample slideshow images
INSERT INTO slideshow_images (title, description, image_path, sort_order, status) VALUES 
('Premium Palm Oil Production', 'State-of-the-art palm oil processing facilities', 'uploads/slideshow/slide1.jpg', 1, 'active'),
('Sustainable Farming', 'Environmentally responsible palm cultivation practices', 'uploads/slideshow/slide2.jpg', 2, 'active'),
('Quality Assurance', 'Rigorous testing and quality control processes', 'uploads/slideshow/slide3.jpg', 3, 'active'),
('Global Distribution', 'Worldwide supply chain and distribution network', 'uploads/slideshow/slide4.jpg', 4, 'active');

-- Insert sample featured products
INSERT INTO products (name, slug, description, short_description, price, category_id, image, featured, status) VALUES 
('Premium Crude Palm Oil', 'premium-crude-palm-oil', 'High-quality crude palm oil extracted from fresh palm fruits using state-of-the-art processing technology. Perfect for industrial and commercial applications with consistent quality and purity.', 'High-quality crude palm oil perfect for industrial and commercial applications.', 850.00, 1, 'uploads/products/crude-palm-oil.jpg', 1, 'active'),
('Refined Palm Oil', 'refined-palm-oil', 'Premium refined palm oil with golden clarity, processed through advanced refining techniques. Ideal for food industry, cosmetics, and high-end applications requiring superior quality and taste.', 'Premium refined palm oil with golden clarity for food and cosmetic industries.', 920.00, 2, 'uploads/products/refined-palm-oil.jpg', 1, 'active'),
('Palm Kernel Oil', 'palm-kernel-oil', 'Specialty palm kernel oil extracted from palm kernels with exceptional properties. Rich in lauric acid and perfect for premium applications in cosmetics, pharmaceuticals, and specialty food products.', 'Specialty palm kernel oil with exceptional properties for premium applications.', 1150.00, 3, 'uploads/products/palm-kernel-oil.jpg', 1, 'active'),
('Organic Palm Oil', 'organic-palm-oil', 'Certified organic palm oil produced through sustainable farming practices. 100% natural and environmentally friendly, perfect for organic food products and eco-conscious consumers.', 'Certified organic palm oil produced through sustainable farming practices.', 1050.00, 4, 'uploads/products/organic-palm-oil.jpg', 1, 'active'),
('Palm Oil Shortening', 'palm-oil-shortening', 'Premium palm oil shortening for bakery and confectionery applications. Provides excellent texture, stability, and shelf life for baked goods and pastries.', 'Premium palm oil shortening for bakery and confectionery applications.', 980.00, 4, 'uploads/products/palm-shortening.jpg', 1, 'active'),
('Palm Stearin', 'palm-stearin', 'High-quality palm stearin with excellent melting properties. Ideal for margarine production, cooking oils, and industrial applications requiring solid fat fractions.', 'High-quality palm stearin with excellent melting properties.', 890.00, 4, 'uploads/products/palm-stearin.jpg', 1, 'active');

-- Insert sample blog posts
INSERT INTO blogs (title, slug, excerpt, content, featured_image, author, status, featured, published_at, meta_title, meta_description, tags) VALUES 
('The Future of Sustainable Palm Oil Production', 'future-sustainable-palm-oil-production', 'Exploring innovative approaches to sustainable palm oil farming that protect the environment while meeting global demand.', 
'<h2>Introduction</h2><p>The palm oil industry stands at a critical juncture where sustainability and productivity must go hand in hand. As global demand continues to rise, we are committed to pioneering sustainable practices that protect our environment while delivering the highest quality products.</p>

<h2>Sustainable Farming Practices</h2><p>Our approach to sustainable palm oil production encompasses several key areas:</p>
<ul>
<li><strong>Zero Deforestation:</strong> We strictly adhere to no-deforestation policies, ensuring that new plantations are only established on previously cleared land.</li>
<li><strong>Biodiversity Conservation:</strong> Our plantations maintain wildlife corridors and protect critical habitats for endangered species.</li>
<li><strong>Water Management:</strong> Advanced irrigation systems minimize water usage while maintaining optimal growing conditions.</li>
<li><strong>Soil Health:</strong> Organic fertilizers and crop rotation practices maintain soil fertility for generations to come.</li>
</ul>

<h2>Technology and Innovation</h2><p>We leverage cutting-edge technology to optimize our operations:</p>
<ul>
<li>Precision agriculture using satellite monitoring and IoT sensors</li>
<li>Advanced processing equipment that maximizes yield while minimizing waste</li>
<li>Renewable energy systems powering our facilities</li>
<li>Blockchain technology for complete supply chain transparency</li>
</ul>

<h2>Community Impact</h2><p>Sustainable palm oil production extends beyond environmental considerations to include social responsibility. We work closely with local communities to provide fair employment opportunities, education programs, and healthcare services.</p>

<h2>Conclusion</h2><p>The future of palm oil lies in sustainable practices that benefit everyone - from farmers and communities to consumers and the environment. We are proud to lead this transformation and invite you to join us in creating a more sustainable future.</p>', 
'uploads/blogs/sustainable-palm-oil.jpg', 'Dr. Sarah Johnson', 'published', 1, NOW(), 'Sustainable Palm Oil Production - Future Trends', 'Learn about innovative sustainable palm oil farming practices that protect the environment while meeting global demand.', '["sustainability", "environment", "farming", "innovation"]'),

('Understanding Palm Oil Quality Standards', 'understanding-palm-oil-quality-standards', 'A comprehensive guide to palm oil quality standards and what makes our products stand out in the global market.', 
'<h2>Introduction to Palm Oil Quality</h2><p>Quality is the cornerstone of our palm oil production. Understanding the various quality standards and certifications helps our customers make informed decisions and ensures they receive the best products for their specific needs.</p>

<h2>International Quality Standards</h2><p>Our products meet and exceed international quality standards including:</p>
<ul>
<li><strong>RSPO Certification:</strong> Roundtable on Sustainable Palm Oil certification ensures sustainable production practices.</li>
<li><strong>ISO 9001:</strong> International quality management system certification.</li>
<li><strong>HACCP:</strong> Hazard Analysis and Critical Control Points for food safety.</li>
<li><strong>Halal Certification:</strong> Meeting religious dietary requirements.</li>
</ul>

<h2>Quality Testing Process</h2><p>Every batch of our palm oil undergoes rigorous testing:</p>
<ol>
<li><strong>Raw Material Inspection:</strong> Fresh fruit bunches are carefully selected and inspected.</li>
<li><strong>Processing Monitoring:</strong> Continuous monitoring throughout the extraction and refining process.</li>
<li><strong>Laboratory Analysis:</strong> Comprehensive chemical and physical property testing.</li>
<li><strong>Final Quality Assurance:</strong> Final inspection before packaging and shipment.</li>
</ol>

<h2>Key Quality Parameters</h2><p>We monitor several critical quality parameters:</p>
<ul>
<li>Free Fatty Acid (FFA) content</li>
<li>Moisture and impurities levels</li>
<li>Peroxide value</li>
<li>Color and clarity</li>
<li>Melting point and consistency</li>
</ul>

<h2>Our Commitment to Excellence</h2><p>Quality is not just about meeting standards - it is about exceeding expectations. Our dedicated quality assurance team works tirelessly to ensure that every product that leaves our facility represents the highest standards of excellence.</p>', 
'uploads/blogs/quality-standards.jpg', 'Michael Chen', 'published', 1, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Palm Oil Quality Standards Guide', 'Comprehensive guide to palm oil quality standards and certifications. Learn what makes premium palm oil products.', '["quality", "standards", "certification", "testing"]'),

('The Health Benefits of Palm Oil', 'health-benefits-palm-oil', 'Discover the nutritional advantages and health benefits of incorporating high-quality palm oil into your diet and products.', 
'<h2>Nutritional Profile of Palm Oil</h2><p>Palm oil is rich in essential nutrients that provide numerous health benefits. Understanding its nutritional composition helps consumers make informed choices about incorporating it into their diet.</p>

<h2>Key Nutritional Components</h2><p>Palm oil contains several important nutrients:</p>
<ul>
<li><strong>Vitamin E (Tocotrienols and Tocopherols):</strong> Powerful antioxidants that protect cells from oxidative stress.</li>
<li><strong>Beta-Carotene:</strong> A precursor to Vitamin A, giving palm oil its characteristic golden color.</li>
<li><strong>Saturated and Unsaturated Fats:</strong> Balanced fatty acid profile for optimal health.</li>
<li><strong>Coenzyme Q10:</strong> Supports cellular energy production.</li>
</ul>

<h2>Health Benefits</h2><p>Research has shown several potential health benefits of palm oil:</p>
<ol>
<li><strong>Heart Health:</strong> The balanced fatty acid profile may support cardiovascular health when used as part of a balanced diet.</li>
<li><strong>Antioxidant Properties:</strong> High levels of vitamin E help combat free radicals and reduce oxidative stress.</li>
<li><strong>Brain Function:</strong> Tocotrienols may support cognitive function and brain health.</li>
<li><strong>Skin Health:</strong> Vitamin E and beta-carotene contribute to healthy skin when used topically or consumed.</li>
</ol>

<h2>Culinary Applications</h2><p>Palm oil is versatile in cooking applications:</p>
<ul>
<li>High smoke point makes it ideal for frying and high-temperature cooking</li>
<li>Neutral flavor that does not overpower other ingredients</li>
<li>Excellent stability and shelf life</li>
<li>Trans-fat free alternative to partially hydrogenated oils</li>
</ul>

<h2>Choosing Quality Palm Oil</h2><p>To maximize health benefits, choose high-quality, sustainably produced palm oil from reputable sources. Look for certifications and quality standards that ensure purity and nutritional integrity.</p>', 
'uploads/blogs/health-benefits.jpg', 'Dr. Maria Rodriguez', 'published', 0, DATE_SUB(NOW(), INTERVAL 10 DAY), 'Health Benefits of Palm Oil - Nutrition Guide', 'Discover the nutritional advantages and health benefits of high-quality palm oil for diet and wellness.', '["health", "nutrition", "benefits", "wellness"]'),

('Palm Oil in the Global Market: Trends and Opportunities', 'palm-oil-global-market-trends', 'Analyzing current market trends, opportunities, and the future outlook for palm oil in the global commodity market.', 
'<h2>Global Palm Oil Market Overview</h2><p>The global palm oil market continues to evolve, driven by increasing demand from various industries and changing consumer preferences. Understanding these trends is crucial for stakeholders across the supply chain.</p>

<h2>Current Market Trends</h2><p>Several key trends are shaping the palm oil market:</p>
<ul>
<li><strong>Sustainability Focus:</strong> Increasing demand for certified sustainable palm oil (CSPO)</li>
<li><strong>Food Industry Growth:</strong> Rising demand from food processing and restaurant industries</li>
<li><strong>Biofuel Applications:</strong> Growing use in renewable energy production</li>
<li><strong>Emerging Markets:</strong> Expanding consumption in developing countries</li>
</ul>

<h2>Regional Market Analysis</h2><p>Different regions show varying consumption patterns:</p>
<ol>
<li><strong>Asia-Pacific:</strong> Largest consumer region, driven by population growth and economic development</li>
<li><strong>Europe:</strong> Focus on sustainable sourcing and certification requirements</li>
<li><strong>North America:</strong> Growing awareness and acceptance of palm oil benefits</li>
<li><strong>Africa:</strong> Emerging market with significant growth potential</li>
</ol>

<h2>Industry Applications</h2><p>Palm oil serves diverse industries:</p>
<ul>
<li>Food and beverage manufacturing</li>
<li>Personal care and cosmetics</li>
<li>Pharmaceutical products</li>
<li>Industrial applications</li>
<li>Biofuel and renewable energy</li>
</ul>

<h2>Future Opportunities</h2><p>The palm oil industry presents several growth opportunities:</p>
<ul>
<li>Development of specialty and high-value products</li>
<li>Expansion into new geographical markets</li>
<li>Innovation in sustainable production methods</li>
<li>Integration of digital technologies in supply chain management</li>
</ul>

<h2>Challenges and Solutions</h2><p>While opportunities abound, the industry faces challenges including environmental concerns, price volatility, and regulatory changes. Success requires adaptation, innovation, and commitment to sustainable practices.</p>', 
'uploads/blogs/global-market.jpg', 'James Thompson', 'published', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Palm Oil Global Market Trends and Opportunities', 'Analysis of current palm oil market trends, opportunities, and future outlook in the global commodity market.', '["market", "trends", "global", "opportunities"]'),

('Innovation in Palm Oil Processing Technology', 'innovation-palm-oil-processing-technology', 'Exploring cutting-edge technologies and innovations that are revolutionizing palm oil processing and production efficiency.', 
'<h2>The Evolution of Palm Oil Processing</h2><p>Palm oil processing has undergone significant technological advancement over the past decades. Modern innovations are making production more efficient, sustainable, and cost-effective while maintaining the highest quality standards.</p>

<h2>Advanced Extraction Technologies</h2><p>New extraction methods are improving yield and quality:</p>
<ul>
<li><strong>Enzymatic Processing:</strong> Using enzymes to increase oil extraction rates and improve quality</li>
<li><strong>Supercritical CO2 Extraction:</strong> Solvent-free extraction for premium products</li>
<li><strong>Membrane Technology:</strong> Advanced filtration systems for purer oil</li>
<li><strong>Continuous Processing:</strong> Automated systems that reduce processing time and labor costs</li>
</ul>

<h2>Digital Transformation</h2><p>Technology is revolutionizing palm oil operations:</p>
<ol>
<li><strong>IoT Sensors:</strong> Real-time monitoring of processing parameters</li>
<li><strong>AI and Machine Learning:</strong> Predictive maintenance and quality optimization</li>
<li><strong>Blockchain:</strong> Supply chain transparency and traceability</li>
<li><strong>Automation:</strong> Robotic systems for handling and packaging</li>
</ol>

<h2>Environmental Technologies</h2><p>Sustainable processing innovations include:</p>
<ul>
<li>Waste-to-energy systems utilizing palm oil mill effluent (POME)</li>
<li>Zero-waste processing technologies</li>
<li>Water recycling and treatment systems</li>
<li>Carbon capture and utilization technologies</li>
</ul>

<h2>Quality Enhancement Technologies</h2><p>Advanced quality control systems ensure superior products:</p>
<ul>
<li>Near-infrared spectroscopy for rapid quality analysis</li>
<li>Automated sampling and testing systems</li>
<li>Real-time quality monitoring throughout processing</li>
<li>Advanced refining technologies for specialty products</li>
</ul>

<h2>Future Innovations</h2><p>Emerging technologies promise even greater improvements:</p>
<ul>
<li>Nanotechnology applications in processing and packaging</li>
<li>Biotechnology for enhanced oil properties</li>
<li>Advanced materials for processing equipment</li>
<li>Integration of renewable energy systems</li>
</ul>

<h2>Our Commitment to Innovation</h2><p>We continuously invest in research and development to stay at the forefront of processing technology. Our state-of-the-art facilities incorporate the latest innovations to deliver superior products while minimizing environmental impact.</p>', 
'uploads/blogs/processing-technology.jpg', 'Dr. Robert Kim', 'draft', 0, NULL, 'Palm Oil Processing Technology Innovations', 'Explore cutting-edge technologies revolutionizing palm oil processing and production efficiency.', '["technology", "innovation", "processing", "efficiency"]');

-- Our Strengths table
CREATE TABLE IF NOT EXISTS our_strengths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(100), -- Font Awesome icon class
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Certifications & Awards table
CREATE TABLE IF NOT EXISTS certifications_awards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    type ENUM('certification', 'award') NOT NULL,
    issuing_organization VARCHAR(200),
    date_received DATE,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- FAQs table
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'general',
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data for Our Strengths
INSERT INTO our_strengths (title, description, icon, sort_order, status) VALUES
('We derive our strength', 'We derive our strength and inspiration from our customers. Understanding their needs leads us to find the right product for them. This motivates us to work hard in order to create the best product possible.', 'fas fa-users', 1, 'active'),
('We offer personalized', 'We offer personalized, one-on-one services every step of the way for maximum customer satisfaction.', 'fas fa-handshake', 2, 'active'),
('We understand the market', 'We understand the cooking oil market risk well, and we convey this with the utmost clarity to our customers.', 'fas fa-chart-line', 3, 'active'),
('Long-term Business', 'We believe in establishing long-term business relationships with our customers.', 'fas fa-clock', 4, 'active');

-- Insert sample data for Certifications & Awards
INSERT INTO certifications_awards (title, description, image, type, issuing_organization, date_received, sort_order, status) VALUES
('Good Manufacturing Practice (GMP) / HACCP', 'Certified for maintaining high standards in food safety and quality management systems.', 'uploads/certifications/gmp-haccp.png', 'certification', 'Food Safety Authority', '2023-01-15', 1, 'active'),
('MS (Malaysian Standard)', 'Compliance with Malaysian standards for palm oil production and processing.', 'uploads/certifications/ms-standard.png', 'certification', 'Standards Malaysia', '2023-03-20', 2, 'active'),
('ISO', 'International Organization for Standardization certification for quality management.', 'uploads/certifications/iso.png', 'certification', 'ISO International', '2023-05-10', 3, 'active'),
('Mpob & Mbsa Registered Company', 'Registered with Malaysian Palm Oil Board and Majlis Bandaraya Shah Alam.', 'uploads/certifications/mpob-mbsa.png', 'certification', 'MPOB & MBSA', '2023-02-28', 4, 'active');

-- Insert sample data for FAQs
INSERT INTO faqs (question, answer, category, sort_order, status) VALUES
('What is RBD palm oil olein?', 'RBD palm oil olein, also known as refined, bleached, and deodorized palm oil olein, is a type of palm oil (minyak sawit) that undergoes a refining process to remove impurities, color, and odor. This process involves bleaching and deodorizing the palm oil to produce a clear, neutral-flavored liquid. RBD palm oil olein is commonly used in cooking and food processing due to its high stability and resistance to oxidation, making it suitable for frying and as an ingredient in various food products.', 'cooking_oil', 1, 'active'),
('What key factors should you consider when choosing a cooking oil supplier?', 'When selecting a cooking oil supplier, consider factors such as product quality and certifications, supply chain reliability, pricing and payment terms, storage and logistics capabilities, customer service and support, sustainability practices, and compliance with food safety regulations. Look for suppliers with proper certifications like HACCP, ISO, and local food safety standards.', 'supplier', 2, 'active'),
('What exactly is vegetable oil, and how is it commonly used?', 'Vegetable oil is a broad term for oils extracted from various plant sources including seeds, nuts, and fruits. Common types include palm oil, soybean oil, sunflower oil, and canola oil. These oils are widely used for cooking, frying, baking, and food processing due to their neutral flavors and high smoke points. They are also used in non-food applications such as cosmetics and industrial processes.', 'cooking_oil', 3, 'active'),
('How do palm oil, palm olein, and palm stearin differ in properties and uses?', 'Palm oil is the crude oil extracted from palm fruit. Palm olein is the liquid fraction obtained after fractionation, with a lower melting point, making it ideal for cooking and frying. Palm stearin is the solid fraction with a higher melting point, commonly used in margarine, shortening, and confectionery products. Each fraction has specific applications based on their melting points and consistency at room temperature.', 'cooking_oil', 4, 'active');