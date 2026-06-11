

<?php
session_start();
include 'db.php';

if (!isset($_POST['score'])) {
    echo "Geen score ontvangen";
    exit;
}

if (!isset($_POST['game_id'])) {
    echo "Geen game_id ontvangen";
    exit;
}

$game_id = (int) $_POST['game_id'];
$score = (int) $_POST['score'];

/*
    Als gebruiker is ingelogd: echte gebruiker_id ophalen.
    Als gebruiker niet is ingelogd: gebruiker_id wordt NULL.
*/
$gebruiker_id = null;

if (isset($_SESSION["ingelogd"]) && $_SESSION["ingelogd"] === true) {
    $gebruikersnaam = $_SESSION["gebruikersnaam"];

    $sqlGebruiker = "SELECT id FROM gebruikers WHERE gebruikersnaam = :gebruikersnaam";
    $stmtGebruiker = $conn->prepare($sqlGebruiker);
    $stmtGebruiker->bindParam(':gebruikersnaam', $gebruikersnaam);
    $stmtGebruiker->execute();

    $gebruiker = $stmtGebruiker->fetch(PDO::FETCH_ASSOC);

    if (!$gebruiker) {
        echo "Gebruiker niet gevonden";
        exit;
    }

    $gebruiker_id = $gebruiker["id"];
}

/* score opslaan */
$sql = "INSERT INTO highscores (game_id, gebruiker_id, highscore, timestamp)
        VALUES (:game_id, :gebruiker_id, :highscore, NOW())";

$stmt = $conn->prepare($sql);

$stmt->bindValue(':game_id', $game_id, PDO::PARAM_INT);

if ($gebruiker_id === null) {
    $stmt->bindValue(':gebruiker_id', null, PDO::PARAM_NULL);
} else {
    $stmt->bindValue(':gebruiker_id', $gebruiker_id, PDO::PARAM_INT);
}

$stmt->bindValue(':highscore', $score, PDO::PARAM_INT);

$stmt->execute();

echo "Score opgeslagen";
?>