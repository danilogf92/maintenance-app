# Visibilidad y cierre del editor — v11.31

Usuarios e Instituciones comparten `panelStyle()` del workspace. El enlace de estilos
usa un objeto con propiedades de tamaño y posición; nunca sustituye el atributo
`style` completo ni modifica `display`, que pertenece a `x-show.important`.
Cerrar o abrir el menú lateral y cambiar entre escritorio y móvil no abre el editor.
Las propiedades móviles se restablecen al regresar a escritorio.

La X y Escape cierran un borrador nuevo vacío o incompleto. Si los campos ya son
válidos, se guarda antes de cerrar; si falta información obligatoria, se descarta
el borrador sin insertar un registro. El token queda invalidado al cerrar, por lo
que un autosave pendiente no puede crear un registro después.

Para registros existentes se mantiene la validación antes de cerrar: un cambio
inválido queda disponible para corregirlo. Los permisos y el guardado automático
se mantienen en los dos módulos.

Verificación: pruebas de ancho, cambio móvil/escritorio y conservación del estado
oculto; pruebas Livewire de cierre vacío, cierre con errores, tokens tardíos y
guardado válido al cerrar. No se realizó una prueba visual en navegador.
