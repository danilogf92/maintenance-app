# Confirmación de eliminación — v11.28

Usuarios, Instituciones y eliminación de vistas usan el mismo componente:
`resources/views/components/ui/deletion-modal.blade.php`. Sus estilos se cargan
desde `public/css/pages/deletion-modal.css`, sin depender de una compilación npm.

El error mostrado en la captura era visual: la confirmación de registros era
un bloque dentro de la página, sin estilos de modal. Ahora usa un `dialog`
nativo abierto con `showModal()`: aparece en la capa superior del navegador,
con fondo oscuro, foco dentro del diálogo y contenido de fondo bloqueado.
Funciona sobre el editor de escritorio, el móvil y la configuración de vistas.

El diálogo incluye título, nombre del registro, mensaje, Cancelar, Eliminar y
cierre rojo. Cancelar, Escape, cierre y clic en el fondo cancelan la operación;
no eliminan el registro. Escape no se propaga al editor que está detrás. Durante
la petición los controles se deshabilitan para evitar solicitudes repetidas.
Un fallo de petición mantiene el diálogo abierto y muestra un mensaje.

Las pantallas siguen usando `ConfirmsRecordDeletion` en el servidor: primero se
solicita la confirmación, luego se elimina el ID bloqueado. No se modifica la
protección de cuentas administrativas ni la restricción de instituciones con
usuarios. La eliminación de vistas sigue sin borrar datos de la tabla.

## Reutilización

- `record-editor/delete-confirmation.blade.php` conecta el diálogo con
  `$wire.confirmDelete`, `cancelDelete` y `deleteConfirmedRecord`.
- `record-editor/confirm-dialog.blade.php` conecta la eliminación de vistas con
  el mismo diálogo, usando `deleteView`.
- Para nuevos módulos, usa el editor compartido y estos componentes; no copies
  su HTML ni agregues eliminación directa al botón de papelera.

El panel administrativo no ofrece acciones de borrado actualmente; esta
actualización no añade borrados a las pantallas que no los permitían.

`node tests/Frontend/deletion-modal.cjs` verifica cancelación, confirmación,
peticiones repetidas, errores y recuperación del foco con el controlador real.
Las pruebas PHP verifican confirmación y protecciones del servidor. Queda
pendiente comprobar visualmente el diálogo en el navegador local.

Desde v11.29, una eliminación correcta cierra el diálogo y retira el registro sin mostrar el aviso verde de éxito. Los errores siguen mostrándose.
