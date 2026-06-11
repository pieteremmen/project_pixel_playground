<?php
session_start();

if (!isset($_SESSION["ingelogd"]) || $_SESSION["ingelogd"] !== true) {
    header("Location: login.php");
    exit;
}

include "db.php";

$gebruikersnaam = $_SESSION["gebruikersnaam"] ?? "Gast";

/*
    Highscores ophalen:
    Per game de beste score van deze gebruiker.
*/
$sqlHighscores = "
    SELECT 
        games.game_name,
        MAX(highscores.highscore) AS beste_score
    FROM highscores
    INNER JOIN games 
        ON highscores.game_id = games.id
    INNER JOIN gebruikers 
        ON highscores.gebruiker_id = gebruikers.id
    WHERE gebruikers.gebruikersnaam = :gebruikersnaam
    GROUP BY games.id, games.game_name
    ORDER BY games.game_name ASC
";

$stmtHighscores = $conn->prepare($sqlHighscores);
$stmtHighscores->bindParam(":gebruikersnaam", $gebruikersnaam);
$stmtHighscores->execute();

$mijnHighscores = $stmtHighscores->fetchAll(PDO::FETCH_ASSOC);


/*
    Vrienden ophalen:
    Beide richtingen checken:
    - jij bent gebruiker_id
    - jij bent vriend_id

    Alleen status accepted.
*/
$sqlVrienden = "
    SELECT DISTINCT vriend_naam
    FROM (
        SELECT 
            g2.gebruikersnaam AS vriend_naam
        FROM vrienden v
        INNER JOIN gebruikers g1 
            ON v.gebruiker_id = g1.id
        INNER JOIN gebruikers g2 
            ON v.vriend_id = g2.id
        WHERE g1.gebruikersnaam = :gebruikersnaam
        AND v.status = 'accepted'

        UNION

        SELECT 
            g1.gebruikersnaam AS vriend_naam
        FROM vrienden v
        INNER JOIN gebruikers g1 
            ON v.gebruiker_id = g1.id
        INNER JOIN gebruikers g2 
            ON v.vriend_id = g2.id
        WHERE g2.gebruikersnaam = :gebruikersnaam
        AND v.status = 'accepted'
    ) AS alle_vrienden
    ORDER BY vriend_naam ASC
";

$stmtVrienden = $conn->prepare($sqlVrienden);
$stmtVrienden->bindParam(":gebruikersnaam", $gebruikersnaam);
$stmtVrienden->execute();

$mijnVrienden = $stmtVrienden->fetchAll(PDO::FETCH_ASSOC);


/*
    Badges ophalen:
    Badges die deze gebruiker heeft behaald.
*/
$sqlBadges = "
    SELECT 
        badges.naam,
        badges.badge_condition,
        badges.image
    FROM gebruiker_badge
    INNER JOIN badges 
        ON gebruiker_badge.badge_id = badges.id
    INNER JOIN gebruikers 
        ON gebruiker_badge.gebruiker_id = gebruikers.id
    WHERE gebruikers.gebruikersnaam = :gebruikersnaam
    ORDER BY badges.naam ASC
";

$stmtBadges = $conn->prepare($sqlBadges);
$stmtBadges->bindParam(":gebruikersnaam", $gebruikersnaam);
$stmtBadges->execute();

$mijnBadges = $stmtBadges->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
     <meta name="description" content="hier zie je alles van vanaf je vrienden, badges en highscores van elke game en je kan je wachtwoord en gebruikernaam veranderen.">
    <meta name="keywords" content="profiel, vrienden, badges, highscores, wachtwoord veranderen, gebruikersnaam veranderen">
     <meta name="Yassine">
    <title>Profiel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main class="profile-container">
    <h1>Profiel</h1>

    <section class="profile-grid">

        <div class="profile-card">
            <h2>Profile data:</h2>
            <p>
                <strong>Username:</strong>
                <?= htmlspecialchars($gebruikersnaam); ?>
            </p>
        </div>

        <div class="profile-card">
            <h2>Jouw vrienden:</h2>

            <?php if (empty($mijnVrienden)): ?>
                <p>Je hebt nog geen vrienden.</p>
            <?php else: ?>
                <?php foreach ($mijnVrienden as $vriend): ?>
                    <p><?= htmlspecialchars($vriend["vriend_naam"]); ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="profile-card large-card">
            <h2>Jouw highscores:</h2>

            <?php if (empty($mijnHighscores)): ?>
                <p>Je hebt nog geen highscores.</p>
            <?php else: ?>
                <?php foreach ($mijnHighscores as $score): ?>
                    <div class="score-block">
                        <p><strong><?= htmlspecialchars($score["game_name"]); ?>:</strong></p>
                        <p>Highscore: <?= htmlspecialchars($score["beste_score"]); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="profile-card large-card">
            <h2>Jouw badges:</h2>

            <?php if (empty($mijnBadges)): ?>
                <p>Je hebt nog geen badges.</p>
            <?php else: ?>
                <div class="profile-badges">
                    <?php foreach ($mijnBadges as $badge): ?>
                        <div class="profile-badge">

                            <?php if (!empty($badge["image"])): ?>
                                <img 
                                    src="images/<?= htmlspecialchars($badge["image"]); ?>" 
                                    alt="<?= htmlspecialchars($badge["naam"]); ?>"
                                >
                            <?php endif; ?>

                            <h3><?= htmlspecialchars($badge["naam"]); ?></h3>

                            <?php if (!empty($badge["badge_condition"])): ?>
                                <p><?= htmlspecialchars($badge["badge_condition"]); ?></p>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-card">
            <h2>Wachtwoord veranderen:</h2>

            <form method="post">
                <label for="new-password">Nieuw wachtwoord:</label>
                <input type="password" id="new-password" name="new_password">
               
                <label for="confirm-password">Bevestig nieuw wachtwoord:</label>
                <input type="password" id="confirm-password" name="confirm_password">

                <button type="submit" name="change_password">Change</button>
            </form>
        </div>

        <div class="profile-card">
            <h2>Gebruikersnaam veranderen:</h2>

            <form method="post">
                <label for="new-username">Nieuwe gebruikersnaam:</label>
                <input 
                    type="text" 
                    id="new-username" 
                    name="new_username" 
                    value="<?= htmlspecialchars($gebruikersnaam); ?>"
                >

                <button type="submit" name="change_username">Change</button>
            </form>
        </div>

    </section>
</main>

<?php include "footer.php"; ?>

</body>
</html>