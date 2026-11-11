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
    "'": "&#039;"
  }[c]));
}

(async () => {
  const u = await CodeMateSession.getCurrentUser(),
        base = await loadJSON("data/mock-resources.json");
  let a = getStored("codemate_resources", base);

  const resourceList = document.getElementById("resource-list"),
        uploadBtn = document.getElementById("upload-btn"),
        uploadCard = document.getElementById("upload-card"),
        uploadForm = document.getElementById("upload-form"),
        titleInput = document.getElementById("title"),
        courseInput = document.getElementById("course"),
        typeInput = document.getElementById("type");

  if (u.role === "tutor") uploadBtn.hidden = false;

  function render() {
    resourceList.innerHTML = a
      .filter(x => x.status === "approved" || u.role === "admin" || x.author === u.name)
      .map(x => '<div class="role-card"><span class="role-pill">' + x.course + '</span><h2 style="margin-top:8px">' + esc(x.title) + '</h2><p>' + x.type + ' · ' + esc(x.author) + '</p><button class="role-btn secondary" style="margin-top:10px">Preview</button></div>')
      .join("");
  }

  uploadBtn.onclick = () => {
    uploadCard.hidden = !uploadCard.hidden;
  };

  uploadForm.onsubmit = e => {
    e.preventDefault();
    a.push({
      id: "res" + Date.now(),
      title: titleInput.value,
      course: courseInput.value,
      type: typeInput.value,
      author: u.name,
      status: "pending"
    });
    save("codemate_resources", a);
    uploadForm.reset();
    render();
    alert("Resource submitted for moderation.");
  };

  render();
})();