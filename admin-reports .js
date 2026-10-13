
async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

(async()=>{await CodeMateSession.getCurrentUser();const base=await loadJSON("mock-reports.json");let a=getStored("codemate_reports",base);function render(){rows.innerHTML=a.map(r=>'<tr><td>'+esc(r.reporter)+'</td><td>'+esc(r.target)+'</td><td>'+esc(r.reason)+'</td><td><span class="role-pill '+(r.status==="pending"?"pending":"")+'">'+r.status+'</span></td><td>'+(r.status==="pending"?'<button class="role-btn primary done" data-id="'+r.id+'">Mark reviewed</button>':'—')+'</td></tr>').join("");document.querySelectorAll(".done").forEach(b=>b.onclick=()=>{a.find(x=>x.id===b.dataset.id).status="reviewed";save("codemate_reports",a);render()})}render()})();