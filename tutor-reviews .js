
async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

(async () => {
  const u = await CodeMateSession.getCurrentUser();
  const [ts, rs] = await Promise.all([
    loadJSON("mock-tutors.json"),
    loadJSON("mock-reviews.json")
  ]);

  const t = ts.find(x => x.userId === u.id) || ts[0];
  const mine = rs.filter(r => r.tutorId === t.id);
  const avg = mine.length
    ? (mine.reduce((s, r) => s + r.rating, 0) / mine.length).toFixed(1)
    : "—";

  const summaryEl = document.getElementById("summary");
  const reviewListEl = document.getElementById("review-list");

  if (summaryEl) {
    summaryEl.innerHTML = `<h2>Review Summary</h2><p><strong>${avg}/5</strong> average · ${mine.length} review(s) · credibility ${t.credibility.toFixed(1)}</p>`;
  }

  if (reviewListEl) {
    reviewListEl.innerHTML = mine.length
      ? mine.map(r => `<div class="role-card" style="margin-bottom:10px"><strong>${"★".repeat(r.rating)}</strong><p style="margin-top:6px">${esc(r.comment)}</p><small>${esc(r.reviewer)} · ${r.date}</small></div>`).join("")
      : '<div class="role-card">No reviews yet.</div>';
  }
})();