<?php
$hostname = getenv("DB_HOST");
$username = getenv("DB_USER");
$password = getenv("DB_PASS");
$dbname   = getenv("DB_NAME");
$port     = getenv("DB_PORT") ?: 3306;

$conn = mysqli_connect($hostname, $username, $password, $dbname, $port);

if (!$conn) {
    die("Database connection error");
}
?>
