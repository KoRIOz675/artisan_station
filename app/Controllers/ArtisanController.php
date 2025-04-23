<?php

class ArtisanController extends Controller
{

    private $usersModel;
    private $productModel;

    public function __construct()
    {
        // Corrected model loading names based on your UsersController example
        $this->usersModel = $this->model('Users'); // Use 'Users' model name
        $this->productModel = $this->model('Product');

        if (!$this->usersModel || !$this->productModel) {
            die("Error loading artisan/product resources.");
        }
    }
    public function index()
    {
        $artisans = $this->usersModel->getArtisans();
        $data = [
            'title' => 'Our Artisans',
            'artisans' => $artisans
        ];
        // This view needs to be created: app/Views/artisans/index.php
        $this->view('artisans/index', $data);
    }

    // Show artisan profile and their products
    public function show($username = '')
    {
        $cleanUsername = filter_var(trim($username), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (empty($cleanUsername)) {
            $this->redirect('artisans'); // Redirect to the main artisan list if no username
            return;
        }

        // 1. Fetch Artisan Data by Username
        $artisan = $this->usersModel->getActiveArtisanByUsername($cleanUsername);

        // Check if artisan was found and is valid
        if (!$artisan || !is_object($artisan)) {
            error_log("Active artisan profile not found for username: {$cleanUsername}");
            $this->redirect('artisans');
            return;
        }

        // 2. Fetch Products for this Artisan
        $products = $this->productModel->getActiveProductsByArtisanId($artisan->id);

        // 3. Prepare Data for the View
        $data = [
            'title' => htmlspecialchars($artisan->shop_name ?? $artisan->username),
            'cssFile' => 'artisan-profile.css',
            'artisan' => $artisan,
            'products' => $products
        ];

        // 4. Load the View (ensure this path is correct)
        $this->view('artisans/show', $data);
    }

    // Method for artisans to manage their own profile (linked from header/dashboard)
    public function profile()
    {
        // Check if user is logged in and is an artisan
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
            $this->redirect('users/loginRegister'); // Redirect to main login
            return;
        }
        // $artisanId = $_SESSION['user_id'];
        $this->redirect('users/dashboard');

        /* Example if loading a dedicated view:
        $artisan = $this->usersModel->getUserById($artisanId);
        $products = $this->productModel->getActiveProductsByArtisanId($artisanId);
         $data = [
             'title' => 'Manage My Profile & Art',
             'artisan' => $artisan,
             'products' => $products
         ];
         $this->view('artisans/profile_manage', $data); // Need this view
        */
    }
}
