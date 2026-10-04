document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("profileInput");
    const previewImage = document.getElementById("previewImage");
    const placeholder = document.getElementById("previewPlaceholder");

    if (input) {
        input.addEventListener("change", function () {
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = "block";

                    if (placeholder) {
                        placeholder.style.display = "none";
                    }
                };

                reader.readAsDataURL(file);
            }
        });
    }

});