<?php
// db.php - database connection

$host   = getenv('DB_HOST') ?: 'localhost';
$user   = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: 'root');
$pass   = getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: '');
$dbname = getenv('DB_NAME') ?: 'ecommerce_db';
$port   = (int)(getenv('DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $dbname, $port);
    mysqli_set_charset($conn, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("DB connection failed: " . $e->getMessage());
    die("Sorry, we couldn't connect to the database. Please try again later.");
}