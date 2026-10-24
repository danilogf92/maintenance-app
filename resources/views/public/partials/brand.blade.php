<a class="portal-brand" href="{{ route('welcome') }}" aria-label="{{ \App\Models\BrandSetting::displayName() }} — Inicio">
 @if($logo = \App\Models\BrandSetting::logoUrl())<img class="portal-logo" src="{{ $logo }}" alt="" width="44" height="44">
 @else<x-ui.project-mark :size="44" class="portal-logo" />@endif
 <span>{{ \App\Models\BrandSetting::displayName() }}<small>EQUIPOS Y SERVICIOS</small></span>
</a>
