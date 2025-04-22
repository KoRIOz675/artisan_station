<?php
// --- Error Reporting  ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Configuration ---
require_once '../config/config.php';      // Load main config (URLROOT, DB Credentials, etc.) FIRST
require_once '../config/database.php';    // Load DB class definition or specific DB setup AFTER config

// --- Application Root Path ---
// APPROOT points to the 'app' folder, needed for includes/requires
define('APPROOT', dirname(__DIR__) . '/app');

// --- Autoloader ---
// Simple autoloader - Handles loading Core, Controllers, Models
spl_autoload_register(function ($className) {
    // Define possible directories for classes
    $paths = [
        APPROOT . '/Core/',       // Use APPROOT for server paths
        APPROOT . '/Controllers/',
        APPROOT . '/Models/'
    ];
    // Convert Namespace backslashes if you start using them
    $className = str_replace('\\', '/', $className);

    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return; // Stop searching once found
        } else {
            // Optional: Log or handle the error if needed
            error_log("Autoloader Error: Class file not found at " . $file);
            // echo "<script>console.log('Autoloader Error: Class file not found at " . $file . "');</script>";
        }
    }
});

// --- Load Helpers ---
require_once APPROOT . '/helpers/session_helper.php';

// --- Session Start (after class loading is possible) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Routing ---
$controllerName = 'HomeController'; // Default Controller
$methodName = 'index';             // Default Method
$params = [];

// Parse the URL from ?url= variable (set by .htaccess)
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$urlParts = !empty($url) ? explode('/', filter_var($url, FILTER_SANITIZE_URL)) : [];

// --- Determine Controller ---
if (!empty($urlParts[0])) {
    // Format: controller name should be like 'Users' -> 'UsersController.php'
    $potentialController = ucfirst(strtolower($urlParts[0])) . 'Controller';
    $controllerPath = APPROOT . '/Controllers/' . $potentialController . '.php';

    // Debug
    echo "<script>console.log('URL Part 0: " . ($urlParts[0] ?? 'null') . "');</script>";
    echo "<script>console.log('Potential Controller: " . $potentialController . "');</script>";
    echo "<script>console.log('Controller Path: " . $controllerPath . "');</script>";

    if (file_exists($controllerPath)) {
        $controllerName = $potentialController;
        echo "<script>console.log('Controller Found! Switched to: " . $controllerName . "');</script>";
        unset($urlParts[0]); // Remove controller part from URL parts
    } else {
        echo "<script>console.log('Controller file NOT found. Staying with default: " . $controllerName . "');</script>";
        // Consider implementing a dedicated 404 handler here
    }
} else {
    echo "<script>console.log('No URL parts for controller. Using default: " . $controllerName . "');</script>"; // Added log for homepage case
}

// --- Instantiate Controller ---
// Autoloader should handle loading the class file
if (class_exists($controllerName)) {
    $controllerInstance = new $controllerName(); // Instantiates HomeController, UserController etc.
    echo "<script>console.log('Controller " . $controllerName . " instantiated.');</script>";
} else {
    // Handle Controller class not found error (even if file exists, class name might be wrong)
    // die("Error: Controller class '{$controllerName}' not found. Check filename and class declaration.");
    echo "<script>console.log('Routing Error: Controller class " . $controllerName . " not found.');</script>";
    // Replace die with a proper 404 page later
}

// --- Determine Method ---
// Checks if the second part of the URL maps to a method in the controller
if (isset($urlParts[1])) {
    // Format: method name should be like 'loginRegister'
    $potentialMethod = strtolower($urlParts[1]); // Method names are case-insensitive, but keep consistent
    // Check if method exists in the controller instance (use method_exists)
    if (method_exists($controllerInstance, $potentialMethod)) {
        $methodName = $potentialMethod; // Use the matched method name
        unset($urlParts[1]); // Remove method part from URL parts
    } else {
        // Debug
        echo "<script>console.log('Routing Error: Method " . $potentialMethod . " not found in controller " . $controllerName . ".' );</script>";
    }
}

// --- Get URL Parameters ---
// Remaining parts of the URL are parameters
$params = $urlParts ? array_values($urlParts) : [];

// --- Dispatch Request ---
try {
    // Call the determined controller's method with the parameters
    // Example: call $userControllerInstance->loginRegister() with $params=[]
    // Example: call $productControllerInstance->show() with $params=['product-slug']
    call_user_func_array([$controllerInstance, $methodName], $params);
} catch (Exception $e) {
    // Basic exception handling (replace with proper logging/error pages)
    echo "An application error occurred: " . $e->getMessage();
    error_log("Dispatch Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    // Show a user-friendly error page in production
}
