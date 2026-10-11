<style>
/* Shared controls for every Operations navigation screen. */
#app-sidebar .desktop-collapse-control {
    display:inline-flex; flex:0 0 44px; width:44px; height:40px;
    align-items:center; justify-content:center; padding:0;
    border:1px solid #93c5fd; border-radius:10px;
    background:#eff6ff; color:#1d4ed8; cursor:pointer;
    box-shadow:0 2px 5px #1e40af12;
    transition:background .15s, color .15s, border-color .15s, box-shadow .15s;
}
#app-sidebar .desktop-collapse-control svg { width:28px; height:28px; flex:none; }
#app-sidebar .desktop-collapse-control:hover {
    background:#2563eb; border-color:#2563eb; color:#fff;
    box-shadow:0 3px 9px #2563eb33;
}
#app-sidebar .desktop-collapse-control:focus-visible { outline:3px solid #93c5fd; outline-offset:2px; }
.dark #app-sidebar .desktop-collapse-control { background:#1e293b; border-color:#3b82f6; color:#bfdbfe; }
.dark #app-sidebar .desktop-collapse-control:hover { background:#2563eb; color:#fff; }
@media(max-width:1023px) { #app-sidebar .desktop-collapse-control { display:none!important; } }

/* Override the former thin scrollbar without changing any scroll behavior. */
#contenido .ops-table-wrap,
#contenido .ops-fields,
#app-sidebar nav {
    scrollbar-width:auto!important;
    scrollbar-gutter:stable;
}
#contenido .ops-table-wrap::-webkit-scrollbar,
#contenido .ops-fields::-webkit-scrollbar,
#app-sidebar nav::-webkit-scrollbar { width:16px; height:16px; }
#contenido .ops-table-wrap::-webkit-scrollbar-track,
#contenido .ops-fields::-webkit-scrollbar-track,
#app-sidebar nav::-webkit-scrollbar-track { background:#e2e8f0; border-radius:8px; }
#contenido .ops-table-wrap::-webkit-scrollbar-thumb,
#contenido .ops-fields::-webkit-scrollbar-thumb,
#app-sidebar nav::-webkit-scrollbar-thumb {
    background:#64748b; border:3px solid #e2e8f0; border-radius:8px; min-height:44px;
}
#contenido .ops-table-wrap::-webkit-scrollbar-thumb:hover,
#contenido .ops-fields::-webkit-scrollbar-thumb:hover,
#app-sidebar nav::-webkit-scrollbar-thumb:hover { background:#2563eb; }
#contenido .ops-table-wrap::-webkit-scrollbar-corner { background:#e2e8f0; }
.dark #contenido .ops-table-wrap::-webkit-scrollbar-track,
.dark #contenido .ops-fields::-webkit-scrollbar-track,
.dark #app-sidebar nav::-webkit-scrollbar-track { background:#1e293b; }
.dark #contenido .ops-table-wrap::-webkit-scrollbar-thumb,
.dark #contenido .ops-fields::-webkit-scrollbar-thumb,
.dark #app-sidebar nav::-webkit-scrollbar-thumb { background:#94a3b8; border-color:#1e293b; }
.dark #contenido .ops-table-wrap::-webkit-scrollbar-thumb:hover,
.dark #contenido .ops-fields::-webkit-scrollbar-thumb:hover,
.dark #app-sidebar nav::-webkit-scrollbar-thumb:hover { background:#60a5fa; }
@supports not selector(::-webkit-scrollbar) {
    #contenido .ops-table-wrap, #contenido .ops-fields, #app-sidebar nav { scrollbar-color:#64748b #e2e8f0; }
    .dark #contenido .ops-table-wrap, .dark #contenido .ops-fields, .dark #app-sidebar nav { scrollbar-color:#94a3b8 #1e293b; }
}
</style>
