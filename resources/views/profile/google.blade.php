<section class="page-card google-profile" aria-labelledby="google-access-title">
 <div class="page-section-heading"><h2 id="google-access-title">Acceso con Google</h2><p class="page-help">Conecta tu cuenta de Google para ingresar sin escribir tu contraseña. Tu acceso actual se conserva.</p></div>
 @if($googleAccount)
  <p class="page-help">Cuenta vinculada: <strong>{{ $googleAccount->email }}</strong></p>
  @if(strcasecmp($googleAccount->email, $user->email) !== 0)<p class="page-help">Tu correo cambió. Desvincula y conecta Google con el correo actual para volver a utilizarlo.</p>@endif
  <form method="POST" action="{{ route('google.disconnect') }}">@csrf @method('DELETE')<button type="submit" class="page-button">Desvincular Google</button></form>
 @elseif(\App\Modules\Access\Configuration\GoogleSettings::ready())
  <p class="page-help">Selecciona la cuenta de Google con el mismo correo de tu perfil: {{ $user->email }}.</p>
  <form method="POST" action="{{ route('google.connect') }}">@csrf<button type="submit" class="google-button"><x-ui.google-mark />Vincular cuenta de Google</button></form>
 @else<p class="page-help">El administrador debe configurar Google antes de que puedas vincular tu cuenta.</p>@endif
</section>
