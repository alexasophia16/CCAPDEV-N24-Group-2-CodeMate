
async function loadJSON(path){const r=await fetch(path);if(!r.ok)throw new Error(path);return r.json()}
function getStored(k,f){try{return JSON.parse(localStorage.getItem(k))||f}catch(e){return f}}
function save(k,v){localStorage.setItem(k,JSON.stringify(v))}
function esc(s){return String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}

(async()=>{const u=await CodeMateSession.getCurrentUser(),ts=await loadJSON("mock-tutors.json"),t=ts.find(x=>x.userId===u.id)||ts[0],saved=getStored("codemate_tutor_profile",t);
bio.value=saved.bio||"";experience.value=saved.experience||"";subjects.value=(saved.subjects||[]).join(", ");
credibility.innerHTML='<p><span class="role-pill '+(t.verified?'':'pending')+'">'+(t.verified?'Verified':'Pending verification')+'</span></p><p style="margin-top:12px"><strong>'+t.credibility.toFixed(1)+'/5</strong> credibility · '+t.sessions+' completed sessions</p>';
document.getElementById("profile-form").onsubmit = e => {
  e.preventDefault();
  save("codemate_tutor_profile", {
    ...saved,
    bio: bio.value,
    experience: experience.value,
    subjects: subjects.value.split(",").map(x => x.trim()).filter(Boolean)
  });
  alert("Profile saved.");
};
})();