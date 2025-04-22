<?php
define('BASE_URL', '/artisan_station/public/');
define('URLROOT', 'http://localhost/artisan_station');
define('EVENT_IMG_UPLOAD_DIR', dirname(__DIR__) . '/public/img/events/');
define('EVENT_IMG_URL_PREFIX', URLROOT . '/img/events/');
define('PRODUCT_IMG_UPLOAD_DIR', dirname(__DIR__) . '/public/img/products/');
define('PRODUCT_IMG_URL_PREFIX', URLROOT . '/img/products/');

if (!is_dir(EVENT_IMG_UPLOAD_DIR)) {
    mkdir(EVENT_IMG_UPLOAD_DIR, 0755, true);
}
if (!is_writable(EVENT_IMG_UPLOAD_DIR)) {
    error_log("Warning: Event image upload directory is not writable: " . EVENT_IMG_UPLOAD_DIR);
}
if (!is_dir(PRODUCT_IMG_UPLOAD_DIR)) {
    @mkdir(PRODUCT_IMG_UPLOAD_DIR, 0755, true);
}
if (!is_writable(PRODUCT_IMG_UPLOAD_DIR)) {
    error_log("Warning: Product image upload directory is not writable: " . PRODUCT_IMG_UPLOAD_DIR);
}
