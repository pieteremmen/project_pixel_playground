<?php
$host = "localhost";
$dbname = "mypixelplayground";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database verbinding mislukt: " . $e->getMessage());
}
?>

<!-- gemaakt door Yassine  -->
