<?php
// app/Models/Category.php

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all categories for lists/dropdowns.
     */
    public function getAllCategories($orderBy = 'name', $orderDir = 'ASC')
    {
        // Basic validation
        $allowedOrderBy = ['id', 'name', 'slug', 'created_at'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'name';
        $orderDir = in_array(strtoupper($orderDir), ['ASC', 'DESC']) ? strtoupper($orderDir) : 'ASC';

        try {
            $this->db->query("SELECT id, name, slug, description, image_path, created_at FROM categories ORDER BY {$orderBy} {$orderDir}");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single category by its ID.
     */
    public function getCategoryById($id)
    {
        try {
            $this->db->query('SELECT * FROM categories WHERE id = :id');
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error fetching category by ID ($id): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a category name exists (optionally excluding an ID).
     */
    public function findCategoryByName($name, $excludeId = null)
    {
        $sql = 'SELECT id FROM categories WHERE name = :name';
        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
        }
        $sql .= ' LIMIT 1';

        try {
            $this->db->query($sql);
            $this->db->bind(':name', $name);
            if ($excludeId !== null) {
                $this->db->bind(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error finding category by name ($name): " . $e->getMessage());
            return false; // Safer to assume false on error
        }
    }

    /**
     * Check if a category slug exists (optionally excluding an ID).
     */
    public function findCategoryBySlug($slug, $excludeId = null)
    {
        $sql = 'SELECT id FROM categories WHERE slug = :slug';
        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
        }
        $sql .= ' LIMIT 1';
        try {
            $this->db->query($sql);
            $this->db->bind(':slug', $slug);
            if ($excludeId !== null) {
                $this->db->bind(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error finding category by slug ($slug): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new category.
     */
    public function createCategory($data)
    {
        // Generate slug
        $slug = $this->generateUniqueSlug($data['name']);
        if ($slug === false) return false; // Slug generation failed

        try {
            $this->db->query('INSERT INTO categories (name, slug, description, image_path)
                              VALUES (:name, :slug, :description, :image_path)');

            $this->db->bind(':name', $data['name']);
            $this->db->bind(':slug', $slug);
            $this->db->bind(':description', $data['description'] ?? null);
            $this->db->bind(':image_path', $data['image_path'] ?? null); // Filename from controller

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error creating category: " . $e->getMessage());
            error_log("Category Data: " . print_r($data, true));
            return false;
        }
    }

    /**
     * Update an existing category.
     */
    public function updateCategory($data)
    {
        // Generate slug, ensuring uniqueness excluding current ID
        $slug = $this->generateUniqueSlug($data['name'], $data['id']);
        if ($slug === false) return false;

        try {
            // Dynamically build query based on whether image is being updated
            $sql = 'UPDATE categories SET name = :name, slug = :slug, description = :description';
            if (isset($data['image_path'])) { // Only update image if a new path is provided
                $sql .= ', image_path = :image_path';
            }
            $sql .= ' WHERE id = :id';

            $this->db->query($sql);

            $this->db->bind(':name', $data['name']);
            $this->db->bind(':slug', $slug);
            $this->db->bind(':description', $data['description'] ?? null);
            $this->db->bind(':id', $data['id'], PDO::PARAM_INT);
            if (isset($data['image_path'])) {
                $this->db->bind(':image_path', $data['image_path']); // Bind new image path (can be null)
            }

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating category ID ({$data['id']}): " . $e->getMessage());
            error_log("Category Update Data: " . print_r($data, true));
            return false;
        }
    }

    /**
     * Delete a category by ID.
     * IMPORTANT: Check foreign key constraints on products table!
     * This basic version just deletes the category row.
     */
    public function deleteCategory($id)
    {
        $this->db->query('SELECT COUNT(*) as count FROM products WHERE category_id = :id');
        $this->db->bind(':id', $id);
        $productCount = $this->db->single()->count;
        if ($productCount > 0) {
            return false; /* Cannot delete */
        }

        try {
            $this->db->query('DELETE FROM categories WHERE id = :id');
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error deleting category ID ($id): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper to generate a unique slug.
     */
    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        if (empty($slug)) $slug = 'category'; // Fallback slug

        $originalSlug = $slug;
        $counter = 1;
        while ($this->findCategoryBySlug($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter++;
            if ($counter > 100) { // Safety break
                error_log("Could not generate unique slug for category name: " . $name);
                return false;
            }
        }
        return $slug;
    }

    public function getCategoryBySlug($slug)
    {
        try {
            $this->db->query('SELECT id, name, slug FROM categories WHERE slug = :slug LIMIT 1');
            $this->db->bind(':slug', $slug);
            return $this->db->single(); // Returns object or false
        } catch (Exception $e) {
            error_log("Error fetching category by slug ($slug): " . $e->getMessage());
            return false;
        }
    }
}
