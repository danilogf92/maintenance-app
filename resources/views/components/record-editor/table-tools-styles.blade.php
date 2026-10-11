@once
<style>
.ops-column-trigger { display:flex; align-items:center; gap:7px; width:100%; padding:0; border:0; background:transparent; color:inherit; font:inherit; text-transform:inherit; cursor:pointer; text-align:left; }
.ops-column-trigger:hover { color:#2563eb; }
.ops-column-trigger:focus-visible { outline:2px solid #60a5fa; outline-offset:3px; }
.ops-column-chevron { margin-left:auto; font-size:17px; }
.ops-column-mark { color:#2563eb; font-size:9px; }
.ops-column-menu { position:fixed; z-index:70; width:min(280px,calc(100vw - 16px)); max-height:calc(100dvh - 24px); overflow:auto; padding:14px; border:1px solid #dbe3ed; border-radius:12px; background:#fff; color:#334155; box-shadow:0 12px 32px #0f172a26; font-size:14px; }
.ops-column-menu header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.ops-column-menu header button { width:30px; height:30px; border:0; border-radius:6px; background:transparent; color:#dc2626; font-size:24px; cursor:pointer; }
.ops-column-menu header button:hover { background:#fff1f2; }
.ops-column-filter { display:flex; flex-direction:column; gap:7px; font-size:12px; }
.ops-column-filter input,.ops-column-filter select { width:100%; padding:9px; border:1px solid #cbd5e1; border-radius:7px; background:#f8fafc; color:inherit; font-size:14px; }
.ops-column-actions { display:flex; flex-direction:column; gap:5px; margin-top:12px; }
.ops-column-actions button,.ops-column-clear { border:0; border-radius:7px; padding:9px; background:#f8fafc; color:inherit; text-align:left; cursor:pointer; }
.ops-column-actions button:hover,.ops-column-actions button[aria-pressed="true"] { background:#dbeafe; color:#1d4ed8; }
.ops-column-clear { margin-top:6px; color:#2563eb; }
.ops-table tbody tr.ops-group-heading > td { background:#e2e8f0!important; color:#334155; font-weight:600; }
.ops-group-heading span { margin-left:7px; font-weight:400; }
.ops-table-tools-status { display:flex; flex-wrap:wrap; align-items:center; gap:6px; flex-shrink:0; padding:6px 12px; border-bottom:1px solid #dbe3ed; background:#f8fafc; font-size:12px; }
.ops-table-tools-status button,.ops-table-tools-status span { border:0; border-radius:6px; background:#e2e8f0; color:#334155; padding:5px 8px; }
.ops-table-tools-status button { cursor:pointer; }
.ops-table-tools-status button:hover { background:#dbeafe; color:#1d4ed8; }
.ops-table-tools-status .ops-table-tools-reset { margin-left:auto; color:#2563eb; background:transparent; }
.dark .ops-column-menu { background:#0f172a; color:#e2e8f0; border-color:#334155; }
.dark .ops-column-filter input,.dark .ops-column-filter select,.dark .ops-column-actions button,.dark .ops-column-clear { background:#1e293b; border-color:#475569; }
.dark .ops-column-actions button:hover,.dark .ops-column-actions button[aria-pressed="true"] { background:#1e3a5f; color:#bfdbfe; }
.dark .ops-table tbody tr.ops-group-heading > td { background:#273449!important; color:#e2e8f0; }
.dark .ops-table-tools-status { background:#0f172a; border-color:#334155; }
.dark .ops-table-tools-status button,.dark .ops-table-tools-status span { background:#1e293b; color:#cbd5e1; }
</style>
@endonce
