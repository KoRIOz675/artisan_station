<?php
// app/Controllers/PagesController.php

class PagesController extends Controller
{

    public function __construct()
    {
        // No models needed for these static pages
        // Session started in index.php
    }

    /**
     * Displays the default page or handles errors if needed.
     * For now, maybe redirects home if accessed directly.
     */
    public function index()
    {
        $this->redirect(''); // Redirect to homepage
    }

    /**
     * Displays the FAQ page.
     */
    public function faq()
    {
        $data = [
            'title' => 'Frequently Asked Questions (FAQ)',
            'cssFile' => 'static-page.css' // Optional: common CSS for static pages
        ];
        $this->view('pages/faq', $data);
    }

    /**
     * Displays the Terms & Conditions page.
     */
    public function terms()
    {
        $data = [
            'title' => 'Terms & Conditions',
            'cssFile' => 'static-page.css'
        ];
        $this->view('pages/terms', $data);
    }

    /**
     * Displays the Privacy Policy page.
     */
    public function privacy()
    {
        $data = [
            'title' => 'Privacy Policy',
            'cssFile' => 'static-page.css'
        ];
        $this->view('pages/privacy', $data);
    }

    /**
     * Handle Not Found errors.
     * The router can be configured to call this if no other route matches.
     */
    public function notFound()
    {
        $data = [
            'title' => 'Page Not Found (404)',
            'cssFile' => 'static-page.css'
        ];
        // Set response code
        http_response_code(404);
        $this->view('pages/404', $data); // Create this view
    }
} // End Class
