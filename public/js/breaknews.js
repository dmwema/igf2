var text = [
  "Consectetur in doloremque tenetuunt. Commodi dolorum sit nulla repellat alias delectus illum tenetur perspiciatis",
  "Lorem ipsum dolor sit amet consectetur adipisicing elit.",
  "Commodi dolorum sit nulla repellat alias delectus illum tenetur perspiciatis",
  "Amphithéâtre “Félix Antoine Tshisekedi”, inauguré ce mardi à l ‘ IGF PAR FELIX A.TSHISEKEDI lui même.",
];

var counter = 0;
var elem = document.querySelector(".flash-news p");

setInterval(change, 4500);

function change() {
  elem.classList.add("hide");
  setTimeout(function () {
    elem.innerHTML = text[counter];
    elem.classList.remove("hide");
    counter++;
    if (counter >= text.length) {
      counter = 0;
    }
  }, 500);
}
