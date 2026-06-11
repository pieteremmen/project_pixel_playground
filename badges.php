<?php
session_start();

$conn = new mysqli("localhost", "root", "", "mypixelplayground");

if ($conn->connect_error) {
    die("Connectie mislukt: " . $conn->connect_error);
}

if (!isset($_SESSION["ingelogd"]) || $_SESSION["ingelogd"] !== true || !isset($_SESSION["gebruikersnaam"])) {
    die("Je bent niet ingelogd.");
}

// Gebruiker id ophalen
$gebruikersnaam = $_SESSION["gebruikersnaam"];

$stmt = $conn->prepare("
    SELECT id
    FROM gebruikers
    WHERE gebruikersnaam = ?
");
$stmt->bind_param("s", $gebruikersnaam);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Gebruiker niet gevonden.");
}

$gebruiker = $result->fetch_assoc();
$gebruiker_id = $gebruiker["id"];

// Badges van gebruiker ophalen
$stmt = $conn->prepare("
    SELECT badges.naam, badges.badge_condition, badges.image
    FROM gebruiker_badge
    JOIN badges ON gebruiker_badge.badge_id = badges.id
    WHERE gebruiker_badge.gebruiker_id = ?
");
$stmt->bind_param("i", $gebruiker_id);
$stmt->execute();

$badges = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
        <meta name="description" content="allen badges van vrienden">
    <meta name="badges" content="HTML, meta tags,badges, vrienden, profiel">
    <meta name="Yassine" >
<head>
    <title>Mijn badges</title>
</head>
<body>

<?php include 'header.php'; ?>

<h1>Mijn badges</h1>

<?php if ($badges->num_rows > 0): ?>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">

        <?php while ($badge = $badges->fetch_assoc()): ?>
            <div style="border: 1px solid #ccc; padding: 15px; width: 180px; text-align: center;">
                
                <?php if (!empty($badge["image"])): ?>
                    <img 
                        src="images/<?= htmlspecialchars($badge["image"]) ?>" 
                        alt="<?= htmlspecialchars($badge["naam"]) ?>"
                        style="width: 100px; height: 100px; object-fit: contain;"
                    >
                <?php endif; ?>

                <h3><?= htmlspecialchars($badge["naam"]) ?></h3>
                <p>Behaald door 4 vrienden te hebben.</p>

            </div>
        <?php endwhile; ?>

    </div>

<?php else: ?>

    <p>Je hebt nog geen badges behaald.</p>

<?php endif; ?>

</body>
</html>