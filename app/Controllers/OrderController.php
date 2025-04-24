<?php
// app/Controllers/OrderController.php

class OrderController extends Controller
{

    private $orderModel;
    private $productModel;
    private $cartItemsData = null; // Cache cart details for checkout/process

    public function __construct()
    {
        // --- Login Check --- REQUIRED for placing orders in this setup
        if (!isset($_SESSION['user_id'])) {
            // flash('login_required', 'Please log in to manage orders or checkout.', 'alert alert-warning');
            $this->redirect('users/loginRegister');
            exit;
        }
        // --- End Login Check ---

        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
        if (!$this->orderModel || !$this->productModel) {
            die("Error loading order/product resources.");
        }
        // Ensure session is active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Fetch and prepare cart details (used by checkout and process).
     * Returns true if cart is not empty, false otherwise.
     */
    private function prepareCartData()
    {
        if ($this->cartItemsData !== null) return !empty($this->cartItemsData); // Return cached if already prepared

        $cartItems = [];
        $cartTotal = 0.00;
        $cartEmpty = true;

        if (!empty($_SESSION['cart'])) {
            $productIds = array_keys($_SESSION['cart']);
            foreach ($productIds as $id) {
                $product = $this->productModel->getProductForCart($id);
                if ($product) {
                    $quantity = $_SESSION['cart'][$id];
                    if ($quantity <= 0) {
                        unset($_SESSION['cart'][$id]);
                        continue;
                    }

                    // CRITICAL Check: Ensure sufficient stock exists NOW
                    if ($product->stock_quantity !== null && $quantity > $product->stock_quantity) {
                        // If stock is insufficient, redirect back to cart with error (can't proceed)
                        // flash('checkout_error', "Stock issue for product '{$product->name}'. Please update your cart.", 'alert alert-danger');
                        $this->redirect('cart');
                        exit;
                    }

                    $product->cart_quantity = $quantity;
                    $product->subtotal = $product->price * $quantity;
                    $cartItems[] = $product;
                    $cartTotal += $product->subtotal;
                    $cartEmpty = false; // Cart is not empty
                } else {
                    unset($_SESSION['cart'][$id]); // Remove invalid products
                }
            }
        }

        $this->cartItemsData = ['items' => $cartItems, 'total' => $cartTotal];
        return !$cartEmpty;
    }


    /**
     * Display the checkout page.
     */
    public function checkout()
    {
        // Ensure cart is not empty and has valid stock levels
        if (!$this->prepareCartData()) {
            // flash('cart_error', 'Your cart is empty or contains items with stock issues.', 'alert alert-warning');
            $this->redirect('cart');
            return;
        }

        $data = [
            'title' => 'Checkout',
            'cartItems' => $this->cartItemsData['items'],
            'cartTotal' => $this->cartItemsData['total'],
            // Add fields for address etc. if needed
            'shipping_address' => '',
            'billing_address' => '',
            'address_err' => '',
            'general_err' => ''
        ];
        $this->view('orders/checkout', $data); // Need Views/orders/checkout.php
    }

    /**
     * Process the checkout form (POST request).
     */
    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('checkout');
            return;
        }

        // Re-prepare and validate cart data (prices/stock might have changed)
        if (!$this->prepareCartData()) {
            // flash('cart_error', 'Your cart is empty or item stock has changed.', 'alert alert-warning');
            $this->redirect('cart');
            return;
        }

        // --- Get Data from Checkout Form ---
        // Example: Simple address handling (add more validation)
        $shippingAddress = trim(filter_input(INPUT_POST, 'shipping_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
        $billingAddress = trim(filter_input(INPUT_POST, 'billing_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
        // Add validation for required fields if needed

        $cartItems = $this->cartItemsData['items'];
        $cartTotal = $this->cartItemsData['total'];
        $customerId = $_SESSION['user_id'];

        // --- Database Transaction ---
        // Access the PDO instance from the Database singleton
        $pdo = Database::getInstance()->getPDO(); // Assumes getPDO() method exists in Database.php
        if (!$pdo) { /* Handle error */
            die('DB connection error');
        }

        try {
            $pdo->beginTransaction();

            // 1. Create the main order record
            $orderId = $this->orderModel->createOrder($customerId, $cartTotal, $shippingAddress, $billingAddress);
            if (!$orderId) {
                throw new Exception("Failed to create main order record.");
            }

            // 2. Create order items and decrement stock
            foreach ($cartItems as $item) {
                // Insert item into order_items
                if (is_object($item) && !$this->orderModel->createOrderItem($orderId, $item->id, $item->cart_quantity, $item->price)) {
                    throw new Exception("Failed to create order item for product ID: " . $item->id);
                }
                // Decrement stock
                if ($item->stock_quantity !== null) { // Only decrement if stock is tracked
                    if (!$this->productModel->decrementStock($item->id, $item->cart_quantity)) {
                        // This should have been caught by prepareCartData, but double-check
                        throw new Exception("Stock update failed for product ID: " . $item->id . ". Insufficient stock.");
                    }
                }
            }

            // 3. If all successful, commit transaction
            $pdo->commit();

            // 4. Clear the cart
            unset($_SESSION['cart']);

            // 5. Redirect to success page
            // flash('order_success', 'Your order has been placed successfully!', 'alert alert-success');
            $this->redirect('orders/success/' . $orderId);
            return;
        } catch (Exception $e) {
            // An error occurred, rollback transaction
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Checkout Processing Error: " . $e->getMessage());
            // flash('checkout_error', 'There was an error processing your order: ' . $e->getMessage(), 'alert alert-danger');
            $this->redirect('orders/checkout'); // Redirect back to checkout with error
            return;
        }
    }


    /**
     * Display the order success/confirmation page.
     */
    public function success($orderId = 0)
    {
        $orderId = filter_var($orderId, FILTER_VALIDATE_INT);
        if (!$orderId || $orderId <= 0) {
            $this->redirect('users/dashboard'); // Redirect if invalid ID
            return;
        }
        $data = [
            'title' => 'Order Confirmation',
            'orderId' => $orderId
        ];
        $this->view('orders/success', $data); // Need Views/orders/success.php
    }


    /**
     * Display user's order history (can be integrated into dashboard).
     */
    public function history()
    {
        $orders = $this->orderModel->getOrdersByCustomerId($_SESSION['user_id']);
        $data = [
            'title' => 'My Order History',
            'orders' => $orders
        ];
        $this->view('orders/history', $data); // Need Views/orders/history.php
    }

    /**
     * Display details of a specific order.
     */
    public function details($orderId = 0)
    {
        $orderId = filter_var($orderId, FILTER_VALIDATE_INT);
        if (!$orderId || $orderId <= 0) {
            $this->redirect('orders/history');
            return;
        }

        // Fetch order, ensuring logged-in user owns it
        $order = $this->orderModel->getOrderDetailsById($orderId, $_SESSION['user_id']);

        if (!$order) {
            // flash('error', 'Order not found or access denied.', 'alert alert-warning');
            $this->redirect('orders/history');
            return;
        }

        $data = [
            'title' => 'Order Details #' . $order->id,
            'order' => $order
        ];
        $this->view('orders/details', $data); // Need Views/orders/details.php
    }
} // End Class
