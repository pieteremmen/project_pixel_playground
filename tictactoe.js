let speler = "X";
let spelKlaar = false;

let vakjes = document.getElementsByClassName("vakje");
let bericht = document.getElementById("bericht");

let winCombinaties = [
    [0, 1, 2],
    [3, 4, 5],
    [6, 7, 8],

    [0, 3, 6],
    [1, 4, 7],
    [2, 5, 8],

    [0, 4, 8],
    [2, 4, 6]
];

function openTicTacToe() {
    let spel = document.getElementById("tictactoe-spel");

    spel.style.display = "flex";

    if (spel.requestFullscreen) {
        spel.requestFullscreen();
    }
}

function sluitTicTacToe() {
    let spel = document.getElementById("tictactoe-spel");

    spel.style.display = "none";

    if (document.fullscreenElement) {
        document.exitFullscreen();
    }
}

for (let i = 0; i < vakjes.length; i++) {
    vakjes[i].addEventListener("click", function () {
        klikVakje(i);
    });
}

function klikVakje(index) {
    if (spelKlaar === true) {
        return;
    }

    if (vakjes[index].innerHTML !== "") {
        return;
    }

    vakjes[index].innerHTML = speler;

    controleerWinnaar();

    if (spelKlaar === false) {
        wisselSpeler();
    }
}

function wisselSpeler() {
    if (speler === "X") {
        speler = "O";
    } else {
        speler = "X";
    }

    bericht.innerHTML = "Speler " + speler + " is aan de beurt";
}

function controleerWinnaar() {
    for (let i = 0; i < winCombinaties.length; i++) {
        let combinatie = winCombinaties[i];

        let vak1 = vakjes[combinatie[0]].innerHTML;
        let vak2 = vakjes[combinatie[1]].innerHTML;
        let vak3 = vakjes[combinatie[2]].innerHTML;

        if (vak1 !== "" && vak1 === vak2 && vak2 === vak3) {
            bericht.innerHTML = "Speler " + speler + " heeft gewonnen!";
            spelKlaar = true;

            vakjes[combinatie[0]].style.backgroundColor = "lightgreen";
            vakjes[combinatie[1]].style.backgroundColor = "lightgreen";
            vakjes[combinatie[2]].style.backgroundColor = "lightgreen";

            scoreOpslaan(100);

            return;
        }
    }

    controleerGelijkspel();
}

function controleerGelijkspel() {
    for (let i = 0; i < vakjes.length; i++) {
        if (vakjes[i].innerHTML === "") {
            return;
        }
    }

    bericht.innerHTML = "Gelijkspel!";
    spelKlaar = true;

    scoreOpslaan(50);
}

function resetSpel() {
    speler = "X";
    spelKlaar = false;
    bericht.innerHTML = "Speler X is aan de beurt";

    for (let i = 0; i < vakjes.length; i++) {
        vakjes[i].innerHTML = "";
        vakjes[i].style.backgroundColor = "white";
    }
}

function scoreOpslaan(score) {
    fetch("scores_opslaan.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "score=" + score
    })
    .then(function(response) {
        return response.text();
    })
    .then(function(data) {
        console.log(data);
    });
}

f