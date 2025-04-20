<?php

class AdminController extends Controller
{

    private $usersModel;
    private $eventModel;
    private $productModel;
    private $orderModel;

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
        if (!$this->usersModel  || !$this->productModel || !$this->eventModel) {
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
            // flash('error', 'You cannot change your own role.', 'alert alert-danger');
            $this->redirect('admin/manageUsers');
            return;
        }

        // 3. Attempt to update the role via the model
        if ($this->usersModel->updateUserRole($userId, $newRole)) {
            // flash('success', 'User role updated successfully.', 'alert alert-success');
        } else {
            // flash('error', 'Failed to update user role.', 'alert alert-danger');
        }

        // 4. Redirect back to the user management page
        $this->redirect('admin/manageUsers');
    }
}
