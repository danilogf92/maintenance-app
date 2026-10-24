<x-layouts.public title="Iniciar sesión" page="login">
 <section class="portal-login portal-container" aria-labelledby="login-title">
  <aside class="portal-login-story" aria-labelledby="access-story-title">
   <p class="portal-eyebrow">GESTIÓN TÉCNICA CONECTADA</p>
   <h2 id="access-story-title">El contexto completo.<br>La siguiente acción.</h2>
   <p>Retoma tu trabajo con la información del equipo y cada intervención en un mismo lugar.</p>
   <ol class="portal-login-context">
    <li><span>01</span><div><strong>Conoce el equipo</strong><p>Identificación, ubicación e historial.</p></div></li>
    <li><span>02</span><div><strong>Organiza la atención</strong><p>Tickets, servicios y responsables.</p></div></li>
    <li><span>03</span><div><strong>Documenta lo realizado</strong><p>Actividades e informes relacionados.</p></div></li>
   </ol>
   <div class="portal-story-note"><x-ui.icon name="shield" /><span>Información disponible según los permisos de tu cuenta.</span></div>
  </aside>
  <div class="portal-login-form">
   <p class="portal-eyebrow">ACCESO A LA PLATAFORMA</p>
   <h1 id="login-title">Bienvenido de nuevo.</h1>
   <p class="portal-login-description">Ingresa con tu cuenta de trabajo para continuar.</p>
   @if($errors->any())<div class="portal-alert" role="alert" id="login-errors"><strong>No pudimos iniciar sesión.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
   @if(session('status'))<div class="portal-alert portal-alert-success" role="status">{{ session('status') }}</div>@endif
   <form method="POST" action="{{ route('login') }}" class="portal-form">@csrf
    <div class="portal-field"><label for="ma-email">Correo electrónico</label><input id="ma-email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" inputmode="email" required autofocus placeholder="usuario@empresa.com" @error('email') aria-invalid="true" aria-describedby="login-errors" @enderror></div>
    <div class="portal-field"><label for="ma-password">Contraseña</label><div class="portal-password"><input id="ma-password" name="password" type="password" autocomplete="current-password" required placeholder="Ingresa tu contraseña" @error('password') aria-invalid="true" aria-describedby="login-errors" @enderror><button type="button" data-password-toggle aria-controls="ma-password" aria-label="Mostrar contraseña" aria-pressed="false" hidden><x-ui.icon name="eye" /></button></div></div>
    <button type="submit" class="portal-button">Iniciar sesión<x-ui.icon name="arrow-right" /></button>
   </form>
   <div class="google-login"><p class="google-divider">O continúa con</p>
    @if(\App\Modules\Access\Configuration\GoogleSettings::ready())
     <a class="google-button" href="{{ route('google.redirect') }}"><x-ui.google-mark />Continuar con Google</a>
     <p class="google-login-hint">La primera vez, ingresa con tu contraseña y vincula Google desde Mi perfil.</p>
    @else
     <button class="google-button" type="button" disabled aria-describedby="google-configuration"><x-ui.google-mark />Continuar con Google</button>
     <p class="google-login-hint" id="google-configuration">Google estará disponible cuando el administrador complete la configuración.</p>
    @endif
   </div>
   <p class="portal-login-help"><x-ui.icon name="info" /><span>¿Necesitas acceso o recuperar tu cuenta? Contacta al administrador de tu institución.</span></p>
   <a class="portal-back" href="{{ route('welcome') }}">← Volver a la presentación</a>
  </div>
 </section>
</x-layouts.public>
