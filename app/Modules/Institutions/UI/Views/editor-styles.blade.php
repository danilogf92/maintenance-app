<x-record-editor.tabbed-editor-styles />
@once
<style>
.institution-editor-relations { min-height:0; flex:1; overflow:auto; padding:var(--record-editor-padding); }
.institution-related-search { width:100%; margin:12px 0; padding:9px 10px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:inherit; }
.institution-related-table { width:100%; border-collapse:collapse; font-size:13px; }
.institution-related-table th,.institution-related-table td { padding:9px; border:1px solid #e2e8f0; text-align:left; overflow-wrap:anywhere; }
.institution-related-table button { color:#2563eb; cursor:pointer; text-align:left; }
.institution-related-table tbody tr:nth-child(even) { background:#f1f5f9; }
.institution-user-dialog { padding:0; border:1px solid #cbd5e1; border-radius:12px; width:min(620px,calc(100vw - 24px)); max-height:calc(100dvh - 24px); background:#fff; color:#334155; }
.institution-user-dialog::backdrop { background:#0f172a66; }
.institution-user-dialog .record-editor-content { max-height:calc(100dvh - 160px); }
.dark .institution-user-dialog { background:#0f172a; color:#e2e8f0; border-color:#334155; }
.dark .institution-related-search { background:#0f172a; border-color:#475569; }
.dark .institution-related-table tbody tr:nth-child(even) { background:#1e293b; }
.dark .institution-related-table th,.dark .institution-related-table td { border-color:#334155; }
</style>
@endonce
