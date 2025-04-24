<?php
// app/Controllers/CartController.php

class CartController extends Controller
{

    private $productModel;

    public function __construct()
    {
        $this->productModel = $this->model('Product');
        if (!$this->productModel) {
            die("Error loading product resource for cart.");
        }
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Start session if not already started (redundant if in index.php)
        }
        // Initialize cart in session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Display the shopping cart page.
     */
    public function index()
    {
        $cartItems = [];
        $cartTotal = 0.00;

        if (!empty($_SESSION['cart'])) {
            // Get product IDs from cart keys
            $productIds = array_keys($_SESSION['cart']);

            foreach ($productIds as $id) {
                $product = $this->productModel->getProductForCart($id); // Fetch current product details
                if ($product && is_object($product)) {
                    $quantity = $_SESSION['cart'][$id];
                    // Prevent negative quantity display issues
                    if ($quantity <= 0) {
                        unset($_SESSION['cart'][$id]); // Remove invalid item
                        continue;
                    }
                    // Check against current stock (optional display feature)
                    $product->cart_quantity = $quantity;
                    $product->subtotal = $product->price * $quantity;
                    // Flag if requested quantity exceeds available stock
                    $product->stock_issue = ($product->stock_quantity !== null && $quantity > $product->stock_quantity);
                    $cartItems[] = $product;
                    $cartTotal += $product->subtotal;
                } else {
                    // Product not found or inactive, remove from cart
                    unset($_SESSION['cart'][$id]);
                    error_log("Product ID {$id} removed from cart because it's no longer valid.");
                }
            }
        }

        $data = [
            'title' => 'Your Shopping Cart',
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal
        ];
        $this->view('cart/index', $data);
    }

    /**
     * Add an item to the cart (POST request).
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('marketplace'); // Redirect if not POST
            return;
        }

        // Sanitize inputs
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        // Basic validation
        if (!$productId || $productId <= 0) { /* Handle error */
            $this->redirectBackWithError('Invalid product.');
            return;
        }
        if (!$quantity || $quantity <= 0) {
            $quantity = 1;
        } // Default quantity to 1 if invalid/missing

        // Check if product exists and is active
        $product = $this->productModel->getProductForCart($productId);
        if (!$product) { /* Handle error */
            $this->redirectBackWithError('Product not found or unavailable.');
            return;
        }

        // Optional: Check stock before adding (basic check)
        $currentCartQuantity = $_SESSION['cart'][$productId] ?? 0;
        $newTotalQuantity = $currentCartQuantity + $quantity;
        if ($product->stock_quantity !== null && $newTotalQuantity > $product->stock_quantity) {
            /* Handle error */
            $this->redirectBackWithError('Not enough stock available.');
            return;
        }

        // Add or update quantity in cart session
        $_SESSION['cart'][$productId] = $newTotalQuantity;

        // flash('cart_success', 'Product added to cart!', 'alert alert-info');
        // Redirect back to previous page or cart page
        $this->redirect('cart'); // Redirect to cart view
    }

    /**
     * Update item quantity in cart (POST request).
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cart');
            return;
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$productId || !isset($_SESSION['cart'][$productId])) { /* Handle error */
            $this->redirectBackWithError('Invalid product.');
            return;
        }

        if ($quantity !== false && $quantity > 0) {
            // Optional: Check stock on update
            $product = $this->productModel->getProductForCart($productId);
            if ($product && $product->stock_quantity !== null && $quantity > $product->stock_quantity) {
                /* Handle error */
                $this->redirectBackWithError('Not enough stock for updated quantity.');
                return;
            }
            $_SESSION['cart'][$productId] = $quantity; // Update quantity
        } elseif ($quantity !== false && $quantity <= 0) {
            unset($_SESSION['cart'][$productId]); // Remove if quantity is 0 or less
        } else {
            /* Handle error */
            $this->redirectBackWithError('Invalid quantity.');
            return;
        }

        // flash('cart_update', 'Cart updated.', 'alert alert-info');
        $this->redirect('cart');
    }

    /**
     * Remove an item from the cart (typically GET or POST with ID).
     */
    public function remove($productId = 0)
    {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);

        if ($productId && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            // flash('cart_remove', 'Item removed from cart.', 'alert alert-warning');
        } else {
            // flash('cart_error', 'Could not remove item.', 'alert alert-danger');
        }
        $this->redirect('cart');
    }

    /**
     * Helper to redirect back with an error message (using flash).
     */
    private function redirectBackWithError($message)
    {
        // flash('cart_error', $message, 'alert alert-danger');
        // Redirect to cart or previous page using HTTP_REFERER (less reliable)
        $this->redirect('cart');
        exit;
    }
} // End Class
