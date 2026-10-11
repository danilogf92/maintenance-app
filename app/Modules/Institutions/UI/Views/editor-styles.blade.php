@once
<style>
.institution-editor-header { display:flex; align-items:center; justify-content:flex-end; gap:6px; height:48px; min-height:48px; padding:0 12px; border-bottom:1px solid #e2e8f0; background:#fff; }
.institution-editor-tabs { display:flex; flex:none; gap:4px; padding:0 12px; overflow-x:auto; border-bottom:1px solid #e2e8f0; background:#fff; }
.institution-editor-tabs button { white-space:nowrap; flex:none; padding:12px 10px; min-height:44px; border:0; border-bottom:3px solid transparent; background:transparent; color:#64748b; cursor:pointer; }
.institution-editor-tabs button:hover { color:#2563eb; background:#eff6ff; }
.institution-editor-tabs button.is-active { color:#1d4ed8; border-bottom-color:#2563eb; font-weight:600; }
.institution-editor-tabs button:focus-visible { outline:2px solid #60a5fa; outline-offset:-3px; }
.institution-tab-error { color:#dc2626; margin-left:5px; font-size:10px; }
.institution-editor-content { min-height:0; flex:1; overflow-y:auto; padding:var(--institution-padding); container-type:inline-size; container-name:institution-content; }
.institution-section + .institution-section { margin-top:var(--institution-section-gap); }
.institution-section h3 { margin:0 0 var(--institution-gap); color:inherit; font-size:14px; font-weight:600; }
.institution-field-grid { display:grid; grid-template-columns:repeat(var(--institution-columns),minmax(0,1fr)); gap:var(--institution-gap); }
.institution-field { display:flex; flex-direction:column; min-width:0; gap:var(--institution-label-gap); font-size:14px; }
.institution-field-full { grid-column:1/-1; }
.institution-field input,.institution-field select { width:100%; min-width:0; min-height:var(--institution-control-height); padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:inherit; }
.institution-field input:focus,.institution-field select:focus { outline:2px solid #93c5fd; border-color:#2563eb; }
.institution-field small { color:#dc2626; font-size:12px; }
.institution-editor-relations { min-height:0; flex:1; overflow:auto; padding:var(--institution-padding); }
.institution-related-search { width:100%; margin:12px 0; padding:9px 10px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:inherit; }
.institution-related-table { width:100%; border-collapse:collapse; font-size:13px; }
.institution-related-table th,.institution-related-table td { padding:9px; border:1px solid #e2e8f0; text-align:left; overflow-wrap:anywhere; }
.institution-related-table button { color:#2563eb; cursor:pointer; text-align:left; }
.institution-related-table tbody tr:nth-child(even) { background:#f1f5f9; }
.institution-user-dialog { padding:0; border:1px solid #cbd5e1; border-radius:12px; width:min(620px,calc(100vw - 24px)); max-height:calc(100dvh - 24px); background:#fff; color:#334155; }
.institution-user-dialog::backdrop { background:#0f172a66; }
.institution-user-dialog .institution-user-fields { overflow-y:auto; max-height:calc(100dvh - 100px); padding:20px; }
.dark .institution-editor-header,.dark .institution-editor-tabs,.dark .institution-user-dialog { background:#0f172a; color:#e2e8f0; border-color:#334155; }
.dark .institution-field input,.dark .institution-field select,.dark .institution-related-search { background:#0f172a; border-color:#475569; }
.dark .institution-editor-tabs button { color:#cbd5e1; }
.dark .institution-editor-tabs button.is-active { color:#93c5fd; }
.dark .institution-editor-tabs button:hover,.dark .institution-related-table tbody tr:nth-child(even) { background:#1e293b; }
.dark .institution-related-table th,.dark .institution-related-table td { border-color:#334155; }
@container institution-content(max-width:600px) { .institution-field-grid { grid-template-columns:repeat(var(--institution-mobile-columns),minmax(0,1fr)); } }
@media(max-width:819px) { .institution-field input,.institution-field select { font-size:16px; } .institution-editor-tabs { scrollbar-width:thin; } }
</style>
@endonce
