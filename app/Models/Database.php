<?php
class Database
{
    private static $instance = null;
    private $pdo;
    private $stmt;
    private $error;

    private function __construct()
    {
        // Ensure config constants are defined before attempting connection
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_CHARSET')) {
            error_log('ERROR: Database configuration constants are not defined.');
            die('Database configuration is incomplete. Please check config files.');
        }

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Fetch objects by default
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            // Directly use PDO as it's global
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) { // Directly use PDOException
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            die("Database Connection Error. Please check logs or contact support.");
        }
    }

    // Get singleton instance
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {}

    // Prepare statement with query
    public function query($sql)
    {
        $this->stmt = null;
        $this->error = null;
        try {
            $this->stmt = $this->pdo->prepare($sql);
        } catch (PDOException $e) { // Directly use PDOException
            $this->error = $e->getMessage();
            error_log("Database Query Prepare Error: " . $this->error . " | SQL: " . $sql);
        }
    }

    // Bind values
    public function bind($param, $value, $type = null)
    {
        if ($this->stmt === null) {
            error_log("Attempted to bind on a non-prepared statement.");
            return;
        }
        if (is_null($type)) {
            switch (true) {
                // Use global PDO constants directly
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        try {
            $this->stmt->bindValue($param, $value, $type);
        } catch (PDOException $e) { // Directly use PDOException
            $this->error = $e->getMessage();
            error_log("Database Bind Error: " . $this->error . " | Param: " . $param);
        }
    }

    // Execute the prepared statement
    public function execute()
    {
        if ($this->stmt === null) {
            $this->error = "Statement not prepared or prepare failed.";
            error_log($this->error);
            return false;
        }
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) { // Directly use PDOException
            $this->error = $e->getMessage();
            error_log("Database Query Execute Error: " . $this->error);
            return false;
        }
    }

    // Get result set as array of objects
    public function resultSet()
    {
        if ($this->stmt === null) return [];
        if (!$this->execute()) {
            return [];
        }
        // Use global PDO constant directly
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get single record as object
    public function single()
    {
        if ($this->stmt === null) return false;
        if (!$this->execute()) {
            return false;
        }
        // Use global PDO constant directly
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Get row count
    public function rowCount()
    {
        if ($this->stmt === null) return 0;
        return $this->stmt->rowCount();
    }

    // Get last inserted ID
    public function lastInsertId()
    {
        if ($this->pdo === null) return false;
        try {
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) { // Directly use PDOException
            $this->error = $e->getMessage();
            error_log("Database Last Insert ID Error: " . $this->error);
            return false;
        }
    }

    // Get last error message
    public function getError()
    {
        return $this->pdo->errorInfo()[2] ?? null;
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
