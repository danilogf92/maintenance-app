{{-- Cabecera lateral: sin nombre duplicado; el control permanece visible en escritorio. --}}
<div class="flex h-12 shrink-0 items-center justify-between gap-2 border-b border-slate-200 px-2 dark:border-slate-700">
    <a x-show="!collapsed || mobileOpen" href="{{ route('dashboard') }}"
       class="sidebar-brand-link flex h-9 w-10 shrink-0 items-center justify-center rounded-lg transition hover:bg-slate-100 dark:hover:bg-slate-800"
       title="Ir al inicio" aria-label="Volver al inicio">
        <x-ui.project-mark :size="40" />
    </a>

    {{-- El control es de escritorio. Su visibilidad no depende del CSS compilado. --}}
    @once
        @include('components.layouts.navigation-controls-styles')
    @endonce
    <button type="button" @click="collapsed = !collapsed; saveCollapse()"
            :class="collapsed ? 'mx-auto' : 'ml-auto'"
            class="desktop-collapse-control"
            :title="collapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral'"
            :aria-label="collapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral'"
            :aria-expanded="!collapsed" aria-controls="app-sidebar">
        <svg viewBox="0 0 28 28" fill="none" stroke="currentColor"
             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="4" width="22" height="20" rx="4"/>
            <path d="M4 8a3 3 0 0 1 3-3h3v18H7a3 3 0 0 1-3-3Z" fill="currentColor" opacity=".16" stroke="none"/>
            <path d="M10 5v18"/>
            <path class="sidebar-expand-chevron" d="m15 10 4 4-4 4"/>
            <path class="sidebar-collapse-chevron" d="m20 10-4 4 4 4"/>
        </svg>
    </button>
    <button type="button" @click="mobileOpen = false"
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-600 hover:text-white lg:hidden"
            aria-label="Cerrar menú móvil">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
            <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
        </svg>
    </button>
</div>
