const backgroundMusic = document.getElementById("backgroundMusic");
const clickSound = document.getElementById("clickSound");

backgroundMusic.volume = 0.5;

const savedTime = localStorage.getItem("musicTime");

if (savedTime) {
    backgroundMusic.currentTime = parseFloat(savedTime);
}

backgroundMusic.play().catch(function() {});

document.addEventListener("click", function(event) {

    if (backgroundMusic.paused) {
        backgroundMusic.play().catch(function() {});
    }

    const link = event.target.closest("a");

    if (link) {

        event.preventDefault();

        clickSound.currentTime = 0;

        clickSound.play().catch(function() {});

        localStorage.setItem(
            "musicTime",
            backgroundMusic.currentTime
        );

        setTimeout(function() {
            window.location.href = link.href;
        }, 500);

    }

});

setInterval(function() {

    if (!backgroundMusic.paused) {

        localStorage.setItem(
            "musicTime",
            backgroundMusic.currentTime
        );

    }

}, 500);