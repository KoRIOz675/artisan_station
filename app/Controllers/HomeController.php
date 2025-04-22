<?php
// File: app/Controllers/HomeController.php

class HomeController extends Controller
{

    private $productModel; // Declare productModel
    private $userModel;    // Declare userModel
    private $eventModel;    // Declare eventModel
    private $categoryModel; // Declare categoryModel

    public function __construct()
    {
        // Load models
        $this->productModel = $this->model('Product');
        $this->userModel = $this->model('Users');
        $this->eventModel = $this->model('Event');
        $this->categoryModel = $this->model('Category');
        if (!$this->productModel || !$this->userModel || !$this->eventModel || !$this->categoryModel) {
            die("Error loading core models.");
        }
    }

    public function index()
    {
        $allFeaturedArtisans = $this->userModel->getFeaturedArtisans();
        $featuredArtisan = null; // Initialize
        $featuredArtisanProducts = []; // Initialize
        $categories = $this->categoryModel->getAllCategories();

        // Select the first one found as 'Artisan of the Week'
        if (!empty($allFeaturedArtisans)) {
            $featuredArtisan = $allFeaturedArtisans[0]; // Get the first object

            // Now fetch some products for this specific artisan (e.g., limit 5)
            if ($featuredArtisan && isset($featuredArtisan->id)) {
                $featuredArtisanProducts = $this->productModel->getActiveProductsByArtisanId($featuredArtisan->id, 5);
            }
        }
        // --- End Handle Featured Artisan ---


        $featuredProducts = []; // General featured products (placeholder)
        $upcomingEvents = $this->eventModel->getUpcomingActiveEvents(3);

        // Prepare data for the view
        $data = [
            'title' => 'Welcome to Artisan Station',
            'description' => 'For Artists, by Artists',
            'cssFile' => 'home.css',
            'featuredArtisan' => $featuredArtisan,           // Pass the single featured artisan object (or null)
            'featuredArtisanProducts' => $featuredArtisanProducts, // Pass their products
            'products' => $featuredProducts,
            'events' => $upcomingEvents,
            'categories' => $categories,
        ];

        $this->view('home/index', $data);
    }
}
