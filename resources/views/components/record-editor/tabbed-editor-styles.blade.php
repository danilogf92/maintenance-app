@once
<style>
.record-editor-header { display:flex; align-items:center; justify-content:flex-end; gap:6px; height:48px; min-height:48px; padding:0 12px; border-bottom:1px solid #e2e8f0; background:#fff; }
.record-editor-tabs { display:flex; flex:none; gap:4px; padding:0 12px; overflow-x:auto; border-bottom:1px solid #e2e8f0; background:#fff; }
.record-editor-tabs button { display:inline-flex; align-items:center; gap:7px; white-space:nowrap; flex:none; padding:12px 10px; min-height:44px; border:0; border-bottom:3px solid transparent; background:transparent; color:#64748b; cursor:pointer; }
.record-editor-tabs button:hover { color:var(--ui-primary); background:var(--ui-wash); }
.record-editor-tabs button.is-active { color:var(--ui-primary); border-bottom-color:var(--ui-primary); font-weight:600; }
.record-editor-tabs button:focus-visible { outline:2px solid var(--ui-focus); outline-offset:-3px; }
.record-editor-tab-error { color:#dc2626; margin-left:5px; font-size:10px; }
.record-editor-content { min-height:0; flex:1; overflow-y:auto; padding:var(--record-editor-padding); container-type:inline-size; container-name:record-editor-content; }
.record-editor-section + .record-editor-section { margin-top:var(--record-editor-section-gap); }
.record-editor-section h3 { margin:0 0 var(--record-editor-gap); color:inherit; font-size:14px; font-weight:600; }
.record-editor-field-grid { display:grid; grid-template-columns:repeat(var(--record-editor-columns),minmax(0,1fr)); gap:var(--record-editor-gap); }
.record-editor-field { display:flex; flex-direction:column; min-width:0; gap:var(--record-editor-label-gap); font-size:14px; }
.record-editor-field-full { grid-column:1/-1; }
.record-editor-field input,.record-editor-field select,.record-editor-field textarea { width:100%; min-width:0; min-height:var(--record-editor-control-height); padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:inherit; }
.record-editor-field input:focus,.record-editor-field select:focus,.record-editor-field textarea:focus { outline:2px solid var(--ui-focus); border-color:var(--ui-primary); }
.record-editor-field small { color:#dc2626; font-size:12px; }
.dark .record-editor-header,.dark .record-editor-tabs { background:#0f172a; color:#e2e8f0; border-color:#334155; }
.dark .ops-detail-panel { background:#020617; color:#f1f5f9; border-color:#334155; color-scheme:dark; }
.dark .record-editor-field input,.dark .record-editor-field select { background:#0f172a; border-color:#475569; }
.dark .record-editor-tabs button { display:inline-flex; align-items:center; gap:7px; color:#cbd5e1; }
.dark .record-editor-tabs button.is-active { color:var(--ui-focus); }
.dark .record-editor-tabs button:hover { background:#1e293b; }
@container record-editor-content (max-width:600px) { .record-editor-field-grid { grid-template-columns:repeat(var(--record-editor-mobile-columns),minmax(0,1fr)); } }
@media(max-width:819px) { .record-editor-field input,.record-editor-field select,.record-editor-field textarea { font-size:16px; } .record-editor-tabs { scrollbar-width:thin; } }
.record-editor-field input[type=checkbox] { width:18px; min-height:18px; height:18px; padding:0; flex:none; }
.record-editor-check { display:flex; align-items:center; gap:10px; min-height:40px; font-size:14px; }
.record-editor-roles { display:grid; gap:6px; padding:12px; border:1px solid #cbd5e1; border-radius:8px; }
.record-editor-roles legend { padding:0 6px; font-size:14px; }
.record-editor-field .record-editor-hint { color:#64748b; font-size:12px; line-height:1.5; }
.dark .record-editor-field .record-editor-hint { color:#94a3b8; }
.dark .record-editor-roles { border-color:#475569; }
.record-related-search { width:100%; margin:12px 0; padding:9px 10px; border:1px solid #cbd5e1; border-radius:8px; background:transparent; color:inherit; }
.record-related-table { width:100%; min-width:700px; border-collapse:collapse; font-size:13px; }
.record-related-table th,.record-related-table td { padding:9px; border:1px solid #e2e8f0; text-align:left; overflow-wrap:anywhere; }
.record-related-table a { color:var(--ui-primary); text-decoration:underline; }
.record-related-table tbody tr:nth-child(even) { background:#f1f5f9; }
.dark .record-related-table tbody tr:nth-child(even) { background:#1e293b; }
.dark .record-related-table th,.dark .record-related-table td,.dark .record-related-search { border-color:#475569; }
.record-tab-icon { width:16px; height:16px; flex:none; }
.dark .record-editor-field textarea { background:#0f172a; border-color:#475569; }
.record-related-table a { display:inline-flex; align-items:center; gap:6px; }
</style>
@endonce
