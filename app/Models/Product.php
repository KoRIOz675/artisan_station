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

    public function getFilteredActiveProducts(array $filters = [])
    {
        // Base query
        $sql = "SELECT
                    p.id, p.name, p.slug, p.description, p.price, p.image_path, p.created_at,
                    c.name as category_name, c.slug as category_slug,
                    u.username as artisan_username, u.shop_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.artisan_id = u.id
                WHERE p.is_active = 1"; // Start with active products

        $bindings = [];

        // --- Apply Category Filter ---
        if (!empty($filters['category_id']) && filter_var($filters['category_id'], FILTER_VALIDATE_INT)) {
            $sql .= " AND p.category_id = :category_id";
            $bindings[':category_id'] = (int)$filters['category_id'];
        }

        // --- Apply Price Filter ---
        // Min Price
        if (!empty($filters['min_price']) && is_numeric($filters['min_price']) && $filters['min_price'] >= 0) {
            $sql .= " AND p.price >= :min_price";
            $bindings[':min_price'] = $filters['min_price']; // Let PDO handle type
        }
        // Max Price
        if (!empty($filters['max_price']) && is_numeric($filters['max_price']) && $filters['max_price'] >= 0) {
            // Ensure max is not less than min if both provided (optional)
            if (!isset($bindings[':min_price']) || $filters['max_price'] >= $bindings[':min_price']) {
                $sql .= " AND p.price <= :max_price";
                $bindings[':max_price'] = $filters['max_price'];
            }
        }

        // --- Apply Search Filter ---
        if (!empty($filters['search_term'])) {
            $searchTerm = '%' . trim($filters['search_term']) . '%';
            // Search product name, description, category name, artisan username/shop name
            $sql .= " AND (p.name LIKE :search_term
                           OR p.description LIKE :search_term
                           OR c.name LIKE :search_term
                           OR u.username LIKE :search_term
                           OR u.shop_name LIKE :search_term)";
            $bindings[':search_term'] = $searchTerm;
        }

        // --- Ordering (Example: Add price ordering option) ---
        $orderBy = 'p.created_at DESC'; // Default
        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'price_asc') {
                $orderBy = 'p.price ASC';
            } elseif ($filters['sort_by'] === 'price_desc') {
                $orderBy = 'p.price DESC';
            } elseif ($filters['sort_by'] === 'name_asc') {
                $orderBy = 'p.name ASC';
            }
            // Add other sort options if needed
        }
        $sql .= " ORDER BY " . $orderBy;

        // --- Pagination (Add later) ---

        try {

            $this->db->query($sql);
            foreach ($bindings as $param => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR; // Simple type check
                $this->db->bind($param, $value, $type);
            }
            return $this->db->resultSet();
        } catch (Exception $e) { /* ... Error logging ... */
            return [];
        }
    }

    public function getFeaturedActiveProducts($limit = null)
    {
        // Select columns needed for display on homepage card
        $sql = "SELECT
                    p.id, p.name, p.slug, p.price, p.image_path,
                    u.username as artisan_username, u.shop_name
                FROM products p
                LEFT JOIN users u ON p.artisan_id = u.id
                WHERE p.is_active = 1 AND p.is_featured = 1
                ORDER BY p.updated_at DESC"; // Order by most recently updated featured product

        if ($limit !== null && is_int($limit) && $limit > 0) {
            $sql .= " LIMIT :limit";
        }

        try {
            $this->db->query($sql);

            if ($limit !== null && is_int($limit) && $limit > 0) {
                $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            }

            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error fetching featured active products: " . $e->getMessage());
            return [];
        }
    }

    public function getArtOfTheWeek()
    {
        // Select fields needed for the specific display
        $sql = "SELECT
                    p.id, p.name, p.slug, p.price, p.image_path,
                    c.name as category_name,
                    u.first_name as artisan_firstname, u.last_name as artisan_lastname, u.shop_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.artisan_id = u.id
                WHERE p.is_active = 1 AND p.is_featured = 1
                ORDER BY p.updated_at DESC -- Or created_at DESC, or a dedicated 'featured_on' date
                LIMIT 1"; // Fetch only one

        try {
            $this->db->query($sql);
            return $this->db->single(); // Fetch a single object
        } catch (Exception $e) {
            error_log("Error fetching Art of the Week: " . $e->getMessage());
            return false;
        }
    }
    public function getActiveProductByArtisanUsernameAndSlug($artisanUsername, $productSlug)
    {
        try {
            $this->db->query("SELECT
                                p.id, p.name, p.slug, p.description, p.price, p.image_path, p.stock_quantity, p.created_at, p.artisan_id,
                                c.name as category_name, c.slug as category_slug,
                                u.username as artisan_username, u.shop_name, u.profile_picture_path as artisan_image, u.bio as artisan_bio
                              FROM products p
                              LEFT JOIN categories c ON p.category_id = c.id
                              JOIN users u ON p.artisan_id = u.id
                              WHERE p.slug = :product_slug
                                AND u.username = :artisan_username
                                AND p.is_active = 1
                                AND u.is_active = 1
                              LIMIT 1");

            $this->db->bind(':product_slug', $productSlug);
            $this->db->bind(':artisan_username', $artisanUsername);

            $product = $this->db->single(); // Returns object or false

            return $product; // Return the fetched product object or false

        } catch (Exception $e) {
            error_log("Error fetching product by artisan/slug ($artisanUsername / $productSlug): " . $e->getMessage());
            return false;
        }
    }

    public function decrementStock($productId, $quantityToDecrement)
    {
        if ($quantityToDecrement <= 0) return true; // No change needed

        // Note: This doesn't prevent overselling in high-concurrency scenarios without row locking,
        // but it's a basic check for single-user flow.
        try {
            $this->db->query('UPDATE products
                               SET stock_quantity = stock_quantity - :quantity
                               WHERE id = :id AND stock_quantity >= :quantity'); // Prevent going below zero

            $this->db->bind(':quantity', $quantityToDecrement, PDO::PARAM_INT);
            $this->db->bind(':id', $productId, PDO::PARAM_INT);

            if ($this->db->execute()) {
                // Check if any row was actually updated (means stock was sufficient)
                return $this->db->rowCount() > 0;
            } else {
                return false; // DB execution failed
            }
        } catch (Exception $e) {
            error_log("Error decrementing stock for product $productId: " . $e->getMessage());
            return false;
        }
    }
    public function getProductForCart($id)
    {
        try {
            $this->db->query('SELECT id, name, price, stock_quantity, image_path, slug FROM products WHERE id = :id AND is_active = 1');
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            return $this->db->single(); // Returns object or false
        } catch (Exception $e) {
            error_log("Error fetching product for cart (ID: $id): " . $e->getMessage());
            return false;
        }
    }
}
