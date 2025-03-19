<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Session configuration
session_start();

// Database configuration
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

// Pusher configuration for real-time chat
define('PUSHER_APP_ID', $_ENV['PUSHER_APP_ID']);
define('PUSHER_KEY', $_ENV['PUSHER_KEY']);
define('PUSHER_SECRET', $_ENV['PUSHER_SECRET']);
define('PUSHER_CLUSTER', $_ENV['PUSHER_CLUSTER']);

// JWT configuration
define('JWT_SECRET', $_ENV['JWT_SECRET']);
define('JWT_EXPIRATION', $_ENV['JWT_EXPIRATION']);

// Site configuration
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/PetQuest');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/PetQuest/uploads');
define('QR_PATH', UPLOAD_PATH . '/qrcodes');
define('PETS_PATH', UPLOAD_PATH . '/pets');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
    mkdir(QR_PATH, 0777, true);
    mkdir(PETS_PATH, 0777, true);
}

// Error reporting based on environment
if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Initialize Pusher
$pusher = new Pusher\Pusher(
    PUSHER_KEY,
    PUSHER_SECRET,
    PUSHER_APP_ID,
    [
        'cluster' => PUSHER_CLUSTER,
        'useTLS' => true
    ]
); 