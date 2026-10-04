document.addEventListener("DOMContentLoaded", function () {

    const formations = {
        "4-3-3": [
            { pos: "GK", top: "86%", left: "46%" },
            { pos: "LB", top: "66%", left: "10%" },
            { pos: "CB", top: "71%", left: "30%" },
            { pos: "CB", top: "71%", left: "62%" },
            { pos: "RB", top: "66%", left: "82%" },
            { pos: "CM", top: "49%", left: "20%" },
            { pos: "CM", top: "46%", left: "46%" },
            { pos: "CM", top: "49%", left: "72%" },
            { pos: "LW", top: "23%", left: "14%" },
            { pos: "ST", top: "15%", left: "46%" },
            { pos: "RW", top: "23%", left: "78%" }
        ],
        "4-2-3-1": [
            { pos: "GK", top: "86%", left: "46%" },
            { pos: "LB", top: "66%", left: "10%" },
            { pos: "CB", top: "71%", left: "30%" },
            { pos: "CB", top: "71%", left: "62%" },
            { pos: "RB", top: "66%", left: "82%" },
            { pos: "CDM", top: "53%", left: "30%" },
            { pos: "CDM", top: "53%", left: "62%" },
            { pos: "CAM", top: "33%", left: "46%" },
            { pos: "LW", top: "25%", left: "14%" },
            { pos: "ST", top: "14%", left: "46%" },
            { pos: "RW", top: "25%", left: "78%" }
        ],
        "4-4-2": [
            { pos: "GK", top: "86%", left: "46%" },
            { pos: "LB", top: "66%", left: "10%" },
            { pos: "CB", top: "71%", left: "30%" },
            { pos: "CB", top: "71%", left: "62%" },
            { pos: "RB", top: "66%", left: "82%" },
            { pos: "LM", top: "43%", left: "10%" },
            { pos: "CM", top: "46%", left: "32%" },
            { pos: "CM", top: "46%", left: "60%" },
            { pos: "RM", top: "43%", left: "82%" },
            { pos: "ST", top: "17%", left: "32%" },
            { pos: "ST", top: "17%", left: "60%" }
        ],
        "4-3-1-2": [
            { pos: "GK", top: "86%", left: "46%" },
            { pos: "LB", top: "66%", left: "10%" },
            { pos: "CB", top: "71%", left: "30%" },
            { pos: "CB", top: "71%", left: "62%" },
            { pos: "RB", top: "66%", left: "82%" },
            { pos: "CM", top: "51%", left: "20%" },
            { pos: "CM", top: "49%", left: "46%" },
            { pos: "CM", top: "51%", left: "72%" },
            { pos: "CAM", top: "31%", left: "46%" },
            { pos: "ST", top: "15%", left: "30%" },
            { pos: "ST", top: "15%", left: "62%" }
        ],
        "5-3-2": [
            { pos: "GK", top: "86%", left: "46%" },
            { pos: "LWB", top: "59%", left: "6%" },
            { pos: "CB", top: "69%", left: "24%" },
            { pos: "CB", top: "73%", left: "46%" },
            { pos: "CB", top: "69%", left: "68%" },
            { pos: "RWB", top: "59%", left: "86%" },
            { pos: "CM", top: "45%", left: "24%" },
            { pos: "CM", top: "41%", left: "46%" },
            { pos: "CM", top: "45%", left: "68%" },
            { pos: "ST", top: "16%", left: "32%" },
            { pos: "ST", top: "16%", left: "60%" }
        ]
    };

    const pitch = document.getElementById("pitch");
    const formationSelect = document.getElementById("formationSelect");
    const benchList = document.getElementById("benchList");
    const benchPlayers = Array.from(document.querySelectorAll(".bench-player"));

    let lineup = {};
    let selected = [];
    let customPositions = {};

    let savedLineupState = null;

    function playerData(element) {
        return {
            id: element.dataset.id,
            name: element.dataset.name,
            rating: element.dataset.rating,
            img: element.dataset.img
        };
    }

    function cloneState(state) {
        if (!state) return null;
        return JSON.parse(JSON.stringify(state));
    }

    function getCurrentState() {
        return {
            formation: formationSelect.value,
            players: cloneState(lineup),
            customPositions: cloneState(customPositions)
        };
    }

    function statesEqual(a, b) {
        return JSON.stringify(a) === JSON.stringify(b);
    }

    function updateDefaultButton() {
        const button = document.getElementById("defaultButton");

        if (!button || !savedLineupState) return;

        const currentState = getCurrentState();

        if (statesEqual(currentState, savedLineupState)) {
            button.classList.remove("changed");
            button.disabled = true;
        } else {
            button.classList.add("changed");
            button.disabled = false;
        }
    }

    function clearSelection() {
        document.querySelectorAll(".slot.swap-selected").forEach(el => {
            el.classList.remove("swap-selected");
        });

        document.querySelectorAll(".bench-player.selected").forEach(el => {
            el.classList.remove("selected");
        });

        selected = [];

        const button = document.getElementById("swapButton");

        if (button) {
            button.remove();
        }
    }

    function createSwapButton() {
        let button = document.getElementById("swapButton");

        if (!button) {
            button = document.createElement("button");
            button.id = "swapButton";
            button.className = "swap-action";

            button.innerHTML = `
                <span class="swap-icon">↔</span>
                <span>SWAP</span>
            `;

            document.body.appendChild(button);

            button.addEventListener("click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                performSwap();
            });
        }

        positionSwapButton(button);
    }

    function positionSwapButton(button) {
        if (selected.length !== 2) return;

        const firstRect = selected[0].element.getBoundingClientRect();
        const secondRect = selected[1].element.getBoundingClientRect();

        const x =
            (
                (firstRect.left + firstRect.right) / 2 +
                (secondRect.left + secondRect.right) / 2
            ) / 2;

        const y =
            (
                (firstRect.top + firstRect.bottom) / 2 +
                (secondRect.top + secondRect.bottom) / 2
            ) / 2;

        button.style.display = "flex";
        button.style.left = (x - 29) + "px";
        button.style.top = (y - 29) + "px";
    }

    function selectPlayer(type, index, element) {

        if (selected.length === 2) {
            clearSelection();
        }

        const alreadySelected = selected.find(
            item => item.type === type && item.index === index
        );

        if (alreadySelected) {
            clearSelection();
            return;
        }

        selected.push({
            type,
            index,
            element
        });

        if (type === "slot") {
            element.classList.add("swap-selected");
        }

        if (type === "bench") {
            element.classList.add("selected");
        }

        if (selected.length === 2) {
            createSwapButton();
        }
    }

    function enableFreeDrag(slot, index) {

        const card = slot.querySelector(".slot-card");

        if (!card) return;

        let isDragging = false;

        card.style.cursor = "grab";

        card.addEventListener("mousedown", function (e) {

            if (e.button !== 0) return;

            isDragging = true;

            slot.style.zIndex = 50;
            card.style.cursor = "grabbing";

            e.preventDefault();
            e.stopPropagation();
        });

        document.addEventListener("mousemove", function (e) {

            if (!isDragging) return;

            const pitchRect = pitch.getBoundingClientRect();

            let newLeft =
                ((e.clientX - pitchRect.left) / pitchRect.width) * 100;

            let newTop =
                ((e.clientY - pitchRect.top) / pitchRect.height) * 100;

            newLeft = Math.max(5, Math.min(90, newLeft));
            newTop = Math.max(8, Math.min(90, newTop));

            slot.style.left = newLeft + "%";
            slot.style.top = newTop + "%";

            customPositions[index] = {
                top: newTop + "%",
                left: newLeft + "%"
            };

            updateDefaultButton();
        });

        document.addEventListener("mouseup", function () {

            if (!isDragging) return;

            isDragging = false;

            slot.style.zIndex = 5;
            card.style.cursor = "grab";

            updateDefaultButton();
        });
    }

    function renderSlot(index) {

        const slot = document.querySelector(
            '.slot[data-index="' + index + '"]'
        );

        if (!slot) return;

        const player = lineup[index];

        if (player) {

            slot.classList.remove("empty");

            slot.innerHTML = `
                <div class="slot-card">
                    <img src="${player.img}" alt="${player.name}">
                    <div class="slot-rating">${player.rating}</div>
                </div>
                <div class="slot-name">${player.name.split(" ").pop()}</div>
            `;

            enableFreeDrag(slot, index);

        } else {

            slot.classList.add("empty");

            slot.innerHTML = `
                <div class="slot-card">
                    <div class="empty-text">${slot.dataset.pos}</div>
                </div>
                <div class="slot-name">${slot.dataset.pos}</div>
            `;
        }
    }

    function refreshBench() {

        benchPlayers.forEach(player => {

            const used = Object.values(lineup).some(
                item => item && item.id === player.dataset.id
            );

            player.classList.toggle("used", used);

            if (used) {
                player.classList.remove("selected");
            }
        });
    }

    function performSwap() {

        if (selected.length !== 2) return;

        const first = selected[0];
        const second = selected[1];

        if (first.type === "slot" && second.type === "slot") {

            const temp = lineup[first.index];

            lineup[first.index] = lineup[second.index];
            lineup[second.index] = temp;

            renderSlot(first.index);
            renderSlot(second.index);
        }

        else if (first.type === "bench" && second.type === "bench") {

            const firstElement = first.element;
            const secondElement = second.element;

            const elements = Array.from(
                benchList.querySelectorAll(".bench-player")
            );

            const firstIndex = elements.indexOf(firstElement);
            const secondIndex = elements.indexOf(secondElement);

            if (firstIndex !== -1 && secondIndex !== -1) {

                if (firstIndex < secondIndex) {

                    benchList.insertBefore(
                        secondElement,
                        firstElement
                    );

                    benchList.insertBefore(
                        firstElement,
                        elements[secondIndex]
                    );

                } else {

                    benchList.insertBefore(
                        firstElement,
                        secondElement
                    );

                    benchList.insertBefore(
                        secondElement,
                        elements[firstIndex]
                    );
                }
            }
        }

        else {

            let slot;
            let bench;

            if (first.type === "slot") {
                slot = first;
                bench = second;
            } else {
                slot = second;
                bench = first;
            }

            const oldPlayer = lineup[slot.index];
            const newPlayer = playerData(bench.element);

            lineup[slot.index] = newPlayer;

            renderSlot(slot.index);

            if (oldPlayer) {

                const oldBenchElement = benchPlayers.find(
                    el => el.dataset.id === oldPlayer.id
                );

                if (oldBenchElement) {

                    oldBenchElement.classList.remove("used");

                    benchList.appendChild(oldBenchElement);
                }
            }
        }

        refreshBench();
        clearSelection();

        updateDefaultButton();
    }

    function getBestPlayer(position, used) {

        const types = {
            GK: ["GK"],
            LB: ["DF"],
            RB: ["DF"],
            CB: ["DF"],
            LWB: ["DF"],
            RWB: ["DF"],
            CDM: ["MF"],
            CM: ["MF"],
            CAM: ["MF"],
            LM: ["MF", "FW"],
            RM: ["MF", "FW"],
            LW: ["FW", "MF"],
            RW: ["FW", "MF"],
            ST: ["FW"]
        };

        const preferred = types[position] || ["MF"];

        for (const type of preferred) {

            const candidates = benchPlayers.filter(
                p =>
                    !used.has(p.dataset.id) &&
                    p.dataset.position === type
            );

            if (candidates.length > 0) {

                candidates.sort(
                    (a, b) =>
                        Number(b.dataset.rating) -
                        Number(a.dataset.rating)
                );

                return candidates[0];
            }
        }

        const remaining = benchPlayers.filter(
            p => !used.has(p.dataset.id)
        );

        remaining.sort(
            (a, b) =>
                Number(b.dataset.rating) -
                Number(a.dataset.rating)
        );

        return remaining[0] || null;
    }

    function loadFormation(formation, useSavedPlayers = true) {

        pitch.innerHTML = "";

        lineup = {};

        clearSelection();

        const positions =
            formations[formation] ||
            formations["4-3-3"];

        let saved = null;

        if (useSavedPlayers) {

            const savedData =
                localStorage.getItem("lineup_" + managerId);

            if (savedData) {

                try {
                    saved = JSON.parse(savedData);
                } catch (e) {
                    saved = null;
                }
            }
        }

        customPositions = {};

        if (
            saved &&
            saved.formation === formation &&
            saved.customPositions
        ) {
            customPositions = cloneState(saved.customPositions);
        }

        const used = new Set();

        positions.forEach((item, index) => {

            const slot = document.createElement("div");

            slot.className = "slot empty";

            slot.dataset.index = index;
            slot.dataset.pos = item.pos;

            if (customPositions[index]) {

                slot.style.top =
                    customPositions[index].top;

                slot.style.left =
                    customPositions[index].left;

            } else {

                slot.style.top = item.top;
                slot.style.left = item.left;
            }

            let selectedPlayer = null;

            if (
                saved &&
                saved.formation === formation &&
                saved.players &&
                saved.players[index]
            ) {

                selectedPlayer =
                    saved.players[index];

                const exists =
                    benchPlayers.some(
                        p =>
                            p.dataset.id ===
                            selectedPlayer.id
                    );

                if (!exists) {
                    selectedPlayer = null;
                }
            }

            if (!selectedPlayer) {

                const best =
                    getBestPlayer(item.pos, used);

                if (best) {
                    selectedPlayer =
                        playerData(best);
                }
            }

            if (selectedPlayer) {

                used.add(selectedPlayer.id);

                lineup[index] =
                    selectedPlayer;

                slot.classList.remove("empty");

                slot.innerHTML = `
                    <div class="slot-card">
                        <img src="${selectedPlayer.img}" alt="${selectedPlayer.name}">
                        <div class="slot-rating">${selectedPlayer.rating}</div>
                    </div>
                    <div class="slot-name">${selectedPlayer.name.split(" ").pop()}</div>
                `;

                enableFreeDrag(slot, index);

            } else {

                slot.innerHTML = `
                    <div class="slot-card">
                        <div class="empty-text">${item.pos}</div>
                    </div>
                    <div class="slot-name">${item.pos}</div>
                `;
            }

            slot.addEventListener("click", function (event) {

                event.preventDefault();
                event.stopPropagation();

                if (!lineup[index]) return;

                selectPlayer(
                    "slot",
                    index,
                    slot
                );
            });

            pitch.appendChild(slot);
        });

        refreshBench();
    }

    function saveLineup(silent = false) {

        const state = getCurrentState();

        localStorage.setItem(
            "lineup_" + managerId,
            JSON.stringify(state)
        );

        savedLineupState =
            cloneState(state);

        updateDefaultButton();

        if (!silent) {
            alert("Lineup saved successfully!");
        }
    }

    function restoreDefault() {

        if (!savedLineupState) return;

        formationSelect.value =
            savedLineupState.formation;

        localStorage.setItem(
            "lineup_" + managerId,
            JSON.stringify(savedLineupState)
        );

        loadFormation(
            savedLineupState.formation,
            true
        );

        updateDefaultButton();
    }

    const buttonContainer =
        document.createElement("div");

    buttonContainer.className =
        "lineup-buttons";

    const saveButton =
        document.createElement("button");

    saveButton.id = "saveLineupButton";
    saveButton.textContent =
        "SAVE LINEUP";

    const defaultButton =
        document.createElement("button");

    defaultButton.id = "defaultButton";
    defaultButton.textContent =
        "DEFAULT";

    defaultButton.disabled = true;

    buttonContainer.appendChild(saveButton);
    buttonContainer.appendChild(defaultButton);

    document
        .querySelector(".formation-selector")
        .appendChild(buttonContainer);

    saveButton.addEventListener(
        "click",
        function (e) {

            e.preventDefault();
            e.stopPropagation();

            saveLineup();
        }
    );

    defaultButton.addEventListener(
        "click",
        function (e) {

            e.preventDefault();
            e.stopPropagation();

            restoreDefault();
        }
    );

    benchPlayers.forEach(player => {

        player.removeAttribute("draggable");

        player.addEventListener(
            "click",
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                if (player.classList.contains("used")) {
                    return;
                }

                selectPlayer(
                    "bench",
                    benchPlayers.indexOf(player),
                    player
                );
            }
        );
    });

    formationSelect.addEventListener(
        "change",
        function () {

            customPositions = {};

            loadFormation(
                this.value,
                false
            );

            updateDefaultButton();
        }
    );

    document.addEventListener(
        "click",
        function (event) {

            if (
                !event.target.closest(".slot") &&
                !event.target.closest(".bench-player") &&
                !event.target.closest("#swapButton") &&
                !event.target.closest(".formation-selector")
            ) {
                clearSelection();
            }
        }
    );

    window.addEventListener(
        "resize",
        function () {

            const button =
                document.getElementById("swapButton");

            if (
                button &&
                selected.length === 2
            ) {
                positionSwapButton(button);
            }
        }
    );

    window.addEventListener(
        "scroll",
        function () {

            const button =
                document.getElementById("swapButton");

            if (
                button &&
                selected.length === 2
            ) {
                positionSwapButton(button);
            }
        }
    );

    const savedData =
        localStorage.getItem("lineup_" + managerId);

    if (savedData) {

        try {

            const saved =
                JSON.parse(savedData);

            savedLineupState =
                cloneState(saved);

            formationSelect.value =
                saved.formation ||
                formationSelect.value;

            loadFormation(
                formationSelect.value,
                true
            );

        } catch (e) {

            savedLineupState = null;

            loadFormation(
                formationSelect.value,
                false
            );
        }

    } else {

        loadFormation(
            formationSelect.value,
            false
        );

        savedLineupState =
            getCurrentState();

        localStorage.setItem(
            "lineup_" + managerId,
            JSON.stringify(savedLineupState)
        );
    }

    updateDefaultButton();
});