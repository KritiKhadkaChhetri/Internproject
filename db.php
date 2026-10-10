<?php
// db.php - database connection

$host = "localhost";
$user = "root";          // XAMPP default user
$pass = "";              // XAMPP default password is empty
$dbname = "ecommerce_db";

// Make mysqli throw errors we can catch, instead of silent failures
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $dbname);
    mysqli_set_charset($conn, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Don't show technical details to visitors
    error_log("DB connection failed: " . $e->getMessage());
    die("Sorry, we couldn't connect to the database. Please try again later.");
}