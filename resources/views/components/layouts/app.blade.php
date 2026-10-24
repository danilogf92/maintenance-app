<!doctype html>
<html lang="es" class="h-full" data-theme="{{ auth()->user()?->theme_preference ?? 'system' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
@include('components.layouts.sidebar-state')
<script>
(function () {
  const root = document.documentElement;
  const preference = root.dataset.theme || 'system';
  const dark = preference === 'dark' || (preference === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
  root.classList.toggle('dark', dark);
  root.style.colorScheme = dark ? 'dark' : 'light';
  if (preference === 'system') {
    matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
      root.classList.toggle('dark', event.matches);
      root.style.colorScheme = event.matches ? 'dark' : 'light';
    });
  }
})();
</script>
<meta name="theme-color" content="#BE4128"><title>{{ \App\Models\BrandSetting::displayName() }}</title>
@vite(['resources/css/app.css','resources/js/app.js'])
@foreach(['shared','auth','dashboard','profile','google-access','table-navigation','mobile-workspace','deletion-modal'] as $stylesheet)
<link rel="stylesheet" href="{{ asset('css/pages/'.$stylesheet.'.css') }}?v=11.61.0">
@endforeach
<link rel="stylesheet" href="{{ asset('css/pages/sidebar-feedback.css') }}?v=11.61.0">
<link rel="stylesheet" href="{{ asset('css/pages/contacts.css') }}?v=11.61.0">
<link rel="stylesheet" href="{{ asset('css/pages/planning.css') }}?v=11.61.0">
<link rel="stylesheet" href="{{ asset('css/pages/planning-calendar.css') }}?v=11.61.0">
<link rel="stylesheet" href="{{ asset('css/pages/operations-identity.css') }}?v=11.61.0">
<link rel="stylesheet" href="{{ asset('css/pages/contracts.css') }}?v=11.61.0">
@livewireStyles
 <x-layouts.favicon />
</head>
@php($palette = \App\Models\UiColorSetting::currentPalette())
<style>:root{--ui-primary:{{ $palette['primary'] }};--ui-success:{{ $palette['success'] }};--ui-danger:{{ $palette['danger'] }};--ui-secondary:{{ $palette['secondary'] }};}</style>
<body class="operations-app min-h-full bg-slate-50 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
<a class="sr-only focus:not-sr-only focus:absolute focus:z-[80] focus:bg-white focus:p-3" href="#contenido">Saltar al contenido</a>
@auth
<div x-data="{mobileOpen:false,profileOpen:false,collapsed:document.documentElement.dataset.sidebarCollapsed==='1',saveCollapse(){const value=this.collapsed?'1':'0';document.documentElement.dataset.sidebarCollapsed=value;try{localStorage.setItem('maintenance-sidebar-collapsed',value)}catch(error){}}}" @keydown.escape.window="mobileOpen=false; profileOpen=false" class="h-dvh overflow-hidden">
 <div x-show="mobileOpen" x-cloak @click="mobileOpen=false" class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"></div>
 <aside id="app-sidebar" :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-[244px] flex-col overflow-hidden border-r border-slate-200 bg-white shadow-xl lg:translate-x-0 lg:shadow-none dark:border-slate-800 dark:bg-slate-900" aria-label="Navegación operativa">
  <x-layouts.sidebar-brand />
  <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-2 py-4" aria-label="Módulos">
   <span x-show="!collapsed || mobileOpen" class="sidebar-label block px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-slate-400">Operaciones</span>
   @include('components.layouts.operations-navigation')
  </nav>
 </aside>
 <div class="app-shell-content flex h-dvh min-w-0 flex-col overflow-hidden">
  <header class="relative z-30 flex h-12 shrink-0 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 dark:border-slate-800 dark:bg-slate-900/95">
   <div class="flex min-w-0 items-center gap-3"><button @click="mobileOpen=true" type="button" class="rounded-lg p-2 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800" aria-label="Abrir menú" :aria-expanded="mobileOpen" aria-controls="app-sidebar"><x-ui.icon name="menu" /></button><div class="flex min-w-0 items-center gap-2">
     <x-ui.project-mark :size="30" />
     <span class="truncate text-sm font-semibold sm:text-base">{{ \App\Models\BrandSetting::displayName() }}</span>
    </div></div>
   <div class="relative" @click.outside="profileOpen=false">
    <button type="button" @click="profileOpen=!profileOpen" :aria-expanded="profileOpen" aria-haspopup="true" class="flex items-center gap-2 rounded-full p-1 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-slate-800" aria-label="Menú de usuario"><span class="grid h-9 w-9 place-items-center rounded-full bg-blue-700 text-sm font-bold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span><span class="hidden text-sm font-medium sm:inline">{{ auth()->user()->name }}</span><x-ui.icon name="chevron-down" class="h-3.5 w-3.5 text-slate-500" /></button>
    <div x-show="profileOpen" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900" role="menu">
     <div class="border-b border-slate-100 px-3 py-2 dark:border-slate-800"><p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p></div>
     <a class="profile-item" href="{{ route('dashboard') }}" role="menuitem"><x-ui.icon name="home" class="inline-block mr-2" />Inicio</a>
     <a class="profile-item" href="{{ route('profile.edit') }}" role="menuitem"><x-ui.icon name="user" class="inline-block mr-2" />Mi perfil y apariencia</a>
     @if(auth()->user()->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))<a class="profile-item" href="{{ url('/admin') }}" role="menuitem"><x-ui.icon name="settings" class="inline-block mr-2" />Panel administrativo ↗</a>@endif
     <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="profile-item w-full text-left text-red-600" role="menuitem"><x-ui.icon name="log-out" class="inline-block mr-2" />Cerrar sesión</button></form>
    </div>
   </div>
  </header>
  <main id="contenido" class="ops-main min-h-0 min-w-0 flex-1 overflow-hidden p-0">{{ $slot }}</main>
 </div>
</div>
@else
<header class="guest-header"><a href="{{ route('login') }}">{{ \App\Models\BrandSetting::displayName() }}</a></header>
<main id="contenido" class="guest-main">{{ $slot }}</main>
@endauth
@livewireScripts
</body>
</html>
