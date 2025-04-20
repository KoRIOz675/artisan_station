<?php
define('BASE_URL', '/AppWebSite/public/');
define('URLROOT', 'http://localhost/AppWebSite');
define('EVENT_IMG_UPLOAD_DIR', dirname(__DIR__) . '/public/img/events/');
define('EVENT_IMG_URL_PREFIX', URLROOT . '/img/events/');

if (!is_dir(EVENT_IMG_UPLOAD_DIR)) {
    mkdir(EVENT_IMG_UPLOAD_DIR, 0755, true); // Attempt to create if not exists
}
if (!is_writable(EVENT_IMG_UPLOAD_DIR)) {
    // Log an error or display a warning during development
    error_log("Warning: Event image upload directory is not writable: " . EVENT_IMG_UPLOAD_DIR);
    // In production, you might handle this more gracefully
}

?>
