<?php
// app/Controllers/MarketplaceController.php

class MarketplaceController extends Controller
{

    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');

        if (!$this->productModel || !$this->categoryModel) {
            die("Error loading marketplace resources.");
        }
        // No login check needed for public marketplace view
    }

    /**
     * Main marketplace display method.
     * Handles GET parameters for filtering and search.
     */
    public function index()
    {
        // --- Get Filters/Search from GET Request ---
        $categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
        $searchTerm = trim(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');

        // --- Prepare Filters for Model ---
        $filters = [];
        if ($categoryId && $categoryId > 0) {
            $filters['category_id'] = $categoryId;
        }
        if (!empty($searchTerm)) {
            $filters['search_term'] = $searchTerm;
        }
        // Add status filter (already handled in model query)
        // $filters['status'] = 'active';

        // --- Fetch Data ---
        $products = $this->productModel->getFilteredActiveProducts($filters);
        $categories = $this->categoryModel->getAllCategories(); // For the filter dropdown

        // --- Prepare Data for View ---
        $data = [
            'title' => 'Marketplace',
            'cssFile' => 'marketplace.css', // Optional specific CSS
            'products' => $products,
            'categories' => $categories,
            'active_category_id' => $categoryId ?: '', // Pass active filter back to view
            'active_search_term' => $searchTerm // Pass search term back to view
        ];

        $this->view('marketplace/index', $data);
    }

    /**
     * Handles requests coming from category links (e.g., /marketplace/category/woodworking).
     * Fetches the category ID by slug and redirects to index() with GET parameter.
     */
    public function category($slug = '')
    {
        if (empty($slug)) {
            $this->redirect('marketplace'); // Redirect if no slug
            return;
        }

        $cleanSlug = filter_var($slug, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $category = $this->categoryModel->getCategoryBySlug($cleanSlug);

        if ($category && isset($category->id)) {
            // Redirect to the index method with the category ID as a GET parameter
            $this->redirect('marketplace?category=' . $category->id);
        } else {
            // Category slug not found, redirect to general marketplace
            // flash('error', 'Category not found.', 'alert alert-warning');
            $this->redirect('marketplace');
        }
    }
} // End Class
