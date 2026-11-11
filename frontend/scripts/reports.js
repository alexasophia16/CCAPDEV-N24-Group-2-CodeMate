document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal-backdrop");
    const btnOpen = document.getElementById("btn-open-modal");
    const btnClose = document.getElementById("btn-close-modal");

    if (btnOpen && modal) {
        btnOpen.addEventListener("click", () => {
            modal.hidden = false;
        });
    }

    const closeModal = () => {
        if (modal) modal.hidden = true;
    };

    if (btnClose) {
        btnClose.addEventListener("click", closeModal);
    }

    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
});
