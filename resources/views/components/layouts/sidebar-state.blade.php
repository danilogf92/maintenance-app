{{-- Restore layout before the body is painted, without waiting for Alpine. --}}
<script>
(function () {
    const root = document.documentElement;
    try {
        root.dataset.sidebarCollapsed = localStorage.getItem('maintenance-sidebar-collapsed') === '1' ? '1' : '0';
    } catch (error) {
        root.dataset.sidebarCollapsed = '0';
    }
})();
</script>
<style>
@media(min-width:1024px) {
    :root { --sidebar-width:244px; }
    :root[data-sidebar-collapsed="1"] { --sidebar-width:68px; }
    #app-sidebar { width:var(--sidebar-width)!important; transition:none!important; }
    .app-shell-content { padding-left:var(--sidebar-width)!important; transition:none!important; }
    [data-sidebar-collapsed="1"] #app-sidebar .sidebar-label,
    [data-sidebar-collapsed="1"] #app-sidebar .sidebar-brand-link { display:none!important; }
    [data-sidebar-collapsed="1"] #app-sidebar .desktop-collapse-control { margin-left:auto; margin-right:auto; }
    /* Compact links need their own padding: the expanded padding clips icons. */
    [data-sidebar-collapsed="1"] #app-sidebar nav { padding-left:6px; padding-right:6px; scrollbar-gutter:auto!important; }
    [data-sidebar-collapsed="1"] #app-sidebar .nav-item {
        box-sizing:border-box; width:100%; min-width:0; min-height:44px;
        justify-content:center; gap:0; padding:10px 0;
    }
    [data-sidebar-collapsed="1"] #app-sidebar .nav-icon {
        flex:0 0 24px; width:24px; height:24px; align-items:center;
    }
    [data-sidebar-collapsed="1"] #app-sidebar .nav-icon svg { flex:none; width:20px; height:20px; }
    [data-sidebar-collapsed="1"] #app-sidebar a.nav-item:focus-visible { outline:2px solid #60a5fa; outline-offset:-2px; }
}
.sidebar-expand-chevron { display:none; }
[data-sidebar-collapsed="1"] .sidebar-expand-chevron { display:block; }
[data-sidebar-collapsed="1"] .sidebar-collapse-chevron { display:none; }
</style>
