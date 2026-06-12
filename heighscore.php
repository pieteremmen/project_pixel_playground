<?php
session_start();
include 'db.php';

/*
    game_id 3 = Galgje
    game_id 4 = andere game
*/

$games = [
    3 => "Galgje",
    4 => "Tic Tac Toe"
];

$alleScores = [];

foreach ($games as $game_id => $game_titel) {
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
    }

    $alleScores[$game_titel] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_SESSION["ingelogd"]) && $_SESSION["ingelogd"] === true) {
    $titel = "Mijn Highscores van " . htmlspecialchars($_SESSION["gebruikersnaam"]);
} else {
    $titel = "Highscores van Anonieme spelers";
}
?>

<!DOCTYPE html>
<html lang="nl">
     <meta name="description" content="hier zie je jou top 5 laatste heighscores van iedere game.">
    <meta name="keywords" content="top 5 heigscores, HTML, games,">
    <meta name="Yassine">
<head>
    <meta charset="UTF-8">
    <title>Highscores</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: white;
            color: #111;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 40px auto;
        }

        h1 {
            text-align: center;
            font-size: 34px;
            margin-bottom: 25px;
        }

        h2 {
            margin-top: 35px;
            margin-bottom: 15px;
            font-size: 26px;
        }

        .box {
            background: white;
            border: 1px solid #555;
            padding: 20px;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #111827;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f2f2f2;
        }

        .plek {
            font-weight: bold;
        }

        .score {
            font-weight: bold;
        }

        .geen-scores {
            text-align: center;
            padding: 20px;
        }

        @media (max-width: 700px) {
            h1 {
                font-size: 26px;
            }

            h2 {
                font-size: 22px;
            }

            .box {
                overflow-x: auto;
            }

            table {
                min-width: 650px;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">

    <h1><?= $titel ?></h1>

    <?php foreach ($alleScores as $gameNaam => $scores): ?>

        <h2><?= htmlspecialchars($gameNaam) ?></h2>

        <div class="box">
            <table>
                <tr>
                    <th>Plek</th>
                    <th>Naam</th>
                    <th>Game</th>
                    <th>Score</th>
                    <th>Datum</th>
                </tr>

                <?php if (count($scores) === 0): ?>
                    <tr>
                        <td colspan="5" class="geen-scores">
                            Er zijn nog geen highscores voor deze game.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $plek = 1; ?>

                    <?php foreach ($scores as $score): ?>
                        <tr>
                            <td class="plek"><?= $plek ?></td>
                            <td><?= htmlspecialchars($score['gebruikersnaam']) ?></td>
                            <td><?= htmlspecialchars($score['game_name']) ?></td>
                            <td class="score"><?= htmlspecialchars($score['highscore']) ?></td>
                            <td><?= htmlspecialchars($score['timestamp']) ?></td>
                        </tr>

                        <?php $plek++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>

    <?php endforeach; ?>

</div>
<?php include 'footer.php'; ?>
</body>
</html>