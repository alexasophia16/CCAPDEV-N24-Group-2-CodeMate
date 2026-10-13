// Loads mock data, figures out which stats/charts to show based on whoever is signed in right now (see session.js), and renders everything.

(function () {

  async function loadJSON(path) {
    const res = await fetch(path);
    if (!res.ok) throw new Error("Failed to load " + path);
    return res.json();
  }

  function initials(name) {
    return name.split(" ").map(function (w) { return w[0]; }).slice(0, 2).join("");
  }

  // stats
  function renderStats(currentUser, bookings, tutors, discussions) {
    const verifiedCount = tutors.filter(function (t) { return t.verified; }).length;
    const avgCredibility = (tutors.reduce(function (sum, t) { return sum + t.credibility; }, 0) / tutors.length).toFixed(1);

    let stats;

    if (currentUser.role === "admin") {
      const uniqueSubjects = new Set();
      tutors.forEach(function (t) { t.subjects.forEach(function (s) { uniqueSubjects.add(s); }); });
      const pendingVerifications = tutors.filter(function (t) { return !t.verified; }).length;

      stats = [
        { label: "Registered Users", value: 128 },
        { label: "Verified Tutors", value: verifiedCount },
        { label: "Active Courses", value: uniqueSubjects.size },
        { label: "Pending Verifications", value: pendingVerifications }
      ];
    } else if (currentUser.role === "tutor") {
      stats = [
        { label: "Booking Requests", value: bookings.filter(function (b) { return b.status === "pending"; }).length },
        { label: "Upcoming Sessions", value: bookings.filter(function (b) { return b.status === "confirmed"; }).length },
        { label: "Completed Sessions", value: bookings.filter(function (b) { return b.status === "completed"; }).length },
        { label: "Open Discussions", value: discussions.length }
      ];
    } else {
      stats = [
        { label: "Total bookings", value: bookings.length },
        { label: "Verified Tutors", value: verifiedCount },
        { label: "Avg. Credibility", value: avgCredibility },
        { label: "Open Discussions", value: discussions.length }
      ];
    }

    stats.forEach(function (stat, i) {
      const n = i + 1;
      document.getElementById("stat-" + n + "-label").textContent = stat.label;
      document.getElementById("stat-" + n + "-value").textContent = stat.value;
    });
  }

  // charts
  function renderCharts(currentUser, bookings) {
    const subjectTitle = document.getElementById("chart-1-title");
    const statusTitle = document.getElementById("chart-2-title");

    subjectTitle.textContent = currentUser.role === "tutor" ? "Your sessions by subject" : "Bookings by subject";
    statusTitle.textContent = "Bookings by status";

    const subjectCounts = {};
    bookings.forEach(function (b) {
      subjectCounts[b.subject] = (subjectCounts[b.subject] || 0) + 1;
    });

    new Chart(document.getElementById("chart-bookings-by-subject"), {
      type: "bar",
      data: {
        labels: Object.keys(subjectCounts),
        datasets: [{
          label: "Bookings",
          data: Object.values(subjectCounts),
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

    const statuses = ["confirmed", "pending", "completed", "cancelled"];
    const statusColors = { confirmed: "#1F5C3D", pending: "#C99A3B", completed: "#3C6E8F", cancelled: "#B3492F" };
    const statusCounts = statuses.map(function (s) {
      return bookings.filter(function (b) { return b.status === s; }).length;
    });

    new Chart(document.getElementById("chart-booking-status"), {
      type: "doughnut",
      data: {
        labels: statuses.map(function (s) { return s[0].toUpperCase() + s.slice(1); }),
        datasets: [{
          data: statusCounts,
          backgroundColor: statuses.map(function (s) { return statusColors[s]; })
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true, position: "bottom", labels: { boxWidth: 10, font: { size: 11 } } } }
      }
    });
  }

  // featured tutors
  function renderFeaturedTutors(tutors) {
    const container = document.getElementById("featured-tutors-list");
    container.innerHTML = tutors.map(function (t) {
      const badgeClass = t.verified ? "verified" : "pending";
      const badgeText = t.verified ? "Verified" : "Pending";
      const tagsHtml = t.subjects.map(function (s) { return '<span class="tag">' + s + "</span>"; }).join("");

      return (
        '<div class="tutor-card">' +
          '<div class="tutor-top">' +
            '<div class="tutor-avatar">' + initials(t.name) + "</div>" +
            "<div>" +
              '<div class="tutor-name">' + t.name + "</div>" +
              '<div class="tutor-cred">' + t.credibility.toFixed(1) + " - " + t.sessions + " sessions</div>" +
            "</div>" +
            '<span class="tutor-badge ' + badgeClass + '">' + badgeText + "</span>" +
          "</div>" +
          '<div class="tutor-tags">' + tagsHtml + "</div>" +
          '<button class="btn-book" ' + (t.verified ? "" : "disabled") + ">" +
            (t.verified ? "Book session" : "Not yet bookable") +
          "</button>" +
        "</div>"
      );
    }).join("");
  }

  // init
  async function init() {
    try {
      const [currentUser, tutors, bookings, discussions] = await Promise.all([
        CodeMateSession.getCurrentUser(),
        loadJSON("mock-tutors.json"),
        loadJSON("mock-bookings.json"),
        loadJSON("mock-discussions.json")
      ]);

      document.getElementById("dashboard-greeting").textContent = "Welcome back, " + currentUser.name.split(" ")[0];
      document.getElementById("dashboard-subtitle").textContent = "Here's what's happening with your tutoring activity.";

      let visibleBookings = bookings;

      if (currentUser.role === "tutee") {
        visibleBookings = bookings.filter(function (b) {
          return !b.studentId || b.studentId === currentUser.id;
        });
      } else if (currentUser.role === "tutor") {
        const tutor = tutors.find(function (t) {
          return t.userId === currentUser.id || t.name === currentUser.name;
        });
        visibleBookings = tutor ? bookings.filter(function (b) { return b.tutorId === tutor.id; }) : bookings;
      }

      renderStats(currentUser, visibleBookings, tutors, discussions);
      renderCharts(currentUser, visibleBookings);

      const section = document.querySelector(".featured-tutors");
      const sectionTitle = section ? section.querySelector(".section-title") : null;

      if (currentUser.role === "tutee") {
        renderFeaturedTutors(tutors);
      } else if (currentUser.role === "tutor") {
        if (sectionTitle) sectionTitle.textContent = "Tutor Quick Actions";
        document.getElementById("featured-tutors-list").innerHTML =
          '<div class="tutor-card"><div class="tutor-name">Manage your tutoring</div><div class="tutor-tags"><span class="tag">Profile</span><span class="tag">Slots</span><span class="tag">Requests</span><span class="tag">Reviews</span></div>' +
          '<div class="card-actions"><a class="btn-book" href="tutor-bookings.html">Booking requests</a></div></div>' +
          '<div class="tutor-card"><div class="tutor-name">Availability & profile</div><div class="tutor-cred">Keep your subjects, experience, and available schedules updated.</div>' +
          '<div class="card-actions"><a class="btn-book" href="tutor-slots.html">Manage slots</a></div></div>';
      } else {
        if (sectionTitle) sectionTitle.textContent = "Admin Quick Actions";
        document.getElementById("featured-tutors-list").innerHTML =
          '<div class="tutor-card"><div class="tutor-name">Pending tutor verification</div><div class="tutor-cred">Review tutor applications and verification status.</div><div class="card-actions"><a class="btn-book" href="admin-verify-tutors.html">Review tutors</a></div></div>' +
          '<div class="tutor-card"><div class="tutor-name">Reports & moderation</div><div class="tutor-cred">Review submitted reports and flagged content.</div><div class="card-actions"><a class="btn-book" href="admin-reports.html">Open reports</a></div></div>';
      }

    } catch (err) {
      console.error("dashboard.js failed to load mock data:", err);
      document.getElementById("dashboard-greeting").textContent = "Welcome back";
      document.getElementById("dashboard-subtitle").textContent =
        "Could not load mock data - check the browser console for which file 404'd.";
    }
  }

  init();

})();