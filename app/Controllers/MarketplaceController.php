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
        $minPrice = filter_input(INPUT_GET, 'min_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $sortBy = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // e.g., 'price_asc', 'price_desc'

        // --- Prepare Filters for Model ---
        $filters = [];
        if ($categoryId && $categoryId > 0) {
            $filters['category_id'] = $categoryId;
        }
        if (!empty($searchTerm)) {
            $filters['search_term'] = $searchTerm;
        }
        if ($minPrice !== null && $minPrice !== false && is_numeric($minPrice) && $minPrice >= 0) {
            $filters['min_price'] = $minPrice;
        }
        if ($maxPrice !== null && $maxPrice !== false && is_numeric($maxPrice) && $maxPrice >= 0) {
            $filters['max_price'] = $maxPrice;
        }
        if (!empty($sortBy)) {
            $filters['sort_by'] = $sortBy;
        }

        // --- Fetch Data ---
        $products = $this->productModel->getFilteredActiveProducts($filters);
        $categories = $this->categoryModel->getAllCategories();

        // --- Prepare Data for View ---
        $data = [
            'title' => 'Marketplace',
            'cssFile' => 'marketplace.css',
            'products' => $products,
            'categories' => $categories,
            // Pass active filters back to view to repopulate form
            'active_category_id' => $categoryId ?: '',
            'active_search_term' => $searchTerm,
            'active_min_price' => $minPrice !== null && $minPrice !== false ? $minPrice : '',
            'active_max_price' => $maxPrice !== null && $maxPrice !== false ? $maxPrice : '',
            'active_sort_by' => $sortBy ?: ''
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
}
