<?php
/**
 * Database Configuration
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'palmicoil_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // Log error instead of echoing it
            error_log("Database connection error: " . $exception->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}

// Global database connection function
function getDB() {
    $database = new Database();
    return $database->getConnection();
}
?>