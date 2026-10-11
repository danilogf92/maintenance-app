# Corrección de apertura — v11.36

Se revisó maintenance-app(3).zip: el código del módulo coincidía con v11.35.
Faltaba el registro Livewire de `App\Modules\Institutions\UI\InstitutionPage`.
La tabla cargaba por su clase en la ruta, pero la petición posterior no podía
resolver el nombre enviado en el snapshot y devolvía el aviso de página vencida.

La corrección está en `app/Modules/Institutions/InstitutionModule.php`:

```php
Livewire::component('institutions-page', UI\InstitutionPage::class);
```

Mantén un alias explícito para cada componente Livewire ubicado fuera de
`App\Livewire`. No basta con usar su clase directamente en una ruta.

## Instalación y comprobación

1. Ejecuta Actualizar.cmd con la ruta habitual de maintenance-app.
2. Recarga la página con Ctrl+F5. Los snapshots de la pestaña anterior ya no sirven.
3. Abre una institución y cambia entre General, Contacto y Usuarios.
4. Edita un campo, espera el visto y comprueba el dato al volver a abrir.

No hay migraciones nuevas, cambios de sesión ni cambios en los campos configurados.
Si continúa el aviso tras recargar, copia el error reciente de
`storage/logs/laravel.log`, omitiendo datos privados; una sesión realmente vencida
también puede producir 419 y requiere iniciar sesión de nuevo.

## Pruebas

`InstitutionHttpTest` pertenece a Institutions; un puente en tests/Feature permite
su descubrimiento por PHPUnit. Fuerza el middleware CSRF durante las peticiones
HTTP, ya que Laravel lo omite normalmente en testing. Comprueba alias, apertura,
autoguardado y rechazo de un token inválido. No modifica vendor ni desactiva CSRF.
