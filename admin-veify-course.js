
async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

(async()=>{await CodeMateSession.getCurrentUser();const base=await loadJSON("mock-tutors.json");let a=getStored("codemate_admin_tutors",base);function render(){rows.innerHTML=a.map(t=>'<tr><td>'+esc(t.name)+'</td><td>'+esc(t.subjects.join(", "))+'</td><td>'+esc(t.experience)+'</td><td><span class="role-pill '+(t.verified?'':'pending')+'">'+(t.verified?'Verified':'Pending')+'</span></td><td>'+(t.verified?'—':'<button class="role-btn primary approve" data-id="'+t.id+'">Approve</button>')+'</td></tr>').join("");document.querySelectorAll(".approve").forEach(b=>b.onclick=()=>{a.find(x=>String(x.id)===b.dataset.id).verified=true;save("codemate_admin_tutors",a);render()})}render()})();