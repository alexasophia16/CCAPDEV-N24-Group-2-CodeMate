// Opens the shared report pop-up (partials/report-modal.php) when a Report button is clicked.
// Each button has data-target-type (User, Discussion, Reply, Resource) and data-target-id,
// plus an optional data-target-label that is shown to the user instead of the id.
(function () {
  var modal = document.getElementById('report-modal-backdrop');
  if (!modal) return;

  var typeInput = document.getElementById('report-target-type');
  var idInput = document.getElementById('report-target-id');
  var label = document.getElementById('report-target-label');
  var reason = document.getElementById('report-reason');

  function close() {
    modal.hidden = true;
  }

  document.querySelectorAll('.btn-open-report-modal').forEach(function (button) {
    button.addEventListener('click', function () {
      typeInput.value = button.dataset.targetType;
      idInput.value = button.dataset.targetId;
      label.textContent = button.dataset.targetLabel || button.dataset.targetType;
      modal.hidden = false;
      reason.focus();
    });
  });

  document.getElementById('report-cancel').addEventListener('click', close);
  // clicking the dark area outside the box, or pressing Esc, also closes it
  modal.addEventListener('click', function (event) {
    if (event.target === modal) close();
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !modal.hidden) close();
  });
})();
