document.addEventListener("DOMContentLoaded", function () {

    const profileButton = document.getElementById("profileButton");
    const profileDropdown = document.getElementById("profileDropdown");

    if (profileButton && profileDropdown) {
        profileButton.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("show");
        });

        profileDropdown.addEventListener("click", function (event) {
            event.stopPropagation();
        });

        document.addEventListener("click", function () {
            profileDropdown.classList.remove("show");
        });
    }

    const adOverlay = document.getElementById("adOverlay");
    const adImage = document.getElementById("adImage");
    const adClose = document.getElementById("adClose");

    const ads = [
        "Images/fifa-collaboration.jpg",
        "Images/dream-team.png"
    ];

    let currentAd = 0;

    if (adOverlay && adImage && adClose) {
        const adsAlreadyShown = sessionStorage.getItem("adsShown");

        if (adsAlreadyShown !== "true") {
            adImage.src = ads[0];
            adOverlay.classList.remove("hidden");

            adClose.addEventListener("click", function () {
                currentAd++;

                if (currentAd < ads.length) {
                    adImage.src = ads[currentAd];
                } else {
                    adOverlay.classList.add("hidden");
                    sessionStorage.setItem("adsShown", "true");
                }
            });
        }
    }

    const countdownEl = document.getElementById("countdown");
    if (countdownEl) {
        let time = countdownEl.textContent.split(":").map(Number);
        let totalSeconds = time[0] * 3600 + time[1] * 60 + time[2];

        setInterval(function () {
            if (totalSeconds <= 0) {
                location.reload();
                return;
            }
            totalSeconds--;
            let h = Math.floor(totalSeconds / 3600);
            let m = Math.floor((totalSeconds % 3600) / 60);
            let s = totalSeconds % 60;
            countdownEl.textContent =
                String(h).padStart(2, "0") + ":" +
                String(m).padStart(2, "0") + ":" +
                String(s).padStart(2, "0");
        }, 1000);
    }
});