<?php
include 'db.php';

$game_id = 4;
$titel = "Anonieme highscores";
$scores = [];

if ($conn) {
    if (isset($_SESSION["ingelogd"]) && $_SESSION["ingelogd"] === true) {
        $gebruikersnaam = $_SESSION["gebruikersnaam"];

        $sql = "
        SELECT 
            games.game_name,
            highscores.highscore,
            highscores.timestamp,
            gebruikers.gebruikersnaam
        FROM highscores
        INNER JOIN games 
            ON highscores.game_id = games.id
        INNER JOIN gebruikers 
            ON highscores.gebruiker_id = gebruikers.id
        WHERE highscores.game_id = :game_id
        AND gebruikers.gebruikersnaam = :gebruikersnaam
        ORDER BY highscores.highscore DESC
        LIMIT 5
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':game_id', $game_id);
        $stmt->bindParam(':gebruikersnaam', $gebruikersnaam);
        $stmt->execute();

        $titel = "Mijn highscores";
    } else {
        $sql = "
        SELECT 
            games.game_name,
            highscores.highscore,
            highscores.timestamp,
            'Anoniem' AS gebruikersnaam
        FROM highscores
        INNER JOIN games 
            ON highscores.game_id = games.id
        WHERE highscores.game_id = :game_id
        AND highscores.gebruiker_id IS NULL
        ORDER BY highscores.highscore DESC
        LIMIT 5
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':game_id', $game_id);
        $stmt->execute();

        $titel = "Anonieme highscores";
    }

    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $titel = "Highscores niet beschikbaar";
}
?>

<aside class="highscore-widget">
    <h2><?= htmlspecialchars($titel) ?></h2>

    <?php if (count($scores) === 0): ?>
        <p>Er zijn nog geen highscores.</p>
    <?php else: ?>
        <ol>
            <?php foreach ($scores as $score): ?>
                <li>
                    <strong><?= htmlspecialchars($score["highscore"]) ?></strong><br>
                    <span><?= htmlspecialchars($score["game_name"]) ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <a href="heighscore.php">Bekijk alles</a>
</aside>