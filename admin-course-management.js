
async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

(async()=>{await CodeMateSession.getCurrentUser();const base=await loadJSON("mock-courses.json");let a=getStored("codemate_courses",base);function render(){rows.innerHTML=a.map(c=>'<tr><td>'+c.code+'</td><td>'+esc(c.name)+'</td><td><span class="role-pill">'+c.status+'</span></td></tr>').join("")}course-form.onsubmit=e=>{e.preventDefault();a.push({id:"c"+Date.now(),code:code.value.toUpperCase(),name:name.value,status:"active"});save("codemate_courses",a);course-form.reset();render()};render()})();