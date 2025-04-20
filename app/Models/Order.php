<?php

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Gets the total quantity of items sold per day for the last N days.
     * @param int $days Number of days to look back (including today).
     * @return array Array of objects with 'sale_date' and 'total_quantity'.
     */
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

    // Get count of pending orders (example)
    public function getPendingOrderCount()
    {
        try {
            $this->db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
            $row = $this->db->single();
            return ($row && isset($row->count)) ? $row->count : 0;
        } catch (Exception $e) {
            error_log("Error getting pending order count: " . $e->getMessage());
            return 'Error';
        }
    }

    // Add other order methods as needed
}
