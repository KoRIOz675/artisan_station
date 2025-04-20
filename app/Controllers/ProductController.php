<?php

class ProductController extends Controller {

    private $productModel;
    private $userModel; // To check artisan status/ownership

    public function __construct() {
        $this->productModel = $this->model('Product');
        $this->userModel = $this->model('User'); // Needed for artisan info

        // Session start should ideally be in the front controller (public/index.php)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Show all products
    public function index() {
        $products = $this->productModel->getProducts();
        $data = [
            'title' => 'Browse Creations',
            'products' => $products
        ];
        $this->view('products/index', $data); // Needs view: Views/products/index.php
    }

    // Show single product
    public function show($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            // Handle not found error, maybe redirect to product index
            $this->redirect('products'); // Simple redirect
            return;
        }

        $artisan = $this->userModel->findUserById($product['artisan_id']);

        $data = [
            'title' => htmlspecialchars($product['name']),
            'product' => $product,
            'artisan' => $artisan
        ];
        $this->view('products/show', $data); // Needs view: Views/products/show.php
    }

    // Show form to create new product (requires login as artisan)
    public function create() {
        // Check if user is logged in and is an artisan
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
            // Redirect to login or show error
             $this->redirect('users/login');
            return;
        }

        $data = [
            'title' => 'Add New Creation',
            // Fields for the form (to repopulate on error)
            'name' => '', 'description' => '', 'price' => '', 'materials' => '',
            'dimensions' => '', 'production_time' => '', 'category_id' => '',
            // Error messages
            'name_err' => '', 'description_err' => '', 'price_err' => '' // etc.
        ];
        $this->view('products/create', $data); // Needs view: Views/products/create.php
    }

    // Store new product in DB (POST request from create form)
    public function store() {
         // Check if user is logged in and is an artisan
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             $this->redirect('users/login');
            return;
        }

        // Sanitize POST data (basic example)
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // --- Basic Validation (implement more robust validation) ---
        $errors = false;
        $data = [
            'title' => 'Add New Creation',
            'artisan_id' => $_SESSION['user_id'],
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'price' => trim($_POST['price']),
            'materials' => trim($_POST['materials']),
            'dimensions' => trim($_POST['dimensions']),
            'production_time' => trim($_POST['production_time']),
            'category_id' => trim($_POST['category_id']),
             // Handle image upload separately and securely! Get image path here.
            'image_path' => 'placeholder.jpg', // Placeholder!
            'name_err' => '', 'description_err' => '', 'price_err' => '' // etc.
        ];


        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter product name';
            $errors = true;
        }
        if (empty($data['description'])) {
            $data['description_err'] = 'Please enter a description';
            $errors = true;
        }
         if (empty($data['price']) || !is_numeric($data['price'])) {
            $data['price_err'] = 'Please enter a valid price';
            $errors = true;
        }
        // Add more validation rules...

        // If no errors, attempt to add product
        if (!$errors) {
             // Handle image upload here!
             // $data['image_path'] = $this->handleUpload(); // Example

            if ($this->productModel->addProduct($data)) {
                // Success - redirect to the new product or product list
                 // Add flash message for success
                $this->redirect('products');
            } else {
                // Error adding product - show error message
                die('Something went wrong adding the product.'); // Improve error handling
            }
        } else {
            // Validation failed - reload form with errors
            $this->view('products/create', $data);
        }
    }

    // --- Add edit(), update(), destroy() methods similarly ---
    // Remember to check ownership: $_SESSION['user_id'] == $product['artisan_id']
}