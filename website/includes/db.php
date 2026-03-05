<?php

require_once __DIR__ . '/config.php';

/**
 * PDO bağlantısını döndürür (singleton).
 *
 * @return PDO
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die('DB connection failed: ' . $e->getMessage());
            }
            error_log('DB connection failed: ' . $e->getMessage());
            die('Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.');
        }
    }

    return $pdo;
}

