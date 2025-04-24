<?php

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDailyItemsSoldCount($days = 10)
    {
        if (!is_int($days) || $days < 1) {
            $days = 10; // Default safeguard
        }

        // Query joins orders and order_items, sums quantity, groups by date
        // Filters for orders within the specified date range
        // Assumes orders.status might indicate a completed sale (e.g., 'paid', 'shipped', 'delivered')
        // ADJUST status check based on your system logic
        $this->db->query("SELECT DATE(o.order_datetime) as sale_date, SUM(oi.quantity) as total_quantity
                          FROM orders o
                          JOIN order_items oi ON o.id = oi.order_id
                          WHERE o.order_datetime >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                            AND o.status IN ('paid', 'processing', 'shipped', 'delivered') -- Adjust statuses as needed!
                          GROUP BY DATE(o.order_datetime)
                          ORDER BY sale_date ASC");

        $this->db->bind(':days', $days - 1, PDO::PARAM_INT); // Interval needs days-1 to include today

        return $this->db->resultSet();
    }

    public function createOrder($customerId, $totalAmount, $shippingAddress = null, $billingAddress = null)
    {
        try {
            $this->db->query('INSERT INTO orders (customer_id, total_amount, status, shipping_address, billing_address)
                              VALUES (:customer_id, :total_amount, :status, :shipping_address, :billing_address)');

            $this->db->bind(':customer_id', $customerId, PDO::PARAM_INT);
            $this->db->bind(':total_amount', $totalAmount); // Let PDO handle type
            $this->db->bind(':status', 'pending'); // Default status
            $this->db->bind(':shipping_address', $shippingAddress); // Can be null
            $this->db->bind(':billing_address', $billingAddress);   // Can be null

            if ($this->db->execute()) {
                return $this->db->lastInsertId(); // Return the new order ID
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("Error creating order: " . $e->getMessage());
            return false;
        }
    }

    public function createOrderItem($orderId, $productId, $quantity, $priceAtPurchase)
    {
        try {
            $this->db->query('INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
                              VALUES (:order_id, :product_id, :quantity, :price_at_purchase)');

            $this->db->bind(':order_id', $orderId, PDO::PARAM_INT);
            $this->db->bind(':product_id', $productId, PDO::PARAM_INT);
            $this->db->bind(':quantity', $quantity, PDO::PARAM_INT);
            $this->db->bind(':price_at_purchase', $priceAtPurchase);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error creating order item for order $orderId, product $productId: " . $e->getMessage());
            return false;
        }
    }

    public function getOrdersByCustomerId($customerId, $limit = 20)
    {
        try {
            $this->db->query('SELECT id, order_datetime, total_amount, status
                               FROM orders
                               WHERE customer_id = :customer_id
                               ORDER BY order_datetime DESC
                               LIMIT :limit');
            $this->db->bind(':customer_id', $customerId, PDO::PARAM_INT);
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error fetching orders for customer $customerId: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderDetailsById($orderId, $customerId = null)
    {
        try {
            // Fetch main order details
            $sql = 'SELECT o.*, u.username as customer_username, u.email as customer_email
                     FROM orders o
                     JOIN users u ON o.customer_id = u.id
                     WHERE o.id = :order_id';
            // Optionally restrict by customer ID for security
            if ($customerId !== null) {
                $sql .= ' AND o.customer_id = :customer_id';
            }
            $this->db->query($sql);
            $this->db->bind(':order_id', $orderId, PDO::PARAM_INT);
            if ($customerId !== null) {
                $this->db->bind(':customer_id', $customerId, PDO::PARAM_INT);
            }
            $order = $this->db->single();

            if (!$order) {
                return false; // Order not found or access denied
            }

            // Fetch order items
            $this->db->query('SELECT oi.*, p.name as product_name, p.slug as product_slug, p.image_path as product_image
                               FROM order_items oi
                               JOIN products p ON oi.product_id = p.id
                               WHERE oi.order_id = :order_id');
            $this->db->bind(':order_id', $orderId, PDO::PARAM_INT);
            $items = $this->db->resultSet();

            $order->items = $items; // Attach items to the order object

            return $order;
        } catch (Exception $e) {
            error_log("Error fetching order details for order $orderId: " . $e->getMessage());
            return false;
        }
    }
}
