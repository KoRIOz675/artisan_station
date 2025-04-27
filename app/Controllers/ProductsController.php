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
        $products = $this->productModel->getFilteredActiveProducts();
        $categories = $this->categoryModel->getAllCategories();
        $data = [
            'title' => 'Browse All Creations',
            'cssFile' => 'marketplace.css',
            'products' => $products,
            'categories' => $categories,
            'active_category_id' => '',
            'active_search_term' => ''
        ];
        $this->view('products/index', $data);
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

    public function showByArtisanAndSlug($artisanUsername = '', $productSlug = '')
    {
        $cleanUsername = filter_var(trim($artisanUsername), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $cleanSlug = filter_var(trim($productSlug), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (empty($cleanUsername) || empty($cleanSlug)) {
            error_log("showByArtisanAndSlug called with empty username or slug.");
            $this->redirect('marketplace'); // Or show 404
            return;
        }

        // Fetch product using the new model method (includes artisan/category info)
        $product = $this->productModel->getActiveProductByArtisanUsernameAndSlug($cleanUsername, $cleanSlug);

        // Check if product was found
        if (!$product || !is_object($product)) {
            error_log("Product not found for {$cleanUsername} / {$cleanSlug}");
            $this->redirect('marketplace'); // Redirect to marketplace
            return;
        }

        // Prepare data for the view
        $data = [
            // Use product name for the page title
            'title' => htmlspecialchars($product->name),
            'cssFile' => 'product-page.css', // Optional specific CSS
            'product' => $product // Pass the full product object (including joined data)
            // You might fetch related products later here if needed
        ];

        // Load the single product view
        $this->view('products/show', $data);
    }

    public function edit($id = 0)
    {
        error_log("--- ProductController::edit({$id}) method started ---"); // DEBUG
        // 1. Check login & role
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
            error_log("Edit failed: User not logged in or not artisan. Redirecting..."); // DEBUG
            $this->redirect('users/loginRegister');
            return;
        }
        $artisanId = $_SESSION['user_id'];
        error_log("User ID: {$artisanId}"); // DEBUG

        // 2. Validate ID
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            // flash('error', 'Invalid product ID.', 'alert alert-danger');
            error_log("Edit failed: Invalid Product ID '{$id}'. Redirecting..."); // DEBUG
            $this->redirect('users/dashboard#art-content');
            return; // Redirect back to their art list
        }
        error_log("Product ID validated: {$id}"); // DEBUG

        // 3. Fetch product data
        error_log("Fetching product with ID: {$id}"); // DEBUG
        $product = $this->productModel->getProductById($id);
        error_log("Product data fetched: " . ($product ? 'Found object' : 'NOT FOUND')); // DEBUG

        // 4. Check if product exists and if the current user owns it
        if (!$product || !is_object($product)) {
            error_log("Edit failed: Product {$id} not found in DB. Redirecting..."); // DEBUG
            $this->redirect('users/dashboard#art-content');
            return;
        }
        if ($product->artisan_id != $artisanId) {
            error_log("Auth Error: User {$artisanId} attempting to edit product {$id} owned by {$product->artisan_id}. Redirecting..."); // DEBUG
            $this->redirect('users/dashboard#art-content');
            return;
        }
        error_log("Ownership verified for product {$id}."); // DEBUG

        // 5. Fetch categories for dropdown
        $categories = $this->categoryModel->getAllCategories();
        error_log("Categories fetched."); // DEBUG

        // 6. Prepare data for the view
        $data = [
            'title' => 'Edit Product: ' . htmlspecialchars($product->name),
            'product' => $product, // Pass the product object
            'categories' => $categories,
            // Form fields pre-populated (use values from $product)
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock_quantity' => $product->stock_quantity,
            'category_id' => $product->category_id,
            'current_image' => $product->image_path, // Current image path
            'is_active' => $product->is_active, // Current active status
            // Errors
            'name_err' => '',
            'description_err' => '',
            'price_err' => '',
            'category_id_err' => '',
            'stock_quantity_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];
        error_log("Data prepared for view. Loading view..."); // DEBUG

        // 7. Load the edit view
        $this->view('products/edit', $data); // Need Views/products/edit.php
        error_log("--- ProductController::edit({$id}) method finished ---");
    }

    public function update($id = 0)
    {
        error_log("--- ProductController::update({$id}) method started ---");
        // 1. Check login, role, POST method
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
            error_log("Update failed: User not logged in or not artisan."); // DEBUG
            $this->redirect('users/loginRegister');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Update failed: Request method was not POST."); // DEBUG
            // Maybe redirect back to edit form?
            $this->redirect('products/edit/' . $id);
            return;
        }
        $artisanId = $_SESSION['user_id'];
        error_log("User ID: {$artisanId}, Request Method: POST"); // DEBUG

        // 2. Validate ID
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            error_log("Update failed: Invalid Product ID '{$id}' from URL."); // DEBUG
            $this->redirect('users/dashboard#art-content');
            return;
        }
        error_log("Product ID from URL validated: {$id}"); // DEBUG

        // 3. Fetch original product to verify ownership BEFORE update
        error_log("Fetching original product {$id} for ownership check..."); // DEBUG
        $originalProduct = $this->productModel->getProductById($id);
        if (!$originalProduct || !is_object($originalProduct)) {
            error_log("Update failed: Original product {$id} not found."); // DEBUG
            $this->redirect('users/dashboard#art-content');
            return;
        }
        if ($originalProduct->artisan_id != $artisanId) {
            error_log("Auth Error during update: User {$artisanId} trying to update product {$id} owned by {$originalProduct->artisan_id}."); // DEBUG
            $this->redirect('users/dashboard#art-content');
            return;
        }
        error_log("Ownership verified for update."); // DEBUG

        // 4. Sanitize POST data
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_SANITIZE_NUMBER_INT);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
        $is_active = isset($_POST['is_active']) ? 1 : 0; // Check if checkbox is checked
        error_log("POST data sanitized.");

        // 5. Prepare data for validation / potential view reload
        $data = [
            'title' => 'Edit Product: ' . htmlspecialchars($name), // Use new name in title
            'product' => $originalProduct, // Needed if reloading form
            'categories' => $this->categoryModel->getAllCategories(),
            'id' => $id, // Need id for form action
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock_quantity' => ($stock_quantity === '' || $stock_quantity === false) ? '' : $stock_quantity,
            'category_id' => ($category_id === false || $category_id === null) ? '' : (int)$category_id,
            'current_image' => $originalProduct->image_path,
            'is_active' => $is_active,
            'name_err' => '',
            'description_err' => '',
            'price_err' => '',
            'category_id_err' => '',
            'stock_quantity_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];


        // 6. Image Upload Handling (same logic as store(), check if new image uploaded)
        $newImageFilename = null;
        $deleteOldImage = false;
        $currentImagePath = $originalProduct->image_path ?? null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // ... (Include image validation and move logic from store()) ...
            // ... (Use PRODUCT_IMG_UPLOAD_DIR) ...
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB Example

            if (!in_array($fileExtension, $allowedExtensions)) {
                $data['image_err'] = 'Invalid file type...';
            } elseif ($_FILES['image']['size'] > $maxFileSize) {
                $data['image_err'] = 'File size exceeds 5MB limit.';
            } else {
                $newFileName = 'prod_' . $id . '_' . uniqid('', true) . '.' . $fileExtension; // Prefix with product ID
                $dest_path = PRODUCT_IMG_UPLOAD_DIR . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $newImageFilename = $newFileName;
                    $deleteOldImage = true;
                } else {
                    $data['image_err'] = 'Error saving uploaded image.';
                    error_log("Error moving product edit image: " . $dest_path);
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
            $data['image_err'] = 'File upload error code: ' . $_FILES['image']['error'];
        }
        // --- End Image Upload Handling ---
        error_log("Image handling complete. New filename (null if none/error): " . ($newImageFilename ?? 'NULL')); // DEBUG


        // 7. Text Field Validation (same logic as store())
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter product name.';
        }
        if (empty($data['description'])) {
            $data['description_err'] = 'Please enter a description.';
        }
        // ... (validate price, stock, category) ...
        if ($data['price'] === '' || $data['price'] === false || !is_numeric($data['price']) || $data['price'] < 0) {
            $data['price_err'] = 'Please enter a valid positive price.';
        }
        if ($data['stock_quantity'] === '' || $data['stock_quantity'] === false || !is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
            $data['stock_quantity_err'] = 'Please enter a valid stock quantity (0 or more).';
        }
        if (empty($data['category_id'])) {
            $data['category_id_err'] = 'Please select a category.';
        }


        // 8. If No Errors, Proceed to Update
        error_log("Checking validation results. Image Error: '{$data['image_err']}', Name Error: '{$data['name_err']}', etc."); // DEBUG
        if (empty($data['name_err']) && empty($data['description_err']) && empty($data['price_err']) && empty($data['category_id_err']) && empty($data['stock_quantity_err']) && empty($data['image_err'])) {
            error_log("Validation passed. Preparing data for model updateProduct().");
            // Prepare data for the model - INCLUDE ID and ARTISAN ID
            $productData = [
                'id' => $id,
                'artisan_id' => $artisanId, // Crucial for ownership check in model
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
                'is_active' => $data['is_active']
            ];
            // Only add image path if a new one was uploaded
            if ($newImageFilename !== null) {
                $productData['image_path'] = $newImageFilename;
            }

            // Attempt to update product via model
            if ($this->productModel->updateProduct($productData)) {
                error_log("Model updateProduct() returned TRUE."); // DEBUG
                // Delete old image AFTER successful DB update
                if ($deleteOldImage && !empty($currentImagePath) && $currentImagePath !== $newImageFilename) {
                    $oldImagePathFull = PRODUCT_IMG_UPLOAD_DIR . $currentImagePath;
                    if (file_exists($oldImagePathFull)) {
                        @unlink($oldImagePathFull);
                    }
                }
                error_log("Update successful. Redirecting to dashboard."); // DEBUG
                // flash('product_update_success', 'Product updated successfully!', 'alert alert-success');
                $this->redirect('users/dashboard#art-content'); // Redirect back
                return;
            } else {
                error_log("Model updateProduct() returned FALSE. DB error likely."); // DEBUG
                $data['general_err'] = 'Failed to update product (Database Error).';
                // Delete newly uploaded file if DB update failed
                if ($newImageFilename !== null && file_exists(PRODUCT_IMG_UPLOAD_DIR . $newImageFilename)) {
                    @unlink(PRODUCT_IMG_UPLOAD_DIR . $newImageFilename);
                }
                $this->view('products/edit', $data); // Show form with general error
            }
        } else {
            // Validation errors occurred, reload form
            // Delete newly uploaded file if validation failed
            error_log("Validation errors occurred. Reloading edit form."); // DEBUG
            if ($newImageFilename !== null && file_exists(PRODUCT_IMG_UPLOAD_DIR . $newImageFilename)) {
                @unlink(PRODUCT_IMG_UPLOAD_DIR . $newImageFilename);
            }
            $this->view('products/edit', $data);
        }
    }

    public function destroy($id = 0)
    {
        // 1. Check login, role, POST method
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users/loginRegister');
            return;
        }
        $artisanId = $_SESSION['user_id'];

        // 2. Validate ID
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $this->redirect('users/dashboard#art-content');
            return;
        }

        // 3. Fetch product BEFORE deleting to get image path and verify ownership
        $productToDelete = $this->productModel->getProductById($id);
        if (!$productToDelete || !is_object($productToDelete) || $productToDelete->artisan_id != $artisanId) {
            error_log("Auth Error during delete: User {$artisanId} trying to delete product {$id} they don't own or not found.");
            // flash('error', 'Product not found or permission denied.', 'alert alert-danger');
            $this->redirect('users/dashboard#art-content');
            return;
        }

        // 4. Attempt to delete product via model (passing ID and verified artisan ID)
        if ($this->productModel->deleteProduct($id, $artisanId)) {
            // 5. Delete image file AFTER successful DB deletion
            if (!empty($productToDelete->image_path)) {
                $imagePathFull = PRODUCT_IMG_UPLOAD_DIR . $productToDelete->image_path;
                if (file_exists($imagePathFull)) {
                    if (@unlink($imagePathFull)) {
                        error_log("Deleted product image: " . $imagePathFull);
                    } else {
                        error_log("Failed to delete product image (permissions?): " . $imagePathFull);
                    }
                } else {
                    error_log("Product image file not found for deletion: " . $imagePathFull);
                }
            }
            // flash('product_delete_success', 'Product deleted successfully.', 'alert alert-success');
        } else {
            // flash('product_delete_error', 'Failed to delete product.', 'alert alert-danger');
        }

        // 6. Redirect back to the art list
        $this->redirect('users/dashboard#art-content');
    }
}
