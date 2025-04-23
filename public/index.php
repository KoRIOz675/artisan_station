<?php
// --- Error Reporting ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Configuration ---
require_once '../config/config.php';
require_once '../config/database.php';

// --- Application Root Path ---
define('APPROOT', dirname(__DIR__) . '/app');

// --- Autoloader ---
spl_autoload_register(function ($className) {
    $paths = [
        APPROOT . '/Core/',
        APPROOT . '/Controllers/',
        APPROOT . '/Models/'
    ];
    $className = str_replace('\\', '/', $className);
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    // Keep error log for debugging non-found classes
    error_log("Autoloader Error: Class file not found for class: " . $className);
});

// --- Load Helpers ---
// Ensure session_helper is loaded if you use flash messages
require_once APPROOT . '/helpers/session_helper.php';


// --- Session Start ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Routing ---
$controllerName = 'HomeController'; // Default Controller
$methodName = 'index';             // Default Method
$params = [];
$routeFound = false; // Flag for specific routes

// Parse the URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$urlParts = !empty($url) ? explode('/', filter_var($url, FILTER_SANITIZE_URL)) : [];

// --- 1. Check for Specific Routes ---

// Pattern: /artisans/{username}/products/{slug}
if (
    isset($urlParts[0]) && $urlParts[0] == 'artisans' &&
    isset($urlParts[1]) && // username
    isset($urlParts[2]) && $urlParts[2] == 'products' &&
    isset($urlParts[3])
) { // slug

    $controllerName = 'ProductsController';      // Explicitly set Controller
    $methodName = 'showByArtisanAndSlug';       // Explicitly set Method
    $params = [$urlParts[1], $urlParts[3]];     // Extract username and slug as params
    $routeFound = true;                         // Mark route as found
    // echo "<script>console.log('Route matched: Artisan Product Page');</script>"; // Debug
}
// Pattern: /artisans/{username}
elseif (isset($urlParts[0]) && $urlParts[0] == 'artisans' && isset($urlParts[1])) {
    $controllerName = 'ArtisanController'; // <-- POINT TO ArtisanController
    $methodName = 'show';                 // <-- Use the 'show' method
    $params = [$urlParts[1]];             // Pass the username
    $routeFound = true;
}
// Pattern: /marketplace/category/{slug}
elseif (
    isset($urlParts[0]) && $urlParts[0] == 'marketplace' &&
    isset($urlParts[1]) && $urlParts[1] == 'category' &&
    isset($urlParts[2])
) { // slug

    $controllerName = 'MarketplaceController';  // Explicitly set Controller
    $methodName = 'category';                   // Explicitly set Method
    $params = [$urlParts[2]];                   // Extract slug as param
    $routeFound = true;                         // Mark route as found
    // echo "<script>console.log('Route matched: Marketplace Category');</script>"; // Debug
}

// --- 2. Generic Route Handling (if no specific route matched) ---
if (!$routeFound) {
    // --- Determine Controller ---
    if (!empty($urlParts[0])) {
        $controllerSlug = strtolower($urlParts[0]);
        $potentialController = '';

        // Explicit Controller Mapping (More reliable than generic ucfirst)
        if ($controllerSlug == 'users') {
            $potentialController = 'UsersController';
        } elseif ($controllerSlug == 'admin') {
            $potentialController = 'AdminController';
        } elseif ($controllerSlug == 'events') {
            $potentialController = 'EventsController';
        } elseif ($controllerSlug == 'products') {
            $potentialController = 'ProductsController';
        } elseif ($controllerSlug == 'marketplace') {
            $potentialController = 'MarketplaceController';
        } elseif ($controllerSlug == 'artisans') {
            $potentialController = 'ArtisanController';
        } elseif ($controllerSlug == 'contact') {
            $potentialController = 'ContactController';
        } else {
            // If slug doesn't match known controllers, maybe it's a 404
            // Or fallback to default if appropriate for your structure
            // For now, let it try generic - but this might fail if class name differs
            $potentialController = ucfirst($controllerSlug) . 'Controller';
            error_log("Generic controller lookup for slug: " . $controllerSlug . " -> " . $potentialController);
        }

        $controllerPath = APPROOT . '/Controllers/' . $potentialController . '.php';

        if (!empty($potentialController) && file_exists($controllerPath)) {
            $controllerName = $potentialController;
            unset($urlParts[0]); // Remove controller part
            // echo "<script>console.log('Generic Route: Controller Found: " . $controllerName . "');</script>"; // Debug
        } else {
            // Controller file not found for this slug - Treat as 404
            error_log("Controller file not found for slug '{$controllerSlug}' at path: {$controllerPath}");
            // TODO: Implement a proper 404 handler/controller
            $controllerName = 'PagesController'; // Example: Assuming a Pages controller handles errors
            $methodName = 'notFound';
            $params = [];
            $routeFound = true; // Mark as handled (by 404)
        }
    } else {
        // No controller specified, use default HomeController
        $controllerName = 'HomeController';
        $methodName = 'index';
        // echo "<script>console.log('Generic Route: No controller specified, using default.');</script>"; // Debug
    }

    // --- Determine Method (Only if route wasn't handled by 404 above) ---
    if (!$routeFound && isset($urlParts[1])) {
        // Use the exact case from URL part, controller methods are case-insensitive in PHP by default
        // but check existence using the case provided if needed
        $potentialMethod = $urlParts[1];
        // We need to instantiate the controller *first* to check method_exists accurately
        // Temporarily store potential method, instantiate, then check.
        unset($urlParts[1]); // Remove method part for now
    } // Default method 'index' is already set

    // --- Get URL Parameters ---
    // Remaining parts of the URL are parameters (after controller/method removed)
    // If specific route was found earlier, $params is already set correctly
    if (!$routeFound) {
        $params = $urlParts ? array_values($urlParts) : [];
    }
} // End if (!$routeFound) for generic routing


// --- 3. Instantiate Controller ---
$controllerInstance = null; // Initialize
if (class_exists($controllerName)) {
    try {
        $controllerInstance = new $controllerName();
        // echo "<script>console.log('Controller " . $controllerName . " instantiated.');</script>"; // Debug
    } catch (Throwable $e) { // Catch potential errors during instantiation
        error_log("Error instantiating controller '{$controllerName}': " . $e->getMessage());
        // TODO: Implement 500 error page
        die("Error loading application controller.");
    }
} else {
    error_log("Routing Error: Final Controller class '{$controllerName}' not found.");
    // TODO: Implement 404 handler
    die("Error: Application page not found (Controller Missing).");
}

// --- 4. Refine Method Name based on instance (if generic route) ---
// Stored potentialMethod from generic routing step above
if (isset($potentialMethod) && $controllerInstance && method_exists($controllerInstance, $potentialMethod)) {
    $methodName = $potentialMethod;
    // echo "<script>console.log('Generic Route: Method Found: " . $methodName . "');</script>"; // Debug
} elseif (isset($potentialMethod) && $controllerInstance && !method_exists($controllerInstance, $potentialMethod)) {
    // Method doesn't exist in the chosen controller - Treat as 404
    error_log("Method '{$potentialMethod}' not found in controller '{$controllerName}'.");
    // TODO: Implement 404 handler
    // For now, maybe default to index if it exists? Or die?
    if (method_exists($controllerInstance, 'index')) {
        $methodName = 'index';
        $params = []; // Reset params if falling back to index
    } else {
        die("Error: Application action not found (Method Missing).");
    }
}
// If $potentialMethod wasn't set (e.g., specific route or no method in URL), $methodName retains its value ('index' or from specific route)


// --- 5. Dispatch Request ---
if ($controllerInstance && method_exists($controllerInstance, $methodName)) {
    try {
        // echo "<script>console.log('Dispatching: Controller=" . get_class($controllerInstance) . ", Method=" . $methodName . ", Params=" . json_encode($params) . "');</script>"; // Debug
        // Call the controller's method with the parameters
        call_user_func_array([$controllerInstance, $methodName], $params);
    } catch (Exception $e) {
        error_log("Dispatch Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        // TODO: Implement 500 error page
        echo "An application error occurred during dispatch.";
    }
} else {
    // Fallback if method still doesn't exist after all checks (shouldn't happen ideally)
    error_log("Dispatch Error: Invalid controller or method just before call. Controller: " . ($controllerInstance ? get_class($controllerInstance) : 'null') . ", Method: " . $methodName);
    // TODO: Implement 404 or 500 error page
    die("Error: Unable to process request.");
}
