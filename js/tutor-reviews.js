async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

(async () => {
  const u = await CodeMateSession.getCurrentUser();
  const [ts, rs] = await Promise.all([
    loadJSON("data/mock-tutors.json"),
    loadJSON("data/mock-reviews.json")
  ]);

  const summaryEl = document.getElementById("summary");
  const reviewListEl = document.getElementById("review-list");
  const filterWrap = document.getElementById("tutor-filter-wrap");
  const filterEl = document.getElementById("tutor-filter");

  const isTutor = u.role === "tutor";
  const tutorName = id => (ts.find(x => x.id === id) || {}).name || "Unknown tutor";

  function render(list, tutor) {
    const avg = list.length
      ? (list.reduce((s, r) => s + r.rating, 0) / list.length).toFixed(1)
      : "—";

    if (summaryEl) {
      // credibility is per tutor, so only show it when one tutor is selected
      const cred = tutor ? ` · credibility ${tutor.credibility.toFixed(1)}` : "";
      summaryEl.innerHTML = `<h2>Review Summary</h2><p><strong>${avg}/5</strong> average · ${list.length} review(s)${cred}</p>`;
    }

    if (reviewListEl) {
      reviewListEl.innerHTML = list.length
        ? list.map(r => {
            // tutors only see their own reviews, so skip the tutor name for them
            const about = isTutor ? "" : `<span class="role-pill">${esc(tutorName(r.tutorId))}</span> `;
            return `<div class="role-card" style="margin-bottom:10px">${about}<strong>${"★".repeat(r.rating)}</strong><p style="margin-top:6px">${esc(r.comment)}</p><small>${esc(r.reviewer)} · ${esc(r.subject)} · ${r.date}</small></div>`;
          }).join("")
        : '<div class="role-card">No reviews yet.</div>';
    }
  }

  if (isTutor) {
    // tutors see the reviews they received
    const t = ts.find(x => x.userId === u.id) || ts.find(x => x.name === u.name) || ts[0];
    render(rs.filter(r => r.tutorId === t.id), t);
    return;
  }

  // tutees and admins see every review, and can narrow it down by tutor
  if (filterEl && filterWrap) {
    filterEl.innerHTML = '<option value="">All tutors</option>' +
      ts.map(t => `<option value="${t.id}">${esc(t.name)}</option>`).join("");
    filterWrap.hidden = false;
    filterEl.addEventListener("change", () => {
      const tutor = ts.find(t => String(t.id) === filterEl.value);
      render(tutor ? rs.filter(r => r.tutorId === tutor.id) : rs, tutor);
    });
  }

  render(rs, null);
})();
