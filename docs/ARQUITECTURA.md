# Arquitectura de la base

## Capas y puntos de extensión

| Carpeta/archivo | Responsabilidad |
|---|---|
| app/Models | Usuarios, instituciones y marca; relaciones y tipos |
| app/Services/UserManager.php | Validación, autorización y transacción al crear/editar personas |
| app/Filament/Resources | Formularios y tablas administrativos; autorización de cada recurso |
| app/Livewire/Home.php | Pantalla de aplicación normal |
| app/Http/Controllers/SessionController.php | Inicio/cierre de sesión normal |
| app/Http/Middleware/EnsureUserIsActive.php | Revocar acceso de usuarios desactivados |
| app/Providers/AppServiceProvider.php | Gate: SuperAdmin activo y rechazo de cuentas inactivas |
| app/Providers/Filament/AdminPanelProvider.php | Configuración del panel y logo |
| app/Console/Commands/SetupTemplate.php | Configuración de instalación nueva |
| database/seeders/DatabaseSeeder.php | Catálogo inicial de permisos y roles; no cuentas con contraseñas fijas |
| database/migrations | Estructura; las siguientes entregas agregarán archivos nuevos |
| resources/views | HTML Blade en español |
| public/css/template.css | Estilos propios, sin paso de compilación Node |
| tests/Feature/TemplateTest.php | Pruebas de comportamiento y acceso |

## Entidades

| Entidad | Datos principales | Relación |
|---|---|---|
| institutions | nombre, código único, tipo | Una institución tiene varios usuarios |
| users | institución, nombre, correo único, identificación, teléfono, cargo, contraseña, activo, acceso admin | Una institución obligatoria; varios roles |
| roles | nombre y guard web | Varios usuarios; varios permisos |
| permissions | nombre y guard web | Varios roles; soporte del paquete para asignación directa |
| model_has_roles | vínculo usuario/rol | La interfaz maneja los roles mediante UserManager |
| role_has_permissions | vínculo rol/permiso | Editado por SuperAdmin |
| model_has_permissions | permisos directos | Tabla del paquete; sin editor de asignación directa en esta fase |
| brand_settings | nombre y ruta de logo | Configuración global, registro ID 1 |
| sessions | sesión, usuario, IP, agente y actividad | Tabla de sesión Laravel; aún sin límite simultáneo |
| cache/jobs | infraestructura | Preparación para extensiones |

## Acceso y límites de la versión

Institución es una pertenencia organizativa; **no separa bases de datos ni configura multiempresa aislada**. Los permisos administrativos de esta fase son globales. El cargo es texto informativo y no otorga permisos. Los futuros cursos implementarán sus propios alcances en servidor.

Roles iniciales: SuperAdmin, Administrador, Técnico, Cliente, Instructor y Marketing. Los cuatro últimos no reciben permisos administrativos por defecto; adquirirán sus capacidades de capacitación en las fases correspondientes.

- SuperAdmin activo tiene acceso global por Gate y puede crear otras cuentas administrativas.
- Administrador necesita el indicador de acceso administrativo **y** `panel.access`. Puede administrar instituciones y cuentas ordinarias; no cambiar cuentas administrativas ni asignar roles con permisos administrativos.
- Los roles se combinan. Agregar una institución o cargo no concede permisos.
- Solo SuperAdmin edita el catálogo de roles y la marca. El rol SuperAdmin no es editable en el panel.
- No se borran usuarios ni instituciones desde el panel: desactivar personas preserva la posibilidad de relaciones e historial futuro.
- UserManager protege la desactivación propia y exige conservar un SuperAdmin activo. Los futuros flujos de escritura de usuarios deben pasar por este servicio.
- La consola/SQL son herramientas privilegiadas; las restricciones del panel no impiden una modificación directa en la base.

No hay registro público. No se envían correos. La recuperación de contraseña, 2FA, límites de sesiones y auditoría todavía son pendientes.

## Diseño legible

Mantener formularios responsables de presentar datos y servicios responsables de reglas de negocio. Nombrar modelos y métodos en inglés y textos de interfaz en español. Comentar el motivo de una regla (por ejemplo, no perder el último SuperAdmin), no cada instrucción evidente. Los nuevos módulos de capacitación deberán añadir servicios separados de acceso, publicación, avance y evaluación.
