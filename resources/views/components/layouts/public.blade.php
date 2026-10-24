@props(['title' => null, 'page' => 'home'])
<!doctype html>
<html lang="es">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
 <meta name="theme-color" content="#FAF9F6">
 <meta name="description" content="{{ config('portal.description') }}">
 <title>{{ $title ? $title.' · ' : '' }}{{ \App\Models\BrandSetting::displayName() }}</title>
 <link rel="stylesheet" href="{{ asset('css/pages/portal.css') }}?v=11.60.0">
 <link rel="stylesheet" href="{{ asset('css/pages/google-access.css') }}?v=11.44.0">
 @if($page === 'login')<script src="{{ asset('js/public-access.js') }}?v=11.43.0" defer></script>@endif
 @php($identity = \App\Models\UiColorSetting::currentPalette())
 <style>:root{--portal-accent:{{ $identity['primary'] }};--portal-accent-hover:{{ $identity['primary'] }};}</style>
 <x-layouts.favicon />
</head>
<body class="portal portal-page-{{ $page }}">
 <a class="portal-skip" href="#contenido">Saltar al contenido</a>
 <header class="portal-header"><div class="portal-container portal-header-inner">
  @include('public.partials.brand')
  <nav aria-label="Navegación principal">
   @if($page === 'home')<a class="portal-nav-link" href="#capacidades">Funcionalidades</a><a class="portal-nav-link" href="#flujo">Flujo de trabajo</a>
   @else<a class="portal-nav-link" href="{{ route('welcome') }}">Volver al inicio</a>@endif
   @if($page !== 'login')<a class="portal-button portal-button-small" href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ auth()->check() ? 'Ir al panel' : 'Ingresar' }}<x-ui.icon name="arrow-right" /></a>@endif
  </nav>
 </div></header>
 <main id="contenido" tabindex="-1">{{ $slot }}</main>
 <footer class="portal-footer portal-container"><span>{{ \App\Models\BrandSetting::displayName() }} <span aria-hidden="true">·</span> Gestión técnica conectada</span><span>Acceso para usuarios autorizados</span></footer>
</body>
</html>
