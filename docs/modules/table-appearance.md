# Apariencia de las tablas — v11.32

Usuarios e Instituciones usan el componente compartido
`resources/views/components/record-editor/row-styles.blade.php`.

- Filas impares blancas; filas pares gris suave (#f1f5f9).
- Líneas horizontales y divisiones verticales entre columnas (#dbe3ed).
- Hover azul claro y selección azul con indicador lateral, con prioridad sobre
  el color alternado.
- Tonos adaptados al modo oscuro; en móvil las divisiones verticales se ocultan
  cuando los registros se muestran como tarjetas.

Para cambiar los tonos, modifica `--row-white`, `--row-even` y `--cell-line`.
La alternancia depende de la posición visible de la fila, no del ID del registro;
se adapta a filtros, ordenación y eliminación sin peticiones adicionales.
