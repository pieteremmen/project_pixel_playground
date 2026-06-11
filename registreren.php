<?php
$melding = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli("localhost", "root", "", "mypixelplayground");

        $gebruikersnaam = trim($_POST["gebruikersnaam"]);
        $wachtwoord = $_POST["wachtwoord"];
        $confirm = $_POST["conformatie_wachtwoord"];

        if ($wachtwoord !== $confirm) {
            $melding = "Wachtwoorden komen niet overeen!";
        } else {

            // ❌ geen hash meer
            $stmt = $conn->prepare("INSERT INTO gebruikers (gebruikersnaam, wachtwoord) VALUES (?, ?)");
            $stmt->bind_param("ss", $gebruikersnaam, $wachtwoord);
            $stmt->execute();

           $melding = "<p class='succes_registratie'>Account opgeslagen!</p>";

            
        }
    } catch (mysqli_sql_exception $e) {
        if (str_contains($e->getMessage(), "Duplicate entry")) {
            $melding = "Deze gebruikersnaam bestaat al!";
        } else {
            $melding = "Database fout: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
     <meta name="description" content="registreren pagina waar je een account kan aanmaken.">
    <meta name="keywords" content="registreren, wachtwoord, account aanmaken, gebruikersnaam">
    <meta name="Yassine">
    <title>Registreren</title>
    <script src="registreren.js" defer></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
session_start();
?>


<?php include 'header.php'; ?>

<section class="registratie-pagina">

<p><?php echo $melding; ?></p>

<h1>Registreren</h1>

<form id="registerForm" action="" method="post">
    <label for="gebruikersnaam">Gebruikersnaam:</label><br>
    <input type="text" id="gebruikersnaam" name="gebruikersnaam" required><br><br>

    <label for="wachtwoord">Wachtwoord:</label><br>
    <input type="password" id="wachtwoord" name="wachtwoord" required><br><br>

    <label for="conformatie_wachtwoord">Confirm Wachtwoord:</label><br>
    <input type="password" id="conformatie_wachtwoord" name="conformatie_wachtwoord" required><br><br>

    <input type="submit" value="Register">
</form>

</section>

</body>
</html>