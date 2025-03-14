<?php

/**
 * Base manager class with database access
 */
abstract class AbstractEntityManager {
    
    protected $db;

    /**
     * Constructor initializes database connection
     */
    public function __construct() 
    {
        $this->db = DBManager::getInstance();
    }
}