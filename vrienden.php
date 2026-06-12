<?php
session_start();

$conn = new mysqli("localhost", "root", "", "mypixelplayground");

if ($conn->connect_error) {
    die("Connectie mislukt: " . $conn->connect_error);
}

// Check of je bent ingelogd
if (
    !isset($_SESSION["ingelogd"]) ||
    $_SESSION["ingelogd"] !== true ||
    !isset($_SESSION["gebruikersnaam"])
) {
    die("Je bent niet ingelogd.");
}

// Haal de id op van de ingelogde gebruiker via gebruikersnaam
$ingelogde_gebruikersnaam = $_SESSION["gebruikersnaam"];

$stmt_login = $conn->prepare("
    SELECT id 
    FROM gebruikers 
    WHERE gebruikersnaam = ?
");
$stmt_login->bind_param("s", $ingelogde_gebruikersnaam);
$stmt_login->execute();
$result_login = $stmt_login->get_result();

if ($result_login->num_rows === 0) {
    die("Ingelogde gebruiker niet gevonden in database.");
}

$ingelogde_gebruiker = $result_login->fetch_assoc();
$ingelogde_gebruiker_id = $ingelogde_gebruiker["id"];

$melding = "";


// ===============================
// BADGE FUNCTIE
// ===============================
function checkBadges($conn, $gebruiker_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS totaal
        FROM vrienden
        WHERE status = 'accepted'
        AND (
            gebruiker_id = ?
            OR vriend_id = ?
        )
    ");
    $stmt->bind_param("ii", $gebruiker_id, $gebruiker_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $aantalVrienden = $data['totaal'];

    if ($aantalVrienden >= 4) {
        $badge_condition = "friends_4";

        $stmt = $conn->prepare("
            SELECT id
            FROM badges
            WHERE badge_condition = ?
        ");
        $stmt->bind_param("s", $badge_condition);
        $stmt->execute();

        $result = $stmt->get_result();
        $badge = $result->fetch_assoc();

        if ($badge) {
            $badge_id = $badge['id'];

            $stmt = $conn->prepare("
                SELECT *
                FROM gebruiker_badge
                WHERE gebruiker_id = ?
                AND badge_id = ?
            ");
            $stmt->bind_param("ii", $gebruiker_id, $badge_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $stmt = $conn->prepare("
                    INSERT INTO gebruiker_badge (gebruiker_id, badge_id)
                    VALUES (?, ?)
                ");
                $stmt->bind_param("ii", $gebruiker_id, $badge_id);
                $stmt->execute();
            }
        }
    }
}


// ===============================
// VRIENDVERZOEK STUREN
// ===============================
if (isset($_POST['vriend_id'])) {
    $vriend_id = (int) $_POST['vriend_id'];

    if ($vriend_id != $ingelogde_gebruiker_id) {
        $check = $conn->prepare("
            SELECT *
            FROM vrienden
            WHERE
                (gebruiker_id = ? AND vriend_id = ?)
                OR
                (gebruiker_id = ? AND vriend_id = ?)
        ");
        $check->bind_param(
            "iiii",
            $ingelogde_gebruiker_id,
            $vriend_id,
            $vriend_id,
            $ingelogde_gebruiker_id
        );
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("
                INSERT INTO vrienden (gebruiker_id, vriend_id, status)
                VALUES (?, ?, 'pending')
            ");
            $stmt->bind_param("ii", $ingelogde_gebruiker_id, $vriend_id);
            $stmt->execute();

            $melding = "Vriendverzoek verstuurd!";
        } else {
            $melding = "Jullie zijn al vrienden of er bestaat al een verzoek.";
        }
    } else {
        $melding = "Je kunt jezelf niet toevoegen.";
    }
}


// ===============================
// VRIENDVERZOEK ACCEPTEREN
// ===============================
if (isset($_POST['accepteer_id'])) {
    $verzoek_id = (int) $_POST['accepteer_id'];

    $stmt = $conn->prepare("
        UPDATE vrienden
        SET status = 'accepted'
        WHERE gebruiker_id = ?
        AND vriend_id = ?
        AND status = 'pending'
    ");
    $stmt->bind_param("ii", $verzoek_id, $ingelogde_gebruiker_id);
    $stmt->execute();

    checkBadges($conn, $ingelogde_gebruiker_id);
    checkBadges($conn, $verzoek_id);

    $melding = "Vriendverzoek geaccepteerd!";
}


// ===============================
// VRIENDVERZOEK WEIGEREN
// ===============================
if (isset($_POST['weiger_id'])) {
    $verzoek_id = (int) $_POST['weiger_id'];

    $stmt = $conn->prepare("
        DELETE FROM vrienden
        WHERE gebruiker_id = ?
        AND vriend_id = ?
        AND status = 'pending'
    ");
    $stmt->bind_param("ii", $verzoek_id, $ingelogde_gebruiker_id);
    $stmt->execute();

    $melding = "Vriendverzoek geweigerd.";
}


// ===============================
// VRIEND VERWIJDEREN
// ===============================
if (isset($_POST['verwijder_id'])) {
    $verwijder_id = (int) $_POST['verwijder_id'];

    $stmt = $conn->prepare("
        DELETE FROM vrienden
        WHERE
            (gebruiker_id = ? AND vriend_id = ?)
            OR
            (gebruiker_id = ? AND vriend_id = ?)
    ");
    $stmt->bind_param(
        "iiii",
        $ingelogde_gebruiker_id,
        $verwijder_id,
        $verwijder_id,
        $ingelogde_gebruiker_id
    );
    $stmt->execute();

    $melding = "Vriend verwijderd.";
}


// ===============================
// BINNENGEKOMEN VRIENDVERZOEKEN OPHALEN
// ===============================
$verzoeken = $conn->prepare("
    SELECT vrienden.gebruiker_id, gebruikers.gebruikersnaam
    FROM vrienden
    JOIN gebruikers ON vrienden.gebruiker_id = gebruikers.id
    WHERE vrienden.vriend_id = ?
    AND vrienden.status = 'pending'
");
$verzoeken->bind_param("i", $ingelogde_gebruiker_id);
$verzoeken->execute();
$verzoeken_resultaat = $verzoeken->get_result();


// ===============================
// MIJN VRIENDEN OPHALEN
// ===============================
$mijn_vrienden = $conn->prepare("
    SELECT gebruikers.id, gebruikers.gebruikersnaam
    FROM vrienden
    JOIN gebruikers
        ON (
            (vrienden.gebruiker_id = gebruikers.id AND vrienden.vriend_id = ?)
            OR
            (vrienden.vriend_id = gebruikers.id AND vrienden.gebruiker_id = ?)
        )
    WHERE vrienden.status = 'accepted'
");
$mijn_vrienden->bind_param("ii", $ingelogde_gebruiker_id, $ingelogde_gebruiker_id);
$mijn_vrienden->execute();
$mijn_vrienden_resultaat = $mijn_vrienden->get_result();


// ===============================
// GEBRUIKERS ZOEKEN
// ===============================
$zoekterm = $_GET['zoekterm'] ?? "";

if ($zoekterm != "") {
    $zoek = $zoekterm . "%";

    $stmt = $conn->prepare("
        SELECT *
        FROM gebruikers
        WHERE gebruikersnaam LIKE ?
        AND id != ?
    ");
    $stmt->bind_param("si", $zoek, $ingelogde_gebruiker_id);
} else {
    $stmt = $conn->prepare("
        SELECT *
        FROM gebruikers
        WHERE id != ?
    ");
    $stmt->bind_param("i", $ingelogde_gebruiker_id);
}

$stmt->execute();
$gebruikers = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta name="description" content="hier kan je je vrienden vinden en verzoek sturen.">
    <meta name="keywords" content="vrienden,zoeken, vriendscap verzoek">
     <meta name="Yassine">
    <meta charset="UTF-8">
    <title>Vrienden toevoegen</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background:
                linear-gradient(#e8e8e8 1px, transparent 1px),
                linear-gradient(90deg, #e8e8e8 1px, transparent 1px);
            background-size: 20px 20px;
            color: #111827;
        }

        .vrienden-container {
            max-width: 1150px;
            margin: 35px auto;
            padding: 20px;
        }

        .pagina-titel {
            text-align: center;
            font-size: 42px;
            margin-bottom: 30px;
        }

        .zoek-blok {
            background: white;
            border: 2px solid #555;
            padding: 22px;
            margin-bottom: 25px;
        }

        .zoek-form {
            display: flex;
            gap: 12px;
        }

        .zoek-form input {
            flex: 1;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #555;
            background: #f5f5f5;
        }

        .zoek-form button {
            padding: 12px 28px;
            font-size: 16px;
            border: 1px solid #555;
            background: #eeeeee;
            cursor: pointer;
        }

        .zoek-form button:hover {
            background: #dddddd;
        }

        .melding {
            background: #eef6ff;
            border: 2px solid #4b89dc;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .vrienden-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .kaart {
            background: white;
            border: 2px solid #555;
            padding: 22px;
            min-height: 180px;
        }

        .kaart.groot {
            grid-column: 1 / -1;
        }

        .kaart h2 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 24px;
        }

        .vriend-rij {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            border-bottom: 1px solid #ddd;
            padding: 12px 0;
        }

        .vriend-rij:last-child {
            border-bottom: none;
        }

        .vriend-naam {
            font-size: 17px;
            font-weight: bold;
            word-break: break-word;
        }

        .vriend-tekst {
            font-size: 15px;
            margin-top: 4px;
            color: #444;
        }

        .knoppen {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .actie-form {
            margin: 0;
        }

        .btn {
            border: 1px solid #555;
            padding: 9px 14px;
            cursor: pointer;
            font-size: 14px;
            background: #eeeeee;
            color: black;
        }

        .btn:hover {
            background: #dddddd;
        }

        .btn-donker {
            background: #111827;
            color: white;
            border-color: #111827;
        }

        .btn-donker:hover {
            background: #1f2937;
        }

        .btn-groen {
            background: #16a34a;
            color: white;
            border-color: #16a34a;
        }

        .btn-groen:hover {
            background: #15803d;
        }

        .btn-rood {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }

        .btn-rood:hover {
            background: #b91c1c;
        }

        .geen-resultaat {
            color: #444;
            margin: 0;
        }

        @media (max-width: 800px) {
            .vrienden-grid {
                grid-template-columns: 1fr;
            }

            .pagina-titel {
                font-size: 32px;
            }

            .zoek-form {
                flex-direction: column;
            }

            .vriend-rij {
                align-items: flex-start;
                flex-direction: column;
            }

            .knoppen {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="vrienden-container">

    <h1 class="pagina-titel">Vrienden toevoegen</h1>

    <?php if ($melding != ""): ?>
        <div class="melding">
            <?php echo htmlspecialchars($melding); ?>
        </div>
    <?php endif; ?>

    <div class="zoek-blok">
        <form method="GET" action="vrienden.php" class="zoek-form">
            <input
                type="text"
                id="zoekInput"
                name="zoekterm"
                placeholder="Zoek gebruiker..."
                value="<?php echo htmlspecialchars($zoekterm); ?>"
                autocomplete="off"
            >
            <button type="submit">Zoeken</button>
        </form>
    </div>

    <div class="vrienden-grid">

        <div class="kaart">
            <h2>Binnengekomen vriendverzoeken</h2>

            <?php if ($verzoeken_resultaat->num_rows > 0): ?>
                <?php while ($verzoek = $verzoeken_resultaat->fetch_assoc()): ?>
                    <div class="vriend-rij">
                        <div>
                            <div class="vriend-naam">
                                <?php echo htmlspecialchars($verzoek['gebruikersnaam']); ?>
                            </div>
                            <div class="vriend-tekst">
                                Wil vrienden worden.
                            </div>
                        </div>

                        <div class="knoppen">
                            <form method="POST" action="vrienden.php" class="actie-form">
                                <input
                                    type="hidden"
                                    name="accepteer_id"
                                    value="<?php echo htmlspecialchars($verzoek['gebruiker_id']); ?>"
                                >
                                <button type="submit" class="btn btn-groen">Accepteren</button>
                            </form>

                            <form method="POST" action="vrienden.php" class="actie-form">
                                <input
                                    type="hidden"
                                    name="weiger_id"
                                    value="<?php echo htmlspecialchars($verzoek['gebruiker_id']); ?>"
                                >
                                <button type="submit" class="btn btn-rood">Weigeren</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="geen-resultaat">Geen nieuwe vriendverzoeken.</p>
            <?php endif; ?>
        </div>

        <div class="kaart">
            <h2>Mijn vrienden</h2>

            <?php if ($mijn_vrienden_resultaat->num_rows > 0): ?>
                <?php while ($vriend = $mijn_vrienden_resultaat->fetch_assoc()): ?>
                    <div class="vriend-rij">
                        <div class="vriend-naam">
                            <?php echo htmlspecialchars($vriend['gebruikersnaam']); ?>
                        </div>

                        <form method="POST" action="vrienden.php" class="actie-form">
                            <input
                                type="hidden"
                                name="verwijder_id"
                                value="<?php echo htmlspecialchars($vriend['id']); ?>"
                            >
                            <button type="submit" class="btn btn-rood">Verwijderen</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="geen-resultaat">Je hebt nog geen vrienden.</p>
            <?php endif; ?>
        </div>

        <div class="kaart groot">
            <h2>Gebruikers</h2>

            <div id="gebruikersResultaten">
                <?php if ($gebruikers->num_rows > 0): ?>
                    <?php while ($gebruiker = $gebruikers->fetch_assoc()): ?>
                        <div class="vriend-rij">
                            <div class="vriend-naam">
                                <?php echo htmlspecialchars($gebruiker['gebruikersnaam']); ?>
                            </div>

                            <form method="POST" action="vrienden.php" class="actie-form">
                                <input
                                    type="hidden"
                                    name="vriend_id"
                                    value="<?php echo htmlspecialchars($gebruiker['id']); ?>"
                                >
                                <button type="submit" class="btn btn-donker">Vriendverzoek sturen</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="geen-resultaat">Geen gebruikers gevonden.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
const zoekInput = document.getElementById("zoekInput");
const gebruikersResultaten = document.getElementById("gebruikersResultaten");

let zoekTimer = null;

zoekInput.addEventListener("keyup", function () {
    clearTimeout(zoekTimer);

    zoekTimer = setTimeout(function () {
        const zoekterm = zoekInput.value;

        fetch("vrienden_live_zoeken.php?zoekterm=" + encodeURIComponent(zoekterm))
            .then(function (response) {
                return response.text();
            })
            .then(function (data) {
                gebruikersResultaten.innerHTML = data;
            })
            .catch(function () {
                gebruikersResultaten.innerHTML = "<p class='geen-resultaat'>Er ging iets fout met zoeken.</p>";
            });
    }, 200);
});
</script>
<?php include 'footer.php'; ?>
</body>
</html>