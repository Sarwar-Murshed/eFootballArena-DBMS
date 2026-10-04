const searchInput = document.getElementById("searchTournament");
const statusFilter = document.getElementById("statusFilter");
const cards = document.querySelectorAll(".tournament-card");

function filterTournaments() {

    const searchText = searchInput.value.toLowerCase();
    const selectedStatus = statusFilter.value;

    cards.forEach(function(card) {

        const name = card.dataset.name;
        const status = card.dataset.status;

        const searchMatch = name.includes(searchText);

        const statusMatch =
            selectedStatus === "all" ||
            status === selectedStatus;

        if(searchMatch && statusMatch) {
            card.style.display = "block";
        }
        else {
            card.style.display = "none";
        }

    });

}

searchInput.addEventListener("input", filterTournaments);

statusFilter.addEventListener("change", filterTournaments);