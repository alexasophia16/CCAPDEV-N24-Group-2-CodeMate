document.addEventListener("DOMContentLoaded", () => {
    const uploadBtn = document.getElementById("upload-btn");
    const uploadCard = document.getElementById("upload-card");

    if (uploadBtn && uploadCard) {
        uploadBtn.addEventListener("click", () => {
            uploadCard.hidden = !uploadCard.hidden;
        });
    }
});
