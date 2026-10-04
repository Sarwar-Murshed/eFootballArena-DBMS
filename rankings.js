document.addEventListener("DOMContentLoaded", function () {

    const backgroundMusic =
        document.getElementById("backgroundMusic");

    const clickSound =
        document.getElementById("clickSound");

    function startMusic() {

        if (backgroundMusic) {

            backgroundMusic.volume = 0.25;

            backgroundMusic.play().catch(function () {
                // Browser blocked autoplay.
            });

        }

    }

    document.addEventListener(
        "click",
        startMusic,
        { once: true }
    );

    const clickableElements =
        document.querySelectorAll(
            "a, button"
        );


    clickableElements.forEach(function (element) {

        element.addEventListener("click", function () {

            if (clickSound) {

                clickSound.currentTime = 0;

                clickSound.volume = 0.6;

                clickSound.play().catch(function () {
                    // Audio blocked by browser.
                });

            }

        });

    });


});