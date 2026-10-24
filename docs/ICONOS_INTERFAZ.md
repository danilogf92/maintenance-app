# Iconos de la interfaz operativa

Los iconos son SVG locales, del mismo estilo, decorativos y acompañados por texto accesible. No dependen de servicios externos.

- Menú de módulos: ui_icon en app/Modules/Workflow/Configuration/Modules.php. icon conserva su uso para Filament.
- Pestañas internas: clave icon en Configuration/Tabs.php de cada entidad.
- Vistas personales: app/Support/Ui/RecordIcons.php, según módulo y filtro. El favorito conserva su estrella.
- Dibujos SVG: resources/views/components/ui/icon.blade.php. Nombres desconocidos usan el icono panel.
- Botones de los módulos pendientes de rediseño: resources/views/workflow/operations.blade.php.

Los iconos no sustituyen los nombres ni los textos de acciones. Selección, hover, contraste y foco se conservan. La X de cerrar sigue siendo roja en las fichas tabuladas.
