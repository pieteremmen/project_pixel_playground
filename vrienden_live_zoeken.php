<?php
session_start();

$conn = new mysqli("localhost", "root", "", "mypixelplayground");

if ($conn->connect_error) {
    exit("Connectie mislukt.");
}

// Check of je bent ingelogd
if (
    !isset($_SESSION["ingelogd"]) ||
    $_SESSION["ingelogd"] !== true ||
    !isset($_SESSION["gebruikersnaam"])
) {
    exit("<p class='geen-resultaat'>Je bent niet ingelogd.</p>");
}

// Haal de id op van de ingelogde gebruiker
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
    exit("<p class='geen-resultaat'>Gebruiker niet gevonden.</p>");
}

$ingelogde_gebruiker = $result_login->fetch_assoc();
$ingelogde_gebruiker_id = $ingelogde_gebruiker["id"];

$zoekterm = $_GET["zoekterm"] ?? "";

// Zoek gebruikers die beginnen met wat je typt
if ($zoekterm !== "") {
    $zoek = $zoekterm . "%";

    $stmt = $conn->prepare("
        SELECT id, gebruikersnaam
        FROM gebruikers
        WHERE gebruikersnaam LIKE ?
        AND id != ?
        ORDER BY gebruikersnaam ASC
    ");
    $stmt->bind_param("si", $zoek, $ingelogde_gebruiker_id);
} else {
    $stmt = $conn->prepare("
        SELECT id, gebruikersnaam
        FROM gebruikers
        WHERE id != ?
        ORDER BY gebruikersnaam ASC
    ");
    $stmt->bind_param("i", $ingelogde_gebruiker_id);
}

$stmt->execute();
$gebruikers = $stmt->get_result();

if ($gebruikers->num_rows > 0) {
    while ($gebruiker = $gebruikers->fetch_assoc()) {
        ?>
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
                <button type="submit" class="btn btn-donker">
                    Vriendverzoek sturen
                </button>
            </form>
        </div>
        <?php
    }
} else {
    echo "<p class='geen-resultaat'>Geen gebruikers gevonden.</p>";
}
?>