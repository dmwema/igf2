document.addEventListener("DOMContentLoaded", function (event) {
  var derniere_position_de_scroll_connue = 0;
  var ticking = false;
  let nav = document.querySelector(".header_area");
  if (window.scrollY > 100) {
    nav.classList.add("fixed");
    console.log(nav.classList);
  } else {
    nav.classList.remove("fixed");
  }
  window.addEventListener("scroll", function (e) {
    derniere_position_de_scroll_connue = window.scrollY;
    if (!ticking) {
      window.requestAnimationFrame(function () {
        if (derniere_position_de_scroll_connue > 150) {
          nav.classList.add("fixed");
        } else {
          nav.classList.remove("fixed");
        }
        ticking = false;
      });
    }
    ticking = true;
  });
});
