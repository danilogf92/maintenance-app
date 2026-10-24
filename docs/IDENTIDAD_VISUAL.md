# Identidad visual — Maintenance APP v11.49.0

Marca del proyecto: M blanca sobre azul con acento turquesa. Paleta de referencia HEX: azul #1D4ED8 y #2563EB, turquesa #14B8A6, blanco #FFFFFF y texto #0F172A.

## Archivos

- maintenance-mark-original.png: imagen original con transparencia, 1254 × 1254 px.
- maintenance-mark-32/48/96/180/192/512.png: tamaños preparados para navegador, interfaz y accesos directos.
- maintenance-logo-light.svg y maintenance-logo-dark.svg: composición horizontal para fondo claro/oscuro. El símbolo se incluye como PNG dentro del SVG; el texto permanece editable. No es una vectorización del símbolo.
- favicon.ico: 16, 24, 32, 48, 64, 128 y 256 px.
- favicon.png: versión PNG de 32 px.

Las reducciones y la conversión ICO conservan el dibujo y su transparencia. No hay fondos blancos añadidos al símbolo. Los SVG horizontales usan Inter, Segoe UI o Arial.

## Integración

Los archivos están en public/branding/maintenance. El componente layouts/favicon.blade.php declara los iconos en las páginas normales y públicas. ui/project-mark.blade.php muestra el símbolo en cabeceras; AdminPanelProvider configura el logo predeterminado y favicon del administrador.

Los logos corporativos de BrandSetting siguen teniendo prioridad donde ya estaban configurados. La identidad del proyecto no se escribe en la base de datos ni sustituye los logos de empresa/fabricante de los informes. Cambiar el logo de la empresa continúa siendo una configuración independiente.

El manifiesto aporta los iconos de accesos directos; no añade service worker, instalación PWA ni modo offline. Las referencias llevan versión para renovar la caché del navegador. No hay migraciones ni dependencias nuevas.

## Origen y prompt

Símbolo creado con la herramienta integrada de generación de imágenes. Prompt final:

Use case: logo-brand. Create a finished square app icon / brand mark for Maintenance APP, a professional technical maintenance management platform for biomedical equipment. A bold simple geometric white capital M inside a solid deep royal blue rounded-square tile (#1D4ED8 / #2563EB), with a single restrained cyan-teal accent (#14B8A6) integrated as a short service-check stroke at the lower right of the M. The M should be immediately unmistakable and legible at 16x16 pixels: two broad upright pillars and a strong central V, no fine strokes. Flat vector-style precision, optical balance, crisp clean edges, understated enterprise software identity, no gradients, no shadows, no 3D, no gears or tiny circuit details. Only one centered icon, no text besides the M, no brand sheet, no mockup, no watermark. Fill most of the square canvas, about 4 percent transparent outer margin around the tile. Genuine transparent alpha outside the rounded tile. The result is the actual production logo mark and favicon source, not a presentation.
