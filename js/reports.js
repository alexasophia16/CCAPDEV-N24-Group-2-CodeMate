async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

document.addEventListener("DOMContentLoaded", async () => {
  const rows = document.getElementById("rows");
  const modal = document.getElementById("modal-backdrop");
  const btnOpen = document.getElementById("btn-open-modal");
  const btnClose = document.getElementById("btn-close-modal");
  const form = document.getElementById("file-report-form");

  const user = (typeof CodeMateSession !== "undefined")
    ? await CodeMateSession.getCurrentUser().catch(() => null)
    : null;

  const currentUserName = user ? (user.name || user.username || "") : "";
  const isTutor = user && user.role === "tutor";
  const mockFile = isTutor ? "data/mock-reports-tutor.json" : "data/mock-reports-tutee.json";
  const storageKey = isTutor ? "codemate_reports_tutor" : "codemate_reports_tutee";

  let base = [];
  try {
    base = await loadJSON(mockFile);
  } catch(e) {
    try {
      base = await loadJSON("data/mock-reports.json");
    } catch(err) {
      base = [];
    }
  }

  let reportsData = getStored(storageKey, base);

  if (btnOpen) {
    btnOpen.onclick = () => {
      modal.hidden = false;
    };
  }

  if (btnClose) {
    btnClose.onclick = () => {
      modal.hidden = true;
      form.reset();
    };
  }

  if (modal) {
    modal.onclick = (e) => {
      if (e.target === modal) {
        modal.hidden = true;
        form.reset();
      }
    };
  }

  function render() {
    let listToDisplay = reportsData;

    if (currentUserName) {
      const userMatches = reportsData.filter(r => r.reporter && r.reporter.toLowerCase().trim() === currentUserName.toLowerCase().trim());
      if (userMatches.length > 0) {
        listToDisplay = userMatches;
      }
    }

    if (listToDisplay.length === 0) {
      rows.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--ink-faint); padding:32px;">You haven\'t submitted any reports yet.</td></tr>';
      return;
    }

    rows.innerHTML = listToDisplay.map(r => 
      '<tr><td>' + esc(r.reporter) + '</td><td>' + esc(r.target) + '</td><td>' + esc(r.reason) + '</td><td><span class="role-pill ' + (r.status === "pending" ? "pending" : "") + '">' + esc(r.status) + '</span></td></tr>'
    ).join("");
  }

  if (form) {
    form.onsubmit = (e) => {
      e.preventDefault();
      const newReport = {
        id: "rep_" + Date.now(),
        reporter: currentUserName || (isTutor ? "Sample Tutor" : "Alexa Canaria"),
        target: document.getElementById("target-input").value.trim(),
        reason: document.getElementById("reason-input").value.trim(),
        status: "pending",
        createdAt: new Date().toISOString().slice(0, 10)
      };

      reportsData.unshift(newReport);
      save(storageKey, reportsData);

      modal.hidden = true;
      form.reset();
      render();
    };
  }

  render();
});