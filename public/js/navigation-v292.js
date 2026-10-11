/* Maintenance APP v2.9.2: enhancement without brittle HTML string matching. */
(function(){
 const esc=(s)=>String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const menu=`<div class="nav291-top"><button type="button" class="nav291-collapse" id="nav292-collapse" aria-label="Colapsar menu">◧ <span class="nav291-label">Colapsar</span></button></div>
 <a class="nav291-link" href="/dashboard"><span class="nav291-icon">⌂</span><span class="nav291-label">Inicio</span></a>
 <hr class="nav291-divider"><details class="nav291-group" open><summary><span class="nav291-heading">ORGANIZACIÓN</span><span class="nav291-chevron">⌄</span></summary><div class="nav291-grouplinks">
 <a class="nav291-link" href="/operations/institutions"><span class="nav291-icon">▥</span><span class="nav291-label">Instituciones</span></a>
 <span class="nav291-link nav291-disabled"><span class="nav291-icon">♙</span><span class="nav291-label">Clientes</span></span>
 <a class="nav291-link" href="/operations/users"><span class="nav291-icon">♧</span><span class="nav291-label">Usuarios</span></a>
 </div></details><hr class="nav291-divider"><details class="nav291-group" open><summary><span class="nav291-heading">OPERACIONES</span><span class="nav291-chevron">⌄</span></summary><div class="nav291-grouplinks">
 ${[['▣','Equipos'],['▤','Tickets'],['▦','Bitácoras'],['⚒','Mantenimientos'],['▥','Reportes']].map(([i,n])=>`<span class="nav291-link nav291-disabled"><span class="nav291-icon">${i}</span><span class="nav291-label">${n}</span></span>`).join('')}
 </div></details>`;
 function init(){
  const sidebar=document.querySelector('.app-side-nav')||document.querySelector('.app-sidebar');
  if(!sidebar||sidebar.dataset.nav292==='1')return;
  sidebar.dataset.nav292='1';
  let nav=sidebar.querySelector('nav');
  if(!nav){nav=document.createElement('nav');sidebar.prepend(nav)}
  nav.setAttribute('aria-label','Navegación operativa');
  nav.innerHTML=menu;
  const canInstitutions=sidebar.dataset.canInstitutions==='1';
  const canUsers=sidebar.dataset.canUsers==='1';
  if(!canInstitutions)nav.querySelector('a[href="/operations/institutions"]')?.remove();
  if(!canUsers)nav.querySelector('a[href="/operations/users"]')?.remove();
  // Hide obsolete sidebar navigation only; do not touch profile/logout footer.
  sidebar.querySelectorAll('nav').forEach(n=>{if(n!==nav)n.hidden=true});
  nav.querySelectorAll('a').forEach(a=>{if(location.pathname===new URL(a.href).pathname)a.setAttribute('aria-current','page')});
  nav.querySelector('#nav292-collapse').addEventListener('click',()=>{
   document.body.classList.toggle('nav291-collapsed');
   try{localStorage.setItem('maintenance-sidebar-collapsed',document.body.classList.contains('nav291-collapsed')?'1':'0')}catch(e){}
  });
  try{if(localStorage.getItem('maintenance-sidebar-collapsed')==='1')document.body.classList.add('nav291-collapsed')}catch(e){}
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
 document.addEventListener('livewire:navigated',init);
})();
