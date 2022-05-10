var text = [
    "Liste de candidats retenus à l'issue du jury final de recrutement des inspecteurs disponible",
    "Teste de recrutement des inspecteurs programmé.",
    "Amphithéâtre “Félix Antoine Tshisekedi”, inauguré ce mardi à l ‘ IGF PAR FELIX A.TSHISEKEDI lui même.",
];

var counter = 0;
var elem = document.querySelector(".flash-news p");

setInterval(change, 4500);

function change() {
    elem.classList.add("hide");
    setTimeout(function() {
        elem.innerHTML = text[counter];
        elem.classList.remove("hide");
        counter++;
        if (counter >= text.length) {
            counter = 0;
        }
    }, 500);
}