
<head>
    <meta charset="UTF-8">
    <meta name="description" content="header kan je naar alles toee.">
<meta name="keywords" content="header nav, HTML, meta tags, navigatie, website structuur">
<meta name="yassine">
    <title>Header</title>
    <link rel="stylesheet" href="style.css">
</head>

<header class="site-header">
    <nav class="navbar">
        <a href="index.php">Home</a>
        <a href="games.php">Games</a>
        <a href="heighscore.php">Highscores</a>
        <a href="vrienden.php">Vrienden</a>
        <a href="profile.php">Profiel</a>

        <?php if (isset($_SESSION["ingelogd"]) && $_SESSION["ingelogd"] === true): ?>
            <a href="badges.php">Badges</a>

            <span class="navbar-user">
                Ingelogd als <?= htmlspecialchars($_SESSION["gebruikersnaam"]) ?>
            </span>
            <a href="uitloggen.php">Uitloggen</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="registreren.php">Registreren</a>
        <?php endif; ?>
    </nav>
</header>

