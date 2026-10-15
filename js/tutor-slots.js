async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

(async()=>{await CodeMateSession.getCurrentUser();const base=await loadJSON("data/mock-tutor-slots.json");let a=getStored("codemate_tutor_slots",base);
function render(){rows.innerHTML=a.map(s=>'<tr><td>'+esc(s.course)+'</td><td>'+s.date+'</td><td>'+s.start+' - '+s.end+'</td><td><span class="role-pill '+(s.status==="open"?"":"pending")+'">'+s.status+'</span></td><td><button class="role-btn danger del" data-id="'+s.id+'">Delete</button></td></tr>').join("");document.querySelectorAll(".del").forEach(b=>b.onclick=()=>{a=a.filter(x=>x.id!==b.dataset.id);save("codemate_tutor_slots",a);render()})}

const slotForm = document.getElementById("slot-form");
slotForm.onsubmit = e => {
  e.preventDefault();
  a.push({id:"s"+Date.now(),tutorId:1,course:course.value,date:date.value,start:start.value,end:end.value,status:"open"});
  save("codemate_tutor_slots",a);
  slotForm.reset();
  render();
};
render()})();