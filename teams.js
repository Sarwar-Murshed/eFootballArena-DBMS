document.addEventListener("DOMContentLoaded", function () {

    const cards = document.querySelectorAll(".player-card");

    cards.forEach(function (card) {

        card.addEventListener("mouseenter", function () {
            card.style.cursor = "pointer";
        });

    });

});