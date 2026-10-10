# Origen y decisiones de adaptación

Fuentes recibidas: `projectsIA.rar` y `Pasted text.txt` (historial Academia Técnica). Se revisaron el manifiesto Composer/lock, modelos User/Role/BrandSetting, formulario administrativo de usuarios, recursos de roles, proveedores del panel y aplicación, rutas y migraciones de usuarios/permisos del RAR.

El RAR contiene una aplicación de gestión de proyectos, no el ZIP `Academia_Tecnica_Laravel.zip` citado en el historial. La extracción de algunos archivos auxiliares de pruebas/assets presentó errores del lector RAR disponible; los archivos de código utilizados se extrajeron y leyeron. No se ejecutó ni certificó la aplicación original completa.

Se tomó el patrón de roles con Spatie, cuenta activa, acceso al panel separado, varias asignaciones de rol y marca configurable. Se adaptó a una plantilla independiente para capacitación: institución obligatoria por persona, roles de capacitación y usuario inicial SuperAdmin. Los módulos de proyectos, presupuestos, SAP, compañías y hojas Excel del RAR no se incorporan a esta plantilla.

El rol vinculado a company_id del sistema de proyectos no se convierte silenciosamente en aislamiento por institución: en esta base los roles administrativos son globales. Los alcances de cursos/lecciones/instructores se implementarán en sus fases según el historial. La extensión por países/plantas se identifica como opcional, sin imponerla al dominio de capacitación.

Se mantuvieron Laravel 12, Livewire 3, Filament 4 y Spatie 8 como líneas principales, retirando dependencias de gráficos, Fortify, Volt y Excel que esta fase no utiliza. Composer detectó avisos de seguridad en el lock recibido; se actualizaron los paquetes afectados y sus dependencias dentro de esas líneas, sin migrar a Livewire 4 ni Filament 5. Versiones finales: Laravel 12.69.3, Livewire 3.8.10, Filament 4.15.1 y Spatie 8.3.0. El lock final fija esas versiones.

El archivo `HISTORIAL_REFERENCIA.txt` contiene las decisiones y el manual de otra entrega, como especificación. Sus cifras de pruebas y sus funciones no describen el código v0.1.0 de este ZIP.
