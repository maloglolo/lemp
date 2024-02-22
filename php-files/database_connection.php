<?php
mysqli_report(MYSQLI_REPORT_OFF);

require __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get database connection details from environment variables
$host = $_ENV["DATABASE_HOSTNAME"];
$dbname = $_ENV["DATABASE_NAME"];
$username = $_ENV["DATABASE_USERNAME"];
$password = $_ENV["DATABASE_PASSWORD"];

// Establish MySQLi database connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;