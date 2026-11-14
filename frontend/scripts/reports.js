document.addEventListener('DOMContentLoaded', () => {
    const modalBackdrop = document.getElementById('modal-backdrop');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const hiddenTargetId = document.getElementById('target-id');

    // If opening from a generic button on the page
    if (btnOpenModal) {
        btnOpenModal.addEventListener('click', () => {
            // Grab the ID from the page URL 
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('id')) {
                hiddenTargetId.value = urlParams.get('id');
            }
            modalBackdrop.hidden = false;
        });
    }

    // If you ever add specific report buttons to discussions/profiles 
    document.querySelectorAll('.btn-report-item').forEach(button => {
        button.addEventListener('click', (e) => {
            hiddenTargetId.value = e.target.getAttribute('data-id');
            modalBackdrop.hidden = false;
        });
    });

    if (btnCloseModal) {
        btnCloseModal.addEventListener('click', () => {
            modalBackdrop.hidden = true;
        });
    }
});
