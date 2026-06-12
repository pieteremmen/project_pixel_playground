
<?php
session_start();
?>


<?php


$melding = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli("localhost", "root", "", "mypixelplayground");

        $gebruikersnaam = trim($_POST["gebruikersnaam"]);
        $wachtwoord = $_POST["wachtwoord"];

        $stmt = $conn->prepare("SELECT wachtwoord FROM gebruikers WHERE gebruikersnaam = ?");
        $stmt->bind_param("s", $gebruikersnaam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $melding = "<p style='color:red;'>Ongeldige gebruikersnaam of wachtwoord!</p>";
        } else {
            $row = $result->fetch_assoc();
            $opgeslagenWachtwoord = $row["wachtwoord"];

            if ($wachtwoord === $opgeslagenWachtwoord) {
                $_SESSION["ingelogd"] = true;
                $_SESSION["gebruikersnaam"] = $gebruikersnaam;

                $melding = "<p style='color:green;'>✅ Inloggen gelukt!</p>";
            } else {
                $melding = "<p style='color:red;'>Ongeldige gebruikersnaam of wachtwoord!</p>";
            }
        }
    } catch (mysqli_sql_exception $e) {
        $melding = "<p style='color:red;'>Database fout: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
     <meta name="description" content="Hier zie je de login pagina en kun je meteen naar registreren.">
    <meta name="keywords" content="login, or registereren, gebruikersnaam, wachtwoord">
     <meta name="pieter">
    <title>Login</title>

    <style>
        .status {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #222;
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

    <section class="registratie-pagina">


  <H1>Login</H1>

<div class="status">
    <?php if (isset($_SESSION["ingelogd"]) && $_SESSION["ingelogd"] === true): ?>
        ✅ Ingelogd als <?= htmlspecialchars($_SESSION["gebruikersnaam"]) ?>
    <?php else: ?>
        ❌ Niet ingelogd
    <?php endif; ?>
</div>

<form action="" method="post">
    <label for="gebruikersnaam">Gebruikersnaam:</label><br>
    <input type="text" id="gebruikersnaam" name="gebruikersnaam" required><br><br>

    <label for="wachtwoord">Wachtwoord:</label><br>
    <input type="password" id="wachtwoord" name="wachtwoord" required><br><br>

    <input type="submit" value="Login">
</form>

<?php echo $melding; ?>
<br>
<a href="registreren.php">Nog geen account? Registreren</a>

</section>
<?php include 'footer.php'; ?>
</body>
</html>