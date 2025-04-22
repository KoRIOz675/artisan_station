<?php

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllCategories()
    {
        try {
            $this->db->query('SELECT id, name FROM categories ORDER BY name ASC');
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }
}
