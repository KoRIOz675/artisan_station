<?php
// File: app/Controllers/HomeController.php

class HomeController extends Controller
{

    private $productModel; // Declare properties
    private $userModel;    // Use userModel consistently
    private $eventModel;    // Declare eventModel

    public function __construct()
    {
        // Load models
        $this->productModel = $this->model('Product');
        $this->userModel = $this->model('Users');
        $this->eventModel = $this->model('Event');
    }

    public function index()
    {
        $allFeaturedArtisans = $this->userModel->getFeaturedArtisans();
        $featuredArtisan = null; // Initialize
        $featuredArtisanProducts = []; // Initialize

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
            // 'artisans' => $allFeaturedArtisans, // We might not need ALL featured artisans anymore unless displayed elsewhere
        ];

        $this->view('home/index', $data);
    }
}
