<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
     <meta name="description" content="wanneer je bent ingelogd kan je uitloggen.">
    <meta name="keywords" content="uitloggen, sessie, login, account">
     <meta name="Pieter">
    <title>uitloggen</title>
</head>
<body>

<?php
session_start();

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>
    
</body>
</html>