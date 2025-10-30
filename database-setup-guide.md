# Database Setup Guide for Palm Oil Website

## Current Issue
MySQL connection is failing with the current credentials. Here are multiple solutions:

## Solution 1: Using XAMPP (Recommended - Easiest)

### Step 1: Install XAMPP
1. Download XAMPP from: https://www.apachefriends.org/download.html
2. Install XAMPP on your Mac
3. Open XAMPP Control Panel

### Step 2: Start Services
1. Click "Start" for Apache
2. Click "Start" for MySQL
3. Both should show "Running" status

### Step 3: Access phpMyAdmin
1. Go to: http://localhost/phpmyadmin
2. No password required for XAMPP's MySQL

### Step 4: Create Database
1. Click "New" in the left sidebar
2. Database name: `idfzgvte_palmicoil_db`
3. Click "Create"

### Step 5: Import Schema
1. Select your database `idfzgvte_palmicoil_db`
2. Click "Import" tab
3. Choose file: Browse to your project folder → database → schema.sql
4. Click "Go"

### Step 6: Update Config (if using XAMPP)
Update your `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'idfzgvte_palmicoil_db';
private $username = 'root';
private $password = '';  // Empty for XAMPP
```

## Solution 2: Fix Current MySQL Installation

### Option A: Reset MySQL Root Password
1. Stop MySQL service
2. Start MySQL in safe mode
3. Reset root password

### Option B: Create New MySQL User
```sql
-- Connect as root (if possible)
CREATE USER 'palmicoil_user'@'localhost' IDENTIFIED BY 'Dilshan@2002';
GRANT ALL PRIVILEGES ON *.* TO 'palmicoil_user'@'localhost';
FLUSH PRIVILEGES;
```

Then update `config/database.php`:
```php
private $username = 'palmicoil_user';
private $password = 'Dilshan@2002';
```

### Option C: Use MySQL Workbench
1. Download MySQL Workbench
2. Connect to your MySQL server
3. Create database and import schema visually

## Solution 3: Alternative Database Setup

### Using Sequel Pro (Mac)
1. Download Sequel Pro (free MySQL client)
2. Connect to your MySQL server
3. Create database and import schema

### Using TablePlus
1. Download TablePlus
2. Connect to MySQL
3. Create database and import schema

## Quick Test Commands

After setting up the database, test with:

```bash
# Test connection (replace with your actual credentials)
mysql -u root -p -e "SHOW DATABASES;"

# Test if your database exists
mysql -u root -p -e "SHOW DATABASES LIKE 'idfzgvte_palmicoil_db';"

# Test website connection
php test-server.php
```

## Verification Steps

1. **Database Created**: ✅ `idfzgvte_palmicoil_db` exists
2. **Tables Imported**: ✅ All tables from schema.sql are created
3. **Connection Works**: ✅ Website can connect to database
4. **Admin Access**: ✅ Can login to admin panel

## Next Steps After Database Setup

1. Visit: http://localhost:8000/test-server.php
2. Should show green checkmarks for all database connections
3. Visit: http://localhost:8000/admin/login.php
4. Login with: admin / admin123
5. Start managing your palm oil website!

## Need Help?

If you're still having issues:
1. Try XAMPP first (easiest solution)
2. Check MySQL service is running
3. Verify credentials in config/database.php
4. Test connection with phpMyAdmin or MySQL Workbench