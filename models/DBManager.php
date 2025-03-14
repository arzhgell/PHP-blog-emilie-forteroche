<?php

/**
 * Database connection manager (singleton)
 */
class DBManager 
{
    // Singleton instance
    private static $instance;

    private $db;

    /**
     * Private constructor - use getInstance() instead
     */
    private function __construct() 
    {
        // Connect to database
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Returns singleton instance
     */
    public static function getInstance() : DBManager
    {
        if (!self::$instance) {
            self::$instance = new DBManager();
        }
        return self::$instance;
    }

    /**
     * Returns PDO connection
     */
    public function getPDO() : PDO
    {
        return $this->db;
    }

    /**
     * Executes SQL query with optional parameters
     */
    public function query(string $sql, ?array $params = null) : PDOStatement
    {
        if ($params == null) {
            $query = $this->db->query($sql);
        } else {
            $query = $this->db->prepare($sql);
            $query->execute($params);
        }
        return $query;
    }
    
}