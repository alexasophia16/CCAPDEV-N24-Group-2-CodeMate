// ask before cancelling a booking
document.querySelectorAll(".confirm-cancel").forEach(function (form) {
  form.addEventListener("submit", function (event) {
    if (!confirm("Cancel this booking? The slot will be opened for other students.")) {
      event.preventDefault();
    }
  });
});
