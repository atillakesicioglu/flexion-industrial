<?php

// Genel uygulama ayarları
define('APP_NAME', 'Flexion Industrial');
define('APP_ENV', 'production'); // development | production

// Veritabanı bağlantı ayarları
define('DB_HOST', '');
define('DB_NAME', '');
define('DB_USER', '');      // phpMyAdmin kullanıcına göre değiştir
define('DB_PASS', '');          // phpMyAdmin şifrene göre değiştir
define('DB_CHARSET', 'utf8mb4');

// URL ve path ayarları (gerekiyorsa base URL buradan ayarlanabilir)
// Örn: define('BASE_URL', 'http://localhost/flexion/website/');
define('BASE_URL', '');

// Session ayarları
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('flexion_admin');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata gösterim ayarları
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}

