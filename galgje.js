
/* gemaakt door Yassine */

const GALGJE_GAME_ID = 3;
let galgjeScoreOpgeslagen = false;

const woordenlijst = [
    {
        woord: "computer",
        betekenis: "Een apparaat waarmee je informatie kunt verwerken."
    },
    {
        woord: "school",
        betekenis: "Een plek waar je leert."
    },
    {
        woord: "programmeren",
        betekenis: "Code schrijven om een computer iets te laten doen."
    },
    {
        woord: "database",
        betekenis: "Een plek waar gegevens worden opgeslagen."
    },
    {
        woord: "javascript",
        betekenis: "Een programmeertaal waarmee je websites interactief maakt."
    },
    {
        woord: "internet",
        betekenis: "Een netwerk waarmee computers over de hele wereld verbonden zijn."
    },
    {
        woord: "website",
        betekenis: "Een verzameling webpagina's op het internet."
    },
    {
        woord: "functie",
        betekenis: "Een stuk code dat je opnieuw kunt gebruiken."
    }
];

let gekozenWoord = "";
let betekenis = "";
let geradenLetters = [];
let fouten = 0;
let spelAfgelopen = false;

function openGalgje() {
    document.getElementById("galgje-spel").style.display = "flex";

    const tictactoeSpel = document.getElementById("tictactoe-spel");
    if (tictactoeSpel) {
        tictactoeSpel.style.display = "none";
    }

    startGalgje();
}

function sluitGalgje() {
    document.getElementById("galgje-spel").style.display = "none";
}

function startGalgje() {
    const randomIndex = Math.floor(Math.random() * woordenlijst.length);

    gekozenWoord = woordenlijst[randomIndex].woord.toLowerCase();
    betekenis = woordenlijst[randomIndex].betekenis;

    geradenLetters = [];
    fouten = 0;
    spelAfgelopen = false;
    galgjeScoreOpgeslagen = false;

    document.getElementById("fouten").innerHTML = fouten;
    document.getElementById("galgjeBericht").innerHTML = "Vul een letter of het hele woord in.";
    document.getElementById("hint").innerHTML = "";
    document.getElementById("letterInput").value = "";
    document.getElementById("antwoordInput").value = "";

    toonWoord();
}

function toonWoord() {
    let weergave = "";

    for (let i = 0; i < gekozenWoord.length; i++) {
        let letter = gekozenWoord[i];

        if (geradenLetters.includes(letter)) {
            weergave += letter + " ";
        } else {
            weergave += "_ ";
        }
    }

    document.getElementById("woordWeergave").innerHTML = weergave;

    if (!weergave.includes("_")) {
        document.getElementById("galgjeBericht").innerHTML = "Goed gedaan! Je hebt het woord geraden.";
        spelAfgelopen = true;
        slaScoreAlsGewonnen();
    }
}

function controleerLetter() {
    if (spelAfgelopen === true) {
        document.getElementById("galgjeBericht").innerHTML = "Het spel is al klaar. Klik op Nieuw spel.";
        return;
    }

    let letter = document.getElementById("letterInput").value.toLowerCase();

    if (letter === "") {
        document.getElementById("galgjeBericht").innerHTML = "Vul eerst een letter in.";
        return;
    }

    if (letter.length > 1) {
        document.getElementById("galgjeBericht").innerHTML = "Vul maar één letter in.";
        return;
    }

    if (geradenLetters.includes(letter)) {
        document.getElementById("galgjeBericht").innerHTML = "Deze letter heb je al geraden.";
        document.getElementById("letterInput").value = "";
        return;
    }

    if (gekozenWoord.includes(letter)) {
        geradenLetters.push(letter);
        document.getElementById("galgjeBericht").innerHTML = "Goed! De letter zit in het woord.";
    } else {
        fouten++;
        document.getElementById("fouten").innerHTML = fouten;
        document.getElementById("galgjeBericht").innerHTML = "Helaas, deze letter zit niet in het woord.";
    }

    document.getElementById("letterInput").value = "";
    toonWoord();
}

function controleerAntwoord() {
    if (spelAfgelopen === true) {
        document.getElementById("galgjeBericht").innerHTML = "Het spel is al klaar. Klik op Nieuw spel.";
        return;
    }

    let antwoord = document.getElementById("antwoordInput").value.toLowerCase();

    if (antwoord === "") {
        document.getElementById("galgjeBericht").innerHTML = "Vul eerst een antwoord in.";
        return;
    }

    if (antwoord === gekozenWoord) {
        geradenLetters = gekozenWoord.split("");
        toonWoord();
        document.getElementById("galgjeBericht").innerHTML = "Goed! Je hebt het hele woord geraden.";
        spelAfgelopen = true;
        slaScoreAlsGewonnen();
    } else {
        fouten++;
        document.getElementById("fouten").innerHTML = fouten;
        document.getElementById("galgjeBericht").innerHTML = "Dat is niet het juiste woord.";
    }

    document.getElementById("antwoordInput").value = "";
}

function toonHint() {
    document.getElementById("hint").innerHTML = "Hint: " + betekenis;
}

function berekenGalgjeScore() {
    let score = 100 - (fouten * 10);

    if (score < 0) {
        score = 0;
    }

    return score;
}

function slaScoreAlsGewonnen() {
    if (galgjeScoreOpgeslagen === true) {
        return;
    }

    let score = berekenGalgjeScore();
    slaGalgjeScoreOp(score);

    galgjeScoreOpgeslagen = true;
}

function slaGalgjeScoreOp(score) {
    fetch("scores_opslaan.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body:
            "game_id=" + encodeURIComponent(GALGJE_GAME_ID) +
            "&score=" + encodeURIComponent(score)
    })
    .then(function(response) {
        return response.text();
    })
    .then(function(data) {
        console.log(data);
    })
    .catch(function(error) {
        console.log("Fout bij score opslaan:", error);
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const letterInput = document.getElementById("letterInput");
    const antwoordInput = document.getElementById("antwoordInput");

    letterInput.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            controleerLetter();
        }
    });

    antwoordInput.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            controleerAntwoord();
        }
    });
});