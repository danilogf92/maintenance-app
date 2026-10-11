# Ancho del editor — corrección v11.24

Usuarios e Instituciones comparten `components/record-editor/drawer-workspace.blade.php`.
Arrastra el control de seis puntos en la cabecera hacia izquierda o derecha.
El panel cambia de ancho y permanece donde lo sueltas. Doble clic restaura 520 px;
las flechas del teclado cambian 32 px cuando el control tiene el foco.

## Por qué se regresaba

Alpine resuelve `$el` según el elemento que ejecuta cada expresión. Dentro del
control o del panel, el código anterior tomaba su ancho como si fuera el espacio
total y calculaba límites incorrectos. Ahora `workspaceElement`, capturado en
`init()`, proporciona siempre el ancho del contenedor completo. La captura del
puntero también pertenece a ese contenedor estable.

Los dos elementos `aside` usan `wire:ignore.self`: Livewire puede actualizar sus
campos, pero conserva los estilos del panel que controla Alpine.

## Límites y persistencia

El panel tiene un mínimo de 420 px y deja al menos 400 px para la tabla. Si el
espacio disponible es menor de 820 px, funciona como panel móvil de pantalla
completa. El ancho se guarda durante el movimiento en el navegador, como antes.
La pestaña favorita sigue guardada en la base de datos, por usuario y módulo.

Para cambiar los límites, edita `minimumDrawerWidth` y `minimumTableWidth` en el
componente compartido. No dupliques el controlador en cada módulo.

## Validación

`node tests/Frontend/drawer-resize.cjs` prueba el controlador real extraído de
Blade: contexto de `$el` descendiente, movimiento en ambos sentidos, liberación,
recarga, límites y transición móvil. No requiere paquetes npm.
Las pruebas PHP y la compilación de Blade verifican la integración. La prueba
de JavaScript simula los eventos; falta confirmar el gesto en el navegador local.
