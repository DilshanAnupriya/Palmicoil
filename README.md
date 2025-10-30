# Palm Oil Website

A comprehensive palm oil business website built with HTML, CSS, JavaScript, and PHP. This website includes a complete content management system, product management, and admin features.

## Features

### Frontend Features
- **Homepage**: Hero section, featured products, company information
- **Products Page**: Product catalog with filtering, search, and sorting
- **Product Detail Page**: Detailed product information with image gallery
- **Contact Page**: Contact form, business information, and FAQ
- **About Page**: Company history, values, and team information
- **Responsive Design**: Mobile-friendly layout using Bootstrap
- **Interactive Elements**: JavaScript animations and form validation

### Backend Features
- **Admin Authentication**: Secure login system for administrators
- **Product Management**: Add, edit, delete products with image uploads
- **Category Management**: Organize products into categories
- **Content Management**: Manage website pages and content
- **Contact Messages**: View and manage contact form submissions
- **Settings Management**: Configure website settings and admin accounts
- **Database Integration**: MySQL database with proper relationships

## Project Structure

```
palm-oil-website/
├── admin/                  # Admin panel files
│   ├── login.php          # Admin login page
│   ├── dashboard.php      # Admin dashboard
│   ├── products.php       # Product management
│   ├── product-form.php   # Add/edit product form
│   ├── categories.php     # Category management
│   ├── pages.php          # Page management
│   ├── messages.php       # Contact messages
│   ├── settings.php       # Website settings
│   └── logout.php         # Logout handler
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── main.js        # Main JavaScript file
├── config/                # Configuration files
│   ├── database.php       # Database connection
│   └── config.php         # Application configuration
├── database/              # Database files
│   └── schema.sql         # Database schema
├── uploads/               # Upload directory (create manually)
├── index.html             # Homepage
├── products.php           # Products listing page
├── product-detail.php     # Product detail page
├── contact.html           # Contact page
├── about.html             # About page
├── contact-handler.php    # Contact form processor
└── test-server.php        # Server test file
```

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Installation Steps

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd palm-oil-website
   ```

2. **Create Database**
   - Create a new MySQL database
   - Import the database schema:
   ```bash
   mysql -u username -p database_name < database/schema.sql
   ```

3. **Configure Database Connection**
   - Edit `config/database.php`
   - Update the database credentials:
   ```php
   private $host = 'localhost';
   private $db_name = 'your_database_name';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

4. **Create Upload Directory**
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

5. **Configure Application Settings**
   - Edit `config/config.php`
   - Update the site URL and paths according to your setup

6. **Start the Server**
   
   **Option A: PHP Built-in Server (Development)**
   ```bash
   php -S localhost:8000
   ```
   
   **Option B: Apache/Nginx (Production)**
   - Configure your web server to point to the project directory
   - Ensure mod_rewrite is enabled for Apache

### Default Admin Credentials
- **Username**: admin
- **Password**: admin123

**Important**: Change the default admin password after first login!

## Usage

### Frontend
- Visit the homepage to see the website
- Browse products, view details, and use the contact form
- All pages are responsive and work on mobile devices

### Admin Panel
1. Access the admin panel at `/admin/login.php`
2. Login with the default credentials
3. Use the dashboard to manage:
   - Products and categories
   - Website pages and content
   - Contact messages
   - Website settings

### Adding Products
1. Login to admin panel
2. Go to "Products" section
3. Click "Add New Product"
4. Fill in product details and upload images
5. Save the product

### Managing Content
1. Use the "Pages" section to manage website content
2. Edit existing pages or create new ones
3. Use the built-in editor for content formatting

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Font Awesome
- **Backend**: PHP 7.4+, MySQL
- **Libraries**: 
  - Bootstrap 5.3.0
  - Font Awesome 6.0.0
  - TinyMCE (for content editing)
  - jQuery 3.6.0

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Security Features

- SQL injection prevention using prepared statements
- XSS protection with input sanitization
- CSRF protection for forms
- Secure file upload handling
- Session management
- Password hashing

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support or questions, please contact the development team or create an issue in the repository.