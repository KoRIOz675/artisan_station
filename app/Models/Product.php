<?php

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Get product by ID
    public function getProductById($id)
    {
        $this->db->query('SELECT p.*, u.name as artisan_name, u.shop_name
                          FROM products p
                          JOIN users u ON p.artisan_id = u.id
                          WHERE p.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get total number of products
    public function getTotalProductCount()
    {
        try {
            $this->db->query('SELECT COUNT(*) as count FROM products');
            $row = $this->db->single();
            return ($row && isset($row->count)) ? $row->count : 0;
        } catch (Exception $e) {
            error_log("Error getting product count: " . $e->getMessage());
            return 'Error';
        }
    }

    // Get all products relevant for featured management
    public function getAllProductsForFeatured()
    {
        // Select minimal data needed for the feature toggle list
        $this->db->query('SELECT p.id, p.name, p.is_active, p.is_featured, u.username as artisan_username
                          FROM products p
                          LEFT JOIN users u ON p.artisan_id = u.id
                          ORDER BY p.name ASC');
        return $this->db->resultSet();
    }

    // Update the featured status of a product
    public function updateProductFeaturedStatus($productId, $status)
    {
        $this->db->query('UPDATE products SET is_featured = :status WHERE id = :id');
        $this->db->bind(':status', $status ? 1 : 0, PDO::PARAM_INT); // Ensure 0 or 1
        $this->db->bind(':id', $productId, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function getActiveProductsByArtisanId($artisanId, $limit = null)
    {
        $sql = "SELECT id, name, slug, image_path, price
                FROM products
                WHERE artisan_id = :artisan_id AND is_active = 1
                ORDER BY created_at DESC"; // Or order by name, etc.

        if ($limit !== null && is_int($limit) && $limit > 0) {
            $sql .= " LIMIT :limit";
        }

        $this->db->query($sql);
        $this->db->bind(':artisan_id', $artisanId, PDO::PARAM_INT);

        if ($limit !== null && is_int($limit) && $limit > 0) {
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        }

        return $this->db->resultSet();
    }

    public function createProduct($data)
    {
        // Basic slug generation (consider a library for robustness)
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name']), '-'));
        $originalSlug = $slug;
        $counter = 1;
        while ($this->findProductBySlug($slug)) { // Use helper to ensure unique slug
            $slug = $originalSlug . '-' . $counter++;
        }

        try {
            // Query matches your schema columns: stock_quantity, is_active, is_featured
            $this->db->query('INSERT INTO products (artisan_id, category_id, name, slug, description, price, image_path, stock_quantity, is_active, is_featured)
                              VALUES (:artisan_id, :category_id, :name, :slug, :description, :price, :image_path, :stock_quantity, :is_active, :is_featured)');

            $this->db->bind(':artisan_id', $data['artisan_id'], PDO::PARAM_INT);
            $this->db->bind(':category_id', $data['category_id'], PDO::PARAM_INT);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':slug', $slug); // Use generated slug
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':price', $data['price']); // Assumes price is correctly formatted number
            $this->db->bind(':image_path', $data['image_path']); // Filename from controller
            $this->db->bind(':stock_quantity', $data['stock_quantity'] ?? 0, PDO::PARAM_INT);
            $this->db->bind(':is_active', $data['is_active'] ?? 1, PDO::PARAM_INT); // Default to active
            $this->db->bind(':is_featured', $data['is_featured'] ?? 0, PDO::PARAM_INT); // Default to not featured

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error creating product: " . $e->getMessage());
            error_log("Product Data: " . print_r($data, true));
            return false;
        }
    }

    public function findProductBySlug($slug)
    {
        try {
            $this->db->query('SELECT id FROM products WHERE slug = :slug LIMIT 1');
            $this->db->bind(':slug', $slug);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error finding product by slug ($slug): " . $e->getMessage());
            return false;
        }
    }
}
