<?php

class ProductsController extends Controller
{

    private $productModel;
    private $userModel;
    private $categoryModel;

    public function __construct()
    {
        $this->productModel = $this->model('Product');
        $this->userModel = $this->model('Users');
        $this->categoryModel = $this->model('Category');

        if (!$this->productModel || !$this->categoryModel || !$this->userModel) {
            die("Error loading product/category resources.");
        }
    }

    // Show all products
    public function index()
    {
        $products = $this->productModel->getProducts();
        $data = [
            'title' => 'Browse Creations',
            'products' => $products
        ];
        $this->view('products/index', $data); // Needs view: Views/products/index.php
    }

    // Show single product
    public function show($id)
    {
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
    public function create()
    {
        // Check if user is logged in and is an artisan FIRST
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
            // flash('auth_error', 'Only artisans can add products.', 'alert alert-danger');
            $this->redirect('users/loginRegister'); // Redirect to login if not artisan
            return;
        }

        // Fetch categories for the dropdown
        $categories = $this->categoryModel->getAllCategories();

        // Default data for the form view
        $data = [
            'title' => 'Add New Artwork/Product',
            'cssFile' => 'create-product.css',
            'categories' => $categories, // Pass categories
            'name' => '',
            'description' => '',
            'price' => '',
            'stock_quantity' => 1,
            'category_id' => '',
            'name_err' => '',
            'description_err' => '',
            'price_err' => '',
            'category_id_err' => '',
            'stock_quantity_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];

        // Changed view path to match convention used elsewhere
        $this->view('products/create_product', $data);
    }

    public function store()
    {
        // Check if user is logged in and is an artisan and it's a POST request
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users/loginRegister');
            return;
        }

        // Sanitize inputs individually
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_SANITIZE_NUMBER_INT);
        // Ensure category_id is treated as int
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
        $category_id = ($category_id === false || $category_id === null) ? '' : (int)$category_id;


        // Prepare data for validation and potential view reload
        $data = [
            'title' => 'Add New Artwork/Product',
            'cssFile' => 'create-product.css',
            'categories' => $this->categoryModel->getAllCategories(), // Fetch again for reload
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock_quantity' => ($stock_quantity === '' || $stock_quantity === false) ? '' : $stock_quantity, // Keep empty if invalid for repopulation
            'category_id' => $category_id,
            'name_err' => '',
            'description_err' => '',
            'price_err' => '',
            'category_id_err' => '',
            'stock_quantity_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];


        // --- Image Upload Handling (Keep from previous example) ---
        $uploadedImageFilename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // ... (Same image validation and move logic as before) ...
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $fileSize = $_FILES['image']['size'];
            $fileType = $_FILES['image']['type'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 5 * 1024 * 1024;

            if (!in_array($fileExtension, $allowedfileExtensions)) {
                $data['image_err'] = 'Invalid file type...';
            } elseif ($fileSize > $maxFileSize) {
                $data['image_err'] = 'File size exceeds 5MB limit.';
            } else {
                $newFileName = uniqid('prod_', true) . '.' . $fileExtension;
                $dest_path = PRODUCT_IMG_UPLOAD_DIR . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $uploadedImageFilename = $newFileName;
                } else {
                    $data['image_err'] = 'Error uploading image (permissions?).';
                    error_log("Error moving uploaded product image to: " . $dest_path);
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
            $data['image_err'] = 'File upload error code: ' . $_FILES['image']['error'];
        } else {
            $data['image_err'] = 'Product image is required.';
        }
        // --- End Image Upload Handling ---


        // --- Text Field Validation ---
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter product name.';
        }
        if (empty($data['description'])) {
            $data['description_err'] = 'Please enter a description.';
        }
        if ($data['price'] === '' || $data['price'] === false || !is_numeric($data['price']) || $data['price'] < 0) {
            $data['price_err'] = 'Please enter a valid positive price.';
        }
        if ($data['stock_quantity'] === '' || $data['stock_quantity'] === false || !is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
            $data['stock_quantity_err'] = 'Please enter a valid stock quantity (0 or more).';
        }
        if (empty($data['category_id'])) {
            $data['category_id_err'] = 'Please select a category.';
        }
        // --- End Validation ---


        // --- If No Errors, Proceed to Create ---
        if (empty($data['name_err']) && empty($data['description_err']) && empty($data['price_err']) && empty($data['category_id_err']) && empty($data['stock_quantity_err']) && empty($data['image_err'])) {

            // Prepare data for the model using correct keys
            $productData = [
                'artisan_id' => $_SESSION['user_id'],
                'category_id' => $data['category_id'], // Already int
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'], // Should be numeric
                'stock_quantity' => $data['stock_quantity'], // Already int
                'image_path' => $uploadedImageFilename, // Filename or null
                // 'is_active' => 1 // Let model handle default
                // 'is_featured' => 0 // Let model handle default
            ];

            // Call the REFINED model method
            if ($this->productModel->createProduct($productData)) { // Use createProduct
                // flash('product_create_success', ...);
                $this->redirect('users/dashboard#art-content'); // Redirect to art tab on dashboard
                return;
            } else {
                // flash('product_create_error', ...);
                $data['general_err'] = 'Failed to save product (Database Error).';
                $this->view('products/create', $data); // Show form with general error
            }
        } else {
            // Validation errors occurred, reload the form with errors
            $this->view('products/create_product', $data);
        }
    }
}
