async function loadJSON(path) {
  const r = await fetch(path);
  if (!r.ok) throw new Error(path);
  return r.json();
}

function getStored(k, f) {
  try {
    return JSON.parse(localStorage.getItem(k)) || f;
  } catch (e) {
    return f;
  }
}

function save(k, v) {
  localStorage.setItem(k, JSON.stringify(v));
}

function esc(s) {
  return String(s ?? "").replace(/[&<>"']/g, c => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;" // Fixed the single quote escape sequence
  }[c]));
}

(async () => {
  // Assuming these dependencies are handled elsewhere
  await CodeMateSession.getCurrentUser();
  const base = await loadJSON("data/mock-tutors.json");
  let a = getStored("codemate_admin_tutors", base);

  // BUG FIX 1: Explicitly target the container element
  const rows = document.getElementById("rows") || document.querySelector(".tutor-rows");

  function render() {
    if (!rows) return; // Prevent errors if the element isn't in the DOM yet

    rows.innerHTML = a.map(t => `
      <tr>
        <td>${esc(t.name)}</td>
        <td>${esc(t.subjects.join(", "))}</td>
        <td>${esc(t.experience)}</td>
        <td>
          <span class="role-pill ${t.verified ? '' : 'pending'}">
            ${t.verified ? 'Verified' : 'Pending'}
          </span>
        </td>
        <td>
          ${t.verified ? '—' : `<button class="role-btn primary approve" data-id="${t.id}">Approve</button>`}
        </td>
      </tr>
    `).join("");

    // Re-attach event listeners to the newly created elements
    document.querySelectorAll(".approve").forEach(b => {
      b.onclick = () => {
        // BUG FIX 3: Ensure data-id mapping matches correctly
        const tutor = a.find(x => String(x.id) === String(b.dataset.id));
        if (tutor) {
          tutor.verified = true;
          save("codemate_admin_tutors", a);
          render(); // Re-render to update UI
        }
      };
    });
  }

  render();
})();
