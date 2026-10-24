<x-layouts.app>
<section class="page-container">
 <div class="page-content page-profile">
  <header class="page-heading"><div><p class="page-label">Mi cuenta</p><h1 class="page-title">Perfil y apariencia</h1><p class="page-help">Actualiza tus datos y cómo prefieres visualizar la plataforma.</p></div></header>
  @if(session('status'))<div role="status" class="page-notice">{{ session('status') }}</div>@endif
  @if($errors->any())<div role="alert" class="page-notice page-notice-error"><strong>No se pudieron guardar los cambios.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ route('profile.update') }}" class="page-card page-profile-form">
   @csrf @method('PUT')
   <div class="page-section-heading"><h2>Datos personales</h2><p class="page-help">El correo electrónico lo administra tu organización.</p></div>
   <div class="page-fields">
    <label for="profile-name">Nombre<input class="page-input" id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required @error('name') aria-invalid="true" @enderror></label>
    <label for="profile-email">Correo electrónico<input class="page-input" id="profile-email" type="email" value="{{ $user->email }}" disabled><small class="page-help">Solo lectura</small></label>
   </div>
   <fieldset class="page-theme"><legend>Apariencia</legend><p class="page-help">Elige el tema que deseas utilizar.</p>
    <div class="page-themes">
     @foreach(['system' => ['Automático','Según tu dispositivo'], 'light' => ['Claro','Fondo claro'], 'dark' => ['Oscuro','Fondo oscuro']] as $value => $option)
      <label class="page-theme-option"><input type="radio" name="theme_preference" value="{{ $value }}" @checked(old('theme_preference', $user->theme_preference ?? 'system') === $value)><span><strong>{{ $option[0] }}</strong><small class="page-help">{{ $option[1] }}</small></span></label>
     @endforeach
    </div>
   </fieldset>
   <div class="page-form-actions"><button class="page-button" type="submit">Guardar preferencias <x-ui.icon name="check" /></button></div>
  </form>
  @include('profile.google')
 </div>
</section>
</x-layouts.app>
