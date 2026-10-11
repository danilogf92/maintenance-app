<x-layouts.app>
<section class="ma-auth" aria-labelledby="ma-login-title">
  <div class="ma-panel ma-auth-panel">
    <div class="ma-panel-heading"><p class="ma-kicker">ACCESO A LA PLATAFORMA</p><h1 id="ma-login-title">Iniciar sesión</h1><p>Ingresa con tu cuenta asignada por el administrador.</p></div>
    @if ($errors->any())
      <div class="ma-alert ma-alert-error" role="alert"><strong>Revisa tus datos de acceso.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if (session('status'))<div class="ma-alert ma-alert-success" role="status">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('login') }}" class="ma-form">
      @csrf
      <div class="ma-field"><label for="ma-email">Correo electrónico</label><input id="ma-email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" inputmode="email" required autofocus placeholder="usuario@empresa.com" @error('email') aria-invalid="true" @enderror></div>
      <div class="ma-field"><label for="ma-password">Contraseña</label><input id="ma-password" name="password" type="password" autocomplete="current-password" required placeholder="Ingresa tu contraseña" @error('password') aria-invalid="true" @enderror></div>
      <button type="submit" class="ma-button ma-button-primary">Ingresar <span aria-hidden="true">→</span></button>
    </form>
  </div>
</section>
</x-layouts.app>
