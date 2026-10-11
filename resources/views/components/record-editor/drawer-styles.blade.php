<style>
.ops-drawer-workspace { position:relative; display:flex!important; width:100%; flex:1 1 0%; min-height:0; min-width:0; overflow:hidden; }
.ops-drawer-workspace .ops-detail-panel { position:relative; display:flex; height:100%; min-height:0; flex-direction:column; overflow:visible!important; }
.ops-drawer-workspace .ops-resize-handle { position:relative; display:flex!important; align-items:center; justify-content:center; width:36px; height:36px; flex:0 0 36px; margin-right:auto; z-index:20; cursor:col-resize; touch-action:none; user-select:none; outline:none; }
.ops-drawer-workspace .ops-resize-grip { display:flex; align-items:center; justify-content:center; width:30px; height:32px; border:1px solid #60a5fa; border-radius:8px; background:#eff6ff; color:#2563eb; box-shadow:0 2px 10px #0002; transition:background .15s, color .15s, box-shadow .15s; }
.ops-drawer-workspace .ops-resize-grip svg { width:16px; height:30px; }
.ops-drawer-workspace .ops-resize-handle:hover .ops-resize-grip,
.ops-drawer-workspace .ops-resize-handle:focus-visible .ops-resize-grip,
.ops-drawer-resizing .ops-resize-grip { background:#2563eb; color:#fff; box-shadow:0 0 0 3px #93c5fd; }
.ops-page .ops-panel-close { display:inline-flex!important; width:36px!important; height:36px!important; min-width:36px; align-items:center; justify-content:center; padding:0!important; border:1px solid #fecaca!important; border-radius:8px!important; background:#fff1f2!important; color:#dc2626!important; cursor:pointer; transition:background .15s, color .15s, box-shadow .15s; }
.ops-page .ops-panel-close svg { width:22px!important; height:22px!important; stroke:currentColor!important; }
.ops-page .ops-panel-close:hover { background:#dc2626!important; border-color:#dc2626!important; color:#fff!important; box-shadow:0 2px 8px #dc262640; }
.ops-page .ops-panel-close:focus-visible { outline:3px solid #fca5a5; outline-offset:2px; }
.ops-record-count { position:sticky; left:0; padding:8px 14px; color:#64748b; font-size:12px; background:#f8fafc; border-top:1px solid #e2e8f0; }
.dark .ops-resize-grip { background:#1e293b; border-color:#3b82f6; color:#93c5fd; }
.dark .ops-page .ops-panel-close { background:#450a0a!important; border-color:#991b1b!important; color:#fca5a5!important; }
.dark .ops-page .ops-panel-close:hover { background:#dc2626!important; color:#fff!important; }
.dark .ops-record-count { background:#0f172a; color:#94a3b8; border-color:#334155; }
@media(max-width:767px) { .ops-drawer-workspace .ops-resize-handle { display:none!important; } }
@media print { .ops-drawer-workspace .ops-detail-panel { display:none!important; } }
</style>
