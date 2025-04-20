<?php

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Get all products (consider pagination for real app)
    public function getProducts()
    {
        // Join with users table to get artisan name
        $this->db->query('SELECT p.*, u.name as artisan_name, u.shop_name
                          FROM products p
                          JOIN users u ON p.artisan_id = u.id
                          WHERE u.role = "artisan"
                          ORDER BY p.created_at DESC');
        return $this->db->resultSet();
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

    // Get products by Artisan ID
    public function getProductsByArtisan($artisan_id)
    {
        $this->db->query('SELECT * FROM products WHERE artisan_id = :artisan_id ORDER BY created_at DESC');
        $this->db->bind(':artisan_id', $artisan_id);
        return $this->db->resultSet();
    }

    // Add a new product
    public function addProduct($data)
    {
        // Add validation and ensure user is an artisan and owns this product
        $this->db->query('INSERT INTO products (artisan_id, name, description, price, materials, dimensions, production_time, image_path, category_id)
                          VALUES (:artisan_id, :name, :description, :price, :materials, :dimensions, :production_time, :image_path, :category_id)');
        $this->db->bind(':artisan_id', $data['artisan_id']); // Should come from logged-in user session
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':materials', $data['materials'] ?? null);
        $this->db->bind(':dimensions', $data['dimensions'] ?? null);
        $this->db->bind(':production_time', $data['production_time'] ?? null);
        $this->db->bind(':image_path', $data['image_path'] ?? 'default_product.jpg'); // Handle image uploads properly
        $this->db->bind(':category_id', $data['category_id'] ?? null);

        return $this->db->execute();
    }

    // Update a product
    public function updateProduct($id, $data)
    {
        // Add validation and authorization (is user the owner?)
        $this->db->query('UPDATE products SET
                            name = :name,
                            description = :description,
                            price = :price,
                            materials = :materials,
                            dimensions = :dimensions,
                            production_time = :production_time,
                            image_path = :image_path,
                            category_id = :category_id
                          WHERE id = :id AND artisan_id = :artisan_id'); // Ensure artisan owns the product

        $this->db->bind(':id', $id);
        $this->db->bind(':artisan_id', $data['artisan_id']); // From session
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':materials', $data['materials'] ?? null);
        $this->db->bind(':dimensions', $data['dimensions'] ?? null);
        $this->db->bind(':production_time', $data['production_time'] ?? null);
        $this->db->bind(':image_path', $data['image_path'] ?? 'default_product.jpg');
        $this->db->bind(':category_id', $data['category_id'] ?? null);

        return $this->db->execute();
    }

    // Delete a product
    public function deleteProduct($id, $artisan_id)
    {
        // Add validation and authorization
        $this->db->query('DELETE FROM products WHERE id = :id AND artisan_id = :artisan_id');
        $this->db->bind(':id', $id);
        $this->db->bind(':artisan_id', $artisan_id); // From session

        return $this->db->execute();
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
}
