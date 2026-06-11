<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="hier zie je allen games die je kan spelen.">
    <meta name="keywords" content="galgje, wordle, vier op een rij, tic tac toe, HTML, meta tags, games, website structuur">
    <meta name="Pieter" content="Pieter heeft wordle en vier op een rij gemaakt">
    <title>Games</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
session_start();
?>

<?php include 'header.php'; ?>

<main class="games-pagina">

    <h1 class="games-titel">All games</h1>

    <section class="games-grid">

        <article class="game-box" onclick="openTicTacToe()">
            <h2>Tic Tac Toe</h2>
            <img src="images/boter,kaas_eieren.png" alt="Tic Tac Toe">
        </article>

        <article class="game-box" onclick="openGalgje()">
            <h2>Galgje</h2>
            <img src="images/galgje.png" alt="Galgje">
        </article>

    </section>

  
    <section id="tictactoe-spel" class="tictactoe-container">
        <button class="sluit-knop" onclick="sluitTicTacToe()">X</button>

        <h1>Tic Tac Toe</h1>

        <section id="bord" class="tictactoe-bord">
            <div class="vakje"></div>
            <div class="vakje"></div>
            <div class="vakje"></div>

            <div class="vakje"></div>
            <div class="vakje"></div>
            <div class="vakje"></div>

            <div class="vakje"></div>
            <div class="vakje"></div>
            <div class="vakje"></div>
        </section>

        <h2 id="bericht">Speler X is aan de beurt</h2>

        <button onclick="resetSpel()">Opnieuw spelen</button>
    </section>

   
    <section id="galgje-spel" class="galgje-overlay">
        <div class="galgje-venster">

            <button class="sluit-knop" onclick="sluitGalgje()">X</button>

            <h1>Galgje</h1>
            <h2>Raad het woord</h2>

            <p id="woordWeergave"></p>

            <p>Foute pogingen: <span id="fouten">0</span></p>
            <p id="galgjeBericht"></p>

            <input type="text" id="letterInput" maxlength="1" placeholder="Vul een letter in">
            <button onclick="controleerLetter()">Controleer letter</button>

            <br><br>

            <input type="text" id="antwoordInput" placeholder="Vul het hele woord in">
            <button onclick="controleerAntwoord()">Controleer antwoord</button>

            <br><br>

            <button onclick="toonHint()">Toon hint</button>
            <p id="hint"></p>

            <br>

            <button onclick="startGalgje()">Nieuw spel</button>

        </div>
    </section>

</main>

<script src="tictactoe.js"></script>
<script src="galgje.js"></script>

</body>
</html>