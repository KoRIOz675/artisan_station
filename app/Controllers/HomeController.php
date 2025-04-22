<?php
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
        $featuredProducts = []; // Initialize

        // Select the first one found as 'Artisan of the Week'
        if (!empty($allFeaturedArtisans)) {
            $featuredArtisan = $allFeaturedArtisans[0];
        }
        // --- End Handle Featured Artisan ---

        $upcomingEvents = $this->eventModel->getUpcomingActiveEvents(3);
        $featuredProducts = $this->productModel->getFeaturedActiveProducts(6);
        $artOfTheWeek = $this->productModel->getArtOfTheWeek();
        $categories = $this->categoryModel->getAllCategories();

        // Prepare data for the view
        $data = [
            'title' => 'Welcome to Artisan Station',
            'description' => 'For Artists, by Artists',
            'cssFile' => 'home.css',
            'featuredArtisan' => $featuredArtisan,
            'artOfTheWeek' => $artOfTheWeek,
            'products' => $featuredProducts,
            'events' => $upcomingEvents,
            'categories' => $categories,
        ];

        $this->view('home/index', $data);
    }
}
