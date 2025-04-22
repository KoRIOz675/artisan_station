<?php

class AdminController extends Controller
{

    private $usersModel;
    private $eventModel;
    private $productModel;
    private $orderModel;
    private $categoryModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('users/loginRegister');
            exit;
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('users/dashboard');
            exit;
        }
        $this->usersModel = $this->model('Users');
        $this->productModel = $this->model('Product');
        $this->eventModel = $this->model('Event');
        $this->orderModel = $this->model('Order');
        $this->categoryModel = $this->model('Category');
        if (!$this->usersModel  || !$this->productModel || !$this->eventModel || !$this->categoryModel) {
            error_log("Failed to load models in AdminController.");
            die("Error loading admin resources.");
        }
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $registrationData = $this->usersModel->getDailyRegistrationsCount(7); // Last 7 days
        $salesData = $this->orderModel->getDailyItemsSoldCount(10); // Last 10 days

        $data = [
            'title' => 'Admin Dashboard',
            'cssFile' => 'admin-dashboard.css',
            'userCount' => $this->usersModel->getTotalUserCount() ?? 'Err',
            'productCount' => $this->productModel->getTotalProductCount() ?? 'Err',
            'eventCount' => $this->eventModel->getTotalEventCount() ?? 'Err',
            'pendingOrderCount' => 0,
            // Chart
            'registrationChartData' => $registrationData,
            'salesChartData'        => $salesData
        ];
        $this->view('admin/index', $data);
    }

    // --- Users Management ---
    public function manageUsers()
    {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('users/dashboard');
            exit;
        }
        $users = $this->usersModel->getAllUsersWithDetails();
        $data = ['title' => 'Manage Users', 'users' => $users];
        $this->view('admin/manage_users', $data);
    }

    public function toggleUserActive($userId = 0, $currentState = 0)
    {
        // Basic validation
        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        $currentState = filter_var($currentState, FILTER_VALIDATE_INT); // 0 or 1

        if ($userId === false || $userId <= 0 || $currentState === false || !in_array($currentState, [0, 1])) {
            // flash('error', 'Invalid user ID or status for toggle.', 'alert alert-danger');
            $this->redirect('admin/manageUsers');
            return;
        }
        // Prevent admin from deactivating themselves (optional safeguard)
        if ($userId == $_SESSION['user_id']) {
            // flash('error', 'You cannot deactivate your own account.', 'alert alert-danger');
            $this->redirect('admin/manageUsers');
            return;
        }

        $newStatus = ($currentState == 1) ? 0 : 1; // Toggle status

        if ($this->usersModel->updateUserActiveStatus($userId, $newStatus)) {
            // flash('success', 'User status updated successfully.', 'alert alert-success');
        } else {
            // flash('error', 'Failed to update user status.', 'alert alert-danger');
        }
        $this->redirect('admin/manageUsers');
    }

    // --- Product Management ---
    public function manageProducts()
    {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('users/dashboard');
            exit;
        }
        // Fetch all products (implement in Product model)
        // ...
        echo "<h1>Manage Products Page (Not Implemented)</h1>";
    }

    // --- Order Management ---
    public function manageOrders()
    {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('users/dashboard');
            exit;
        }
        // Fetch all orders (implement in Order model)
        // ...
        echo "<h1>Manage Orders Page (Not Implemented)</h1>";
    }

    // --- Events Management ---
    public function manageEvents()
    {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('users/dashboard');
            exit;
        }
        $events = $this->eventModel->getAllEvents();
        $data = ['title' => 'Manage Events', 'events' => $events];
        $this->view('admin/manage_events', $data);
    }

    public function toggleEventActive($eventId = 0, $currentState = 0)
    {
        $eventId = filter_var($eventId, FILTER_VALIDATE_INT);
        $currentState = filter_var($currentState, FILTER_VALIDATE_INT);

        if ($eventId === false || $eventId <= 0 || $currentState === false || !in_array($currentState, [0, 1])) {
            // flash('error', 'Invalid event ID or status for toggle.', 'alert alert-danger');
            $this->redirect('admin/manageEvents');
            return;
        }

        $newStatus = ($currentState == 1) ? 0 : 1; // Toggle status

        if ($this->eventModel->updateEventActiveStatus($eventId, $newStatus)) {
            // flash('success', 'Event status updated successfully.', 'alert alert-success');
        } else {
            // flash('error', 'Failed to update event status.', 'alert alert-danger');
        }
        $this->redirect('admin/manageEvents');
    }

    // --- Site settings ---
    public function siteSettings()
    {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('users/dashboard');
            exit;
        }
        $definedConstants = get_defined_constants(true)['user']; // Get user-defined constants

        $data = [
            'title' => 'Site Settings',
            'settings' => $definedConstants // Pass them to the view
        ];
        $this->view('admin/site_settings', $data);
    }

    // --- Featured Content Management ---
    public function manageFeatured()
    {
        $artisans = $this->usersModel->getAllArtisansForFeatured();
        $products = $this->productModel->getAllProductsForFeatured();

        $data = [
            'title' => 'Manage Featured Content',
            'artisans' => $artisans,
            'products' => $products
        ];
        $this->view('admin/manage_featured', $data);
    }

    // Action to toggle artisan featured status
    public function toggleArtisanFeatured($artisanId = 0, $currentState = 0)
    {
        $artisanId = filter_var($artisanId, FILTER_VALIDATE_INT);
        $currentState = filter_var($currentState, FILTER_VALIDATE_INT);

        if ($artisanId === false || $artisanId <= 0 || $currentState === false || !in_array($currentState, [0, 1])) {
            $this->redirect('admin/manageFeatured');
            return;
        }
        $newStatus = ($currentState == 1) ? 0 : 1;
        if ($this->usersModel->updateArtisanFeaturedStatus($artisanId, $newStatus)) { /* Success */
        } else { /* Error */
        }
        $this->redirect('admin/manageFeatured');
    }

    // Action to toggle product featured status
    public function toggleProductFeatured($productId = 0, $currentState = 0)
    {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        $currentState = filter_var($currentState, FILTER_VALIDATE_INT);

        if ($productId === false || $productId <= 0 || $currentState === false || !in_array($currentState, [0, 1])) {
            $this->redirect('admin/manageFeatured');
            return;
        }
        $newStatus = ($currentState == 1) ? 0 : 1;
        if ($this->productModel->updateProductFeaturedStatus($productId, $newStatus)) { /* Success */
        } else { /* Error */
        }
        $this->redirect('admin/manageFeatured');
    }

    public function changeUserRole($userId = 0, $newRole = '')
    {
        // 1. Validate inputs
        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        $allowedRoles = ['customer', 'artisan', 'admin'];
        $newRole = filter_var(trim(strtolower($newRole)), FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Sanitize role name

        if ($userId === false || $userId <= 0 || empty($newRole) || !in_array($newRole, $allowedRoles)) {
            // flash('error', 'Invalid user ID or role provided.', 'alert alert-danger');
            $this->redirect('admin/manageUsers');
            return;
        }

        // 2. Prevent admin from changing their own role
        if ($userId == $_SESSION['user_id']) {
            flash('error', 'You cannot change your own role.', 'alert alert-danger');
            $this->redirect('admin/manageUsers');
            return;
        }

        // 3. Attempt to update the role via the model
        if ($this->usersModel->updateUserRole($userId, $newRole)) {
            flash('success', 'User role updated successfully.', 'alert alert-success');
        } else {
            flash('error', 'Failed to update user role.', 'alert alert-danger');
        }

        // 4. Redirect back to the user management page
        $this->redirect('admin/manageUsers');
    }

    public function manageCategories()
    {
        $categories = $this->categoryModel->getAllCategories();
        $data = [
            'title' => 'Manage Categories',
            'categories' => $categories
        ];
        $this->view('admin/manage_categories', $data);
    }

    public function createCategory()
    {
        $data = [
            'title' => 'Add New Category',
            'name' => '',
            'description' => '',
            'name_err' => '',
            'description_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];
        $this->view('admin/create_category', $data);
    }

    // Store a new category (POST request)
    public function storeCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/manageCategories');
            return;
        }

        // Sanitize text inputs
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        // Prepare data for validation/view
        $data = [
            'title' => 'Add New Category',
            'name' => $name,
            'description' => $description,
            'name_err' => '',
            'description_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];

        // --- Basic Validation ---
        if (empty($name)) {
            $data['name_err'] = 'Category name is required.';
        } elseif ($this->categoryModel->findCategoryByName($name)) {
            $data['name_err'] = 'Category name already exists.';
        }

        // --- Image Upload ---
        $uploadedImageFilename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // ... (Include the same image upload validation logic from ProductController::store) ...
            // ... (Ensure you use CATEGORY_IMG_UPLOAD_DIR) ...
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB Example

            if (!in_array($fileExtension, $allowedfileExtensions)) {
                $data['image_err'] = 'Invalid file type...';
            } elseif ($_FILES['image']['size'] > $maxFileSize) {
                $data['image_err'] = 'File size exceeds 2MB limit.';
            } else {
                $newFileName = uniqid('cat_', true) . '.' . $fileExtension;
                $dest_path = CATEGORY_IMG_UPLOAD_DIR . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $uploadedImageFilename = $newFileName;
                } else {
                    $data['image_err'] = 'Error uploading image (permissions?).';
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
            $data['image_err'] = 'File upload error code: ' . $_FILES['image']['error'];
        }
        // --- End Image Upload ---


        // --- Process if No Errors ---
        if (empty($data['name_err']) && empty($data['image_err'])) {
            $categoryData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'image_path' => $uploadedImageFilename // Will be null if no upload or error
            ];

            if ($this->categoryModel->createCategory($categoryData)) {
                // flash('success', 'Category created successfully.', 'alert alert-success');
                $this->redirect('admin/manageCategories');
                return;
            } else {
                $data['general_err'] = 'Failed to create category (database error).';
                // Optionally delete uploaded file if DB insert failed
                if ($uploadedImageFilename && file_exists(CATEGORY_IMG_UPLOAD_DIR . $uploadedImageFilename)) {
                    @unlink(CATEGORY_IMG_UPLOAD_DIR . $uploadedImageFilename);
                }
                $this->view('admin/create_category', $data); // Reload form with error
            }
        } else {
            // Validation errors, reload form
            $this->view('admin/create_category', $data);
        }
    }


    // Show form to edit an existing category
    public function editCategory($id = 0)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            $this->redirect('admin/manageCategories');
            return;
        }

        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            // flash('error', 'Category not found.', 'alert alert-danger');
            $this->redirect('admin/manageCategories');
            return;
        }

        $data = [
            'title' => 'Edit Category',
            'id' => $id,
            'category' => $category, // Pass current category data
            'name' => $category->name, // Pre-fill form
            'description' => $category->description ?? '',
            'current_image' => $category->image_path, // To display current image
            'name_err' => '',
            'description_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];
        $this->view('admin/edit_category', $data);
    }


    // Update an existing category (POST request)
    public function updateCategory($id = 0)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/manageCategories');
            return;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            $this->redirect('admin/manageCategories');
            return;
        }

        // Get original category data to compare names/slugs and handle image
        $originalCategory = $this->categoryModel->getCategoryById($id);
        if (!$originalCategory) {
            $this->redirect('admin/manageCategories');
            return; // Or show error
        }

        // Sanitize inputs
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        // Prepare data for validation/view
        $data = [
            'title' => 'Edit Category',
            'id' => $id,
            'category' => $originalCategory, // Needed if reloading form
            'name' => $name,
            'description' => $description,
            'current_image' => $originalCategory->image_path,
            'name_err' => '',
            'description_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];


        // --- Basic Validation ---
        if (empty($name)) {
            $data['name_err'] = 'Category name is required.';
        }
        // Check name uniqueness *excluding* the current category ID
        elseif ($name !== $originalCategory->name && $this->categoryModel->findCategoryByName($name, $id)) {
            $data['name_err'] = 'Category name already exists.';
        }

        // --- Image Upload ---
        $newImageFilename = null; // Assume no new image unless uploaded successfully
        $deleteOldImage = false;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // ... (Include the same image upload validation logic as storeCategory) ...
            // ... (Ensure you use CATEGORY_IMG_UPLOAD_DIR) ...
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 2 * 1024 * 1024;

            if (!in_array($fileExtension, $allowedfileExtensions)) {
                $data['image_err'] = 'Invalid file type...';
            } elseif ($_FILES['image']['size'] > $maxFileSize) {
                $data['image_err'] = 'File size exceeds 2MB limit.';
            } else {
                $newFileName = uniqid('cat_', true) . '.' . $fileExtension;
                $dest_path = CATEGORY_IMG_UPLOAD_DIR . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // New image uploaded successfully
                    $deleteOldImage = true; // Mark old one for deletion after DB update
                } else {
                    $data['image_err'] = 'Error uploading image (permissions?).';
                    $newFileName = null; // Reset filename on upload error
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
            $data['image_err'] = 'File upload error code: ' . $_FILES['image']['error'];
        }
        // --- End Image Upload ---

        // --- Process if No Errors ---
        if (empty($data['name_err']) && empty($data['image_err'])) {
            $categoryData = [
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'],
            ];
            // IMPORTANT: Only include image_path in update data IF a new image was successfully uploaded
            // Or if the user explicitly wants to remove the image (add a checkbox for this later?)
            if ($newFileName !== null) {
                $categoryData['image_path'] = $newFileName;
            } // Otherwise, the model's update query won't touch the image_path column


            if ($this->categoryModel->updateCategory($categoryData)) {
                // Delete old image *after* successful DB update if new one was uploaded
                if ($deleteOldImage && !empty($originalCategory->image_path) && $originalCategory->image_path != $newFileName) {
                    $oldImagePath = CATEGORY_IMG_UPLOAD_DIR . $originalCategory->image_path;
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath); // Use @ to suppress errors if file gone
                    }
                }
                // flash('success', 'Category updated successfully.', 'alert alert-success');
                $this->redirect('admin/manageCategories');
                return;
            } else {
                $data['general_err'] = 'Failed to update category (database error).';
                // Optionally delete newly uploaded file if DB update failed
                if ($newFileName && file_exists(CATEGORY_IMG_UPLOAD_DIR . $newFileName)) {
                    @unlink(CATEGORY_IMG_UPLOAD_DIR . $newFileName);
                }
                $this->view('admin/edit_category', $data); // Reload form with error
            }
        } else {
            // Validation errors, reload form
            $this->view('admin/edit_category', $data);
        }
    }

    // Delete a category (POST request)
    public function deleteCategory($id = 0)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/manageCategories');
            return;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            // flash('error', 'Invalid category ID.', 'alert alert-danger');
            $this->redirect('admin/manageCategories');
            return;
        }

        // Optional: Fetch category to get image path BEFORE deleting from DB
        $categoryToDelete = $this->categoryModel->getCategoryById($id);

        if ($this->categoryModel->deleteCategory($id)) {
            // Delete the image file AFTER successful DB deletion
            if ($categoryToDelete && !empty($categoryToDelete->image_path)) {
                $imagePath = CATEGORY_IMG_UPLOAD_DIR . $categoryToDelete->image_path;
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            // flash('success', 'Category deleted successfully.', 'alert alert-success');
        } else {
            // flash('error', 'Failed to delete category. Check if products are assigned to it.', 'alert alert-danger');
        }
        $this->redirect('admin/manageCategories');
    }
}
