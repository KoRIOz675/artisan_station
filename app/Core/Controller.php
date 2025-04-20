<?php

abstract class Controller
{

    // Load model
    public function model($modelName)
    {
        // Require model file
        $modelFile = APPROOT . '/Models/' . $modelName . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            // Instantiate model
            return new $modelName();
        } else {
            // Model does not exist
            die('Model ' . $modelName . ' does not exist.');
        }
    }

    // Load view
    public function view($viewName, $data = [])
    {
        // Construct view path
        $viewFile = APPROOT . '/Views/' . $viewName . '.php';
        $headerFile = APPROOT . '/Views/layouts/header.php';
        $footerFile = APPROOT . '/Views/layouts/footer.php';
        // Check for view file
        if (file_exists($viewFile)) {
            // Extract data to variables for the view
            extract($data);
            // Require view file within output buffer
            ob_start();

            if (file_exists($headerFile)) {
                require_once $headerFile; // Include the header
            } else {
                // Header is missing - Stop execution cleanly
                error_log("Error: Layout header file not found at " . $headerFile); // Log error
                die('Error: Website layout is incomplete (header missing). Please contact support.'); // User-friendly message
            }

            require $viewFile;
            $content = ob_get_clean();
            echo $content; // Or return content if using a template engine

            if (file_exists($footerFile)) {
                require_once $footerFile; // Include the footer
            } else {
                // Footer is missing - Stop execution cleanly
                error_log("Error: Layout footer file not found at " . $footerFile); // Log error
                die('Error: Website layout is incomplete (footer missing). Please contact support.'); // User-friendly message
            }
        } else {
            // View does not exist
            die('View ' . $viewName . ' does not exist.');
        }
    }

    // Simple redirect helper
    // Inside app/Core/Controller.php
    public function redirect($location)
    {
        // Trim leading slash from $location just in case
        $location = ltrim($location, '/');
        // Ensure URLROOT is defined
        if (!defined('URLROOT')) {
            error_log("Redirect Error: URLROOT constant is not defined.");
            die("Configuration error: Cannot redirect.");
        }
        // THIS is the line that builds the final URL:
        header('Location: ' . URLROOT . '/' . $location); // It explicitly adds '/'
        exit();
    }
}
