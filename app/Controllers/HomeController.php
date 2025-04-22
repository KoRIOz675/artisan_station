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
        $categories = $this->categoryModel->getAllCategories();

        // Select the first one found as 'Artisan of the Week'
        if (!empty($allFeaturedArtisans)) {
            $featuredArtisan = $allFeaturedArtisans[0];
        }
        // --- End Handle Featured Artisan ---

        $upcomingEvents = $this->eventModel->getUpcomingActiveEvents(3);
        $featuredProducts = $this->productModel->getFeaturedActiveProducts(6);

        echo "<script>console.log('Products received from model: " . count($featuredProducts) . " items.');</script>";

        // Prepare data for the view
        $data = [
            'title' => 'Welcome to Artisan Station',
            'description' => 'For Artists, by Artists',
            'cssFile' => 'home.css',
            'featuredArtisan' => $featuredArtisan,
            'products' => $featuredProducts,
            'events' => $upcomingEvents,
            'categories' => $categories,
        ];

        echo "<script>console.log('Data passed to view : " . $data['products'] . "');</script>";

        $this->view('home/index', $data);
    }
}
