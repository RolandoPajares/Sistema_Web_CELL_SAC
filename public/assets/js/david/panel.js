// Dashboard interno: calendario y periodo de compras.
(() => {
  const date = document.querySelector('[data-dashboard-date]');
  const dateLabel = document.querySelector('[data-dashboard-date-label]');
  if (date) {
    const open = () => { if (typeof date.showPicker === 'function') date.showPicker(); else date.click(); };
    date.closest('.boton-fecha')?.addEventListener('click', (e) => { if (e.target !== date) { e.preventDefault(); open(); } });
    date.addEventListener('change', () => {
      if (!date.value || !dateLabel) return;
      const d = new Date(date.value + 'T12:00:00');
      dateLabel.textContent = d.toLocaleDateString('es-PE', { day:'2-digit', month:'short', year:'numeric' });
    });
  }
  document.querySelector('[data-dashboard-period]')?.addEventListener('change', (e) => {
    const u = new URL(window.location.href); u.searchParams.set('months', e.target.value); window.location.href = u.toString();
  });
})();

// Modulos internos: calendario y filtros funcionales.
(() => {
  document.querySelectorAll('[data-module-date]').forEach(input => {
    const label=input.closest('.boton-fecha'); const text=label?.querySelector('[data-module-date-label]');
    label?.addEventListener('click', e=>{ if(e.target!==input){e.preventDefault(); if(typeof input.showPicker==='function') input.showPicker(); else input.click();} });
    input.addEventListener('change',()=>{if(!input.value||!text)return; const d=new Date(input.value+'T12:00:00'); text.textContent=d.toLocaleDateString('es-PE',{day:'2-digit',month:'short',year:'numeric'});});
  });
  const filters=document.querySelector('[data-module-filters]'); const table=document.querySelector('[data-filter-table]');
  if(!table) return;
  const search=filters?.querySelector('[data-filter-search]'), status=filters?.querySelector('[data-filter-status]'), date=filters?.querySelector('[data-filter-date]'), dateText=filters?.querySelector('[data-filter-date-label]');
  filters?.querySelector('.module-filter-date')?.addEventListener('click',e=>{if(e.target!==date){e.preventDefault(); if(typeof date.showPicker==='function')date.showPicker(); else date.click();}});
  date?.addEventListener('change',()=>{if(date.value&&dateText){const d=new Date(date.value+'T12:00:00');dateText.textContent=d.toLocaleDateString('es-PE',{day:'2-digit',month:'short',year:'numeric'});}});
  const apply=()=>{const q=(search?.value||'').trim().toLowerCase(), st=(status?.value||'').toLowerCase(); let shown=0; table.querySelectorAll('[data-filter-row]').forEach(row=>{const t=row.textContent.toLowerCase(); const ok=(!q||t.includes(q))&&(!st||t.includes(st)); row.hidden=!ok;if(ok)shown++;}); const empty=table.querySelector('[data-filter-empty]');if(empty)empty.hidden=shown!==0;};
  filters?.querySelector('[data-apply-filters]')?.addEventListener('click',apply); search?.addEventListener('input',apply); status?.addEventListener('change',apply);
  table.querySelector('[data-close-detail]')?.addEventListener('click',()=>{const d=table.querySelector('.detalle-mockup');if(d)d.hidden=true;});
  table.querySelector('[data-export-table]')?.addEventListener('click',()=>{const rows=[...table.querySelectorAll('table tr')].filter(r=>!r.hidden).map(r=>[...r.children].map(c=>'"'+c.textContent.trim().replaceAll('"','""')+'"').join(',')); const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([rows.join('\n')],{type:'text/csv;charset=utf-8'}));a.download='reporte.csv';a.click();URL.revokeObjectURL(a.href);});
})();

// Alertas de reposición: muestra el proveedor vinculado al producto crítico.
(() => {
  const root=document.querySelector('.modulo-listado-real');
  const panel=root?.querySelector('[data-provider-panel]');
  if(!root||!panel) return;
  root.querySelectorAll('[data-provider-detail]').forEach(btn=>btn.addEventListener('click',()=>{
    const row=btn.closest('[data-record]'); let data={};
    try{data=JSON.parse(row?.dataset.record||'{}')}catch(_){data={}}
    panel.querySelector('[data-p-product]').textContent=data.producto||'—';
    panel.querySelector('[data-p-name]').textContent=data.proveedor||'Sin proveedor asignado';
    panel.querySelector('[data-p-email]').textContent=data.correo_proveedor||'—';
    panel.querySelector('[data-p-phone]').textContent=data.telefono_proveedor||'—';
    panel.hidden=false; panel.scrollIntoView({behavior:'smooth',block:'nearest'});
  }));
  panel.querySelector('[data-close-provider]')?.addEventListener('click',()=>panel.hidden=true);
})();
