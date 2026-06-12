<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta name="description" content="hier zie je de games, info en je laatst behaalde heighscores.">
    <meta name="keywords" content="Home-pagina, laatste nehaalde heighscores, slide show alle games">
    <meta name="Pieter">
    <link rel="stylesheet" href="style.css">
    <title>Pixel Playground</title>
</head>
<body>

<?php
session_start();
?>
    <?php include 'header.php'; ?>



<main class="home-pagina">

    <section class="home-titel">
        <h1>Welkom op mijn Pixel Playground!</h1>
    </section>

    <section class="home-games">
        <h2>Onze spellen:</h2>

        <section class="home-slider">

            <section class="home-slide">
                <h3>Boter, kaas en eieren</h3>
                <img src="images/boter,kaas_eieren.png" alt="Boter kaas en eieren">
            </section>

            <section class="home-slide">
                <h3>Galgje</h3>
                <img src="images/galgje.png" alt="Galgje">
            </section>

            <section class="home-slide">
                <h3>Wordle</h3>
                <img src="images/wordle.png" alt="Wordle">
            </section>

            <section class="home-slide">
                <h3>Vier op een rij</h3>
                <img src="images/vieropeenrij.png" alt="Vier op een rij">
            </section>

        </section>

        
    </section>

    <section class="home-info">

   
    
     

        <article class="container_info_home_text">


            <h2>Wat is My Pixel Playground?</h2>

            <p>
                My Pixel Playground is een creatieve website waar pixel art en digitale stijl centraal staan.
                De website voelt speels, kleurrijk en uniek aan, alsof je een kleine digitale speeltuin binnenstapt.
                Bezoekers kunnen er inspiratie opdoen, creatieve ontwerpen bekijken en genieten van een retro uitstraling
                die doet denken aan klassieke games. Door de pixelstijl krijgt de website een herkenbare en vrolijke sfeer.
                My Pixel Playground is daarom een leuke plek voor mensen die houden van kunst, design, games en creatieve online werelden.
            </p>
        </article>

       
    </section>

   
<?php include 'heighscore_klein.php'; ?>




</main>
<script>
let myIndex = 0;
carousel();

function carousel() {
    let x = document.getElementsByClassName("home-slide");

    for (let i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }

    myIndex++;

    if (myIndex > x.length) {
        myIndex = 1;
    }

    x[myIndex - 1].style.display = "block";
    setTimeout(carousel, 2000);
}
</script>


<?php include 'footer.php'; ?>
</body>
</html>