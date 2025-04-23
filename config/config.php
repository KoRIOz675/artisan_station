<?php
define('BASE_URL', '/artisan_station/public/');
define('URLROOT', 'http://localhost/artisan_station');
define('EVENT_IMG_UPLOAD_DIR', dirname(__DIR__) . '/public/img/events/');
define('EVENT_IMG_URL_PREFIX', URLROOT . '/img/events/');
define('PRODUCT_IMG_UPLOAD_DIR', dirname(__DIR__) . '/public/img/products/');
define('PRODUCT_IMG_URL_PREFIX', URLROOT . '/img/products/');
define('CATEGORY_IMG_UPLOAD_DIR', dirname(__DIR__) . '/public/img/categories/');
define('CATEGORY_IMG_URL_PREFIX', URLROOT . '/img/categories/');
define('PROFILE_IMG_UPLOAD_DIR', dirname(__DIR__) . '/public/img/artists/');
define('PROFILE_IMG_URL_PREFIX', URLROOT . '/img/artists/');
define('CONTACT_FORM_RECIPIENT_EMAIL', 'thomas@mafille.net');

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
if (!is_dir(CATEGORY_IMG_UPLOAD_DIR)) {
    @mkdir(CATEGORY_IMG_UPLOAD_DIR, 0755, true);
}
if (!is_writable(CATEGORY_IMG_UPLOAD_DIR)) {
    error_log("Warning: Category image upload directory is not writable: " . CATEGORY_IMG_UPLOAD_DIR);
}
if (!is_dir(PROFILE_IMG_UPLOAD_DIR)) {
    @mkdir(PROFILE_IMG_UPLOAD_DIR, 0755, true);
}
if (!is_writable(PROFILE_IMG_UPLOAD_DIR)) {
    error_log("Warning: Profile image upload directory is not writable: " . PROFILE_IMG_UPLOAD_DIR);
}
