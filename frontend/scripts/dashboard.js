// Draws the dashboard charts (same look as the Phase 1 UI).
// Data is rendered by DashboardController/index.php into #dashboard-chart-data.

(function () {
  var dataEl = document.getElementById("dashboard-chart-data");
  var subjectCanvas = document.getElementById("chart-bookings-by-subject");
  var statusCanvas = document.getElementById("chart-booking-status");
  if (!dataEl || (!subjectCanvas && !statusCanvas)) return;

  function showFallback(canvas) {
    if (!canvas) return;
    var msg = document.createElement("p");
    msg.className = "dashboard-empty";
    msg.textContent = "Charts could not be loaded. Check your internet connection and refresh.";
    canvas.parentNode.replaceWith(msg);
  }

  if (typeof Chart === "undefined") {
    showFallback(subjectCanvas);
    showFallback(statusCanvas);
    return;
  }

  var data;
  try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }

  var statusColors = ["#1F5C3D", "#C99A3B", "#3C6E8F", "#B3492F"]; // confirmed, pending, completed, cancelled

  if (subjectCanvas) {
    new Chart(subjectCanvas, {
      type: "bar",
      data: {
        labels: data.subjects.labels,
        datasets: [{
          label: "Bookings",
          data: data.subjects.values,
          backgroundColor: "#1F5C3D",
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  if (statusCanvas) {
    new Chart(statusCanvas, {
      type: "doughnut",
      data: {
        labels: data.status.labels,
        datasets: [{ data: data.status.values, backgroundColor: statusColors }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true, position: "bottom", labels: { boxWidth: 10, font: { size: 11 } } } }
      }
    });
  }
})();
