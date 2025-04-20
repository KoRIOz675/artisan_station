<?php

class ArtisanController extends Controller {

    private $userModel;
    private $productModel;

     public function __construct() {
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product');
         if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // List all artisans
    public function index() {
        $artisans = $this->userModel->getArtisans();
         $data = [
            'title' => 'Our Artisans',
            'artisans' => $artisans
        ];
        $this->view('artisans/index', $data); // Needs view: Views/artisans/index.php
    }

    // Show artisan profile and their products
    public function show($id) {
        $artisan = $this->userModel->findUserById($id);

        // Ensure the user is actually an artisan
        if (!$artisan || $artisan['role'] !== 'artisan') {
            // Handle not found or not an artisan
            $this->redirect('artisans');
            return;
        }

        $products = $this->productModel->getProductsByArtisan($id);

        $data = [
            'title' => htmlspecialchars($artisan['shop_name'] ?? $artisan['name']),
            'artisan' => $artisan,
            'products' => $products
        ];
         $this->view('artisans/show', $data); // Needs view: Views/artisans/show.php
    }

    // Method for artisans to manage their own profile (linked from header/dashboard)
    public function profile() {
         // Check if user is logged in and is an artisan
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
             $this->redirect('users/login');
            return;
        }
        $artisanId = $_SESSION['user_id'];
        // Fetch artisan data and products
        // Similar to show() but uses session ID
        // ...
        // Load a profile management view (e.g., artisans/profile.php)
    }
}