<a href="{{ route('dashboard') }}" @click="mobileOpen=false"
 class="nav-item {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}" title="Inicio"
 @if(request()->routeIs('dashboard')) aria-current="page" @endif>
 <span class="nav-icon"><x-ui.icon name="home" class="h-5 w-5" /></span>
 <span class="sidebar-label" x-show="!collapsed || mobileOpen">Inicio</span>
</a>
@foreach(\App\Support\Navigation\OperationsNavigation::groups() as $group)
 <x-layouts.nav-group :id="$group['id']" :label="$group['label']" :icon="$group['icon']" :active="$group['active']">
  @foreach($group['items'] as $item)
   <a href="{{ $item['url'] }}" @click="mobileOpen=false"
    class="nav-item {{ $item['active'] ? 'nav-active' : '' }}" title="{{ $item['label'] }}"
    @if($item['active']) aria-current="page" @endif>
    <span class="nav-icon"><x-ui.icon :name="$item['icon']" class="h-5 w-5" /></span>
    <span class="sidebar-label" x-show="!collapsed || mobileOpen">{{ $item['label'] }}</span>
   </a>
  @endforeach
 </x-layouts.nav-group>
@endforeach
