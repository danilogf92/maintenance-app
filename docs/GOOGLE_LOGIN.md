# Acceso adicional con Google — v11.44.0

El correo y contraseña actuales siguen funcionando. No se crean cuentas ni se asignan roles desde Google. El usuario debe existir y estar activo en Maintenance APP.

## 1. Configurar el cliente de Google

1. En Google Cloud, utiliza un proyecto y configura Google Auth Platform / pantalla de consentimiento con el nombre y datos reales de tu aplicación.
2. Crea un cliente OAuth de tipo **Aplicación web**.
3. Registra exactamente esta URI de redirección para el entorno local:

   `http://localhost:8611/auth/google/callback`

4. Si la aplicación está en modo de prueba y Google lo requiere, añade las cuentas que probarán el acceso como usuarios de prueba. Para producción, configura la audiencia y el estado de publicación correspondientes a tu organización.
5. Guarda el Client ID y el Client Secret en el `.env` privado del proyecto instalado. No los subas al código ni los compartas en capturas.

```dotenv
APP_URL=http://localhost:8611
GOOGLE_LOGIN_ENABLED=true
GOOGLE_CLIENT_ID=tu-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8611/auth/google/callback
```

Abre el sitio con `localhost`, igual que el callback; no alternes con `127.0.0.1` durante el ingreso. En producción utiliza el dominio HTTPS real tanto en APP_URL como en GOOGLE_REDIRECT_URI y regístralo en Google. La coincidencia debe incluir esquema, puerto y ruta.

Después de editar `.env`, desde la carpeta del proyecto ejecuta:

```cmd
docker compose exec app php artisan optimize:clear
```

La actualización conserva el `.env`; por eso debes añadir estas variables allí una sola vez. Sin configuración, el botón de Google aparece deshabilitado y el ingreso actual sigue disponible. No se han creado credenciales de Google ni completado una autorización real en tu proyecto: eso requiere tu cliente OAuth.

## 2. Vincular cada cuenta una sola vez

1. El administrador crea o conserva el usuario con su correo de trabajo.
2. El usuario entra con su correo y contraseña actuales.
3. Abre **Mi perfil y apariencia → Acceso con Google → Vincular cuenta de Google**.
4. Selecciona en Google la cuenta con el mismo correo verificado del perfil.
5. Vuelve al perfil con el aviso de vinculación correcta.
6. En los siguientes ingresos puede usar **Continuar con Google** en `/login`.

La vinculación explícita evita apropiarse de una cuenta únicamente por coincidencia de correo. El identificador estable de Google queda asociado al usuario existente. Su contraseña, institución y permisos no se modifican. Si el administrador cambia el correo local, se debe desvincular y volver a conectar Google con el correo nuevo. Una persona puede desvincular Google desde su perfil y continuar con su contraseña.

## 3. Alcance y seguridad

- Laravel Socialite usa autorización con código, estado de sesión y PKCE S256. El intento vence a los diez minutos y no puede reutilizarse.
- Solo se solicitan identidad, correo y perfil básico. No hay permisos de Gmail, Drive ni envío de correos.
- Solo se guarda el identificador de Google y el correo vinculado; no se guardan access tokens ni refresh tokens.
- Se verifican el correo y la cuenta activa; una cuenta de Google desconocida no registra usuarios automáticamente.
- Los permisos siguen viniendo de Maintenance APP. Tener cuenta de Google no habilita Admin.
- Cancelar, volver a una pestaña antigua, perder la sesión o repetir el callback requiere iniciar un intento nuevo. Los errores no muestran tokens ni secretos.
- Desactivar el usuario bloquea ambos métodos y revoca las sesiones conforme al middleware existente. Desvincular Google impide futuros ingresos por ese vínculo; no cierra por sí solo otras sesiones ya iniciadas.
- La migración nueva añade `google_accounts` con vínculos únicos y borrado en cascada; no cambia registros existentes. El actualizador ejecuta la migración y Composer instala Socialite desde el archivo de bloqueo.
- El acceso Google adicional se ofrece en el login normal. El panel `/admin` conserva su autenticación propia; los usuarios autorizados pueden entrar al panel administrativo desde una sesión normal iniciada con Google.

## Dónde cambiar el código

- `app/Modules/Access/Configuration/GoogleSettings.php`: disponibilidad y URI admitida.
- `app/Modules/Access/Queries/GoogleProviderFactory.php`: proveedor Socialite y tiempos máximos de red.
- `app/Modules/Access/Actions/ResolveGoogleAccount.php`: reglas de vinculación e ingreso.
- `app/Modules/Access/Actions/CompleteLogin.php`: cierre común del ingreso y regeneración de sesión.
- `app/Modules/Access/UI/GoogleController.php`: redirección, callback y desvinculación.
- `routes/modules/access.php`: rutas y límites de intentos.
- `resources/views/auth/login.blade.php`, `resources/views/profile/google.blade.php`: controles visuales.
- `public/css/pages/google-access.css`: estilos compartidos.

Referencias oficiales: https://laravel.com/docs/12.x/socialite y https://developers.google.com/identity/protocols/oauth2/web-server .
