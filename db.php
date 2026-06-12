<?php
$host = "localhost";
$dbname = "mypixelplayground";
$username = "root";
$password = "";

$conn = null;


try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $db_error = $e->getMessage();
    $conn = null;
}
?>

<!-- gemaakt door Yassine  -->
