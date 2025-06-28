<?php
require_once 'config.php';

class Database {
    // Paramètres XAMPP par défaut
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->port = "3306";
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->conn;
        } catch(PDOException $exception) {
            throw new PDOException("Erreur de connexion à la base de données: " . $exception->getMessage());
        }
    }

    // Méthode pour tester la connexion
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return ['success' => true, 'message' => 'Connexion réussie à la base de données'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
