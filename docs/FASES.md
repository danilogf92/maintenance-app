# Plan incremental de desarrollo

Versión base: v0.1.0. Las fases 2–8 están pendientes; no confundir el historial original con funcionalidades de esta plantilla. Los ítems conservan su identificación entre entregas.

## Fase 1 — Base reutilizable (incluida)

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 1.1 | Laravel, Livewire y Filament; dos espacios | Ingreso y navegación al panel según acceso |
| 1.2 | Instituciones | Crear/editar; cada persona vinculada a una institución |
| 1.3 | Usuarios | Crear/editar nombre, correo, identificación, teléfono y cargo; desactivar |
| 1.4 | Varios roles y permisos | Roles combinables y autorización en servidor |
| 1.5 | SuperAdmin y configuración inicial | Creación por consola; protección del rol y cuenta propia |
| 1.6 | Nombre y logo | Mismos valores en ingreso, aplicación y administración |
| 1.7 | Instalación y documentación | CMD con destino nuevo; Docker/MariaDB/phpMyAdmin; pruebas |

## Fase 2 — Completar administración y acceso

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 2.1 | Límite configurable de sesiones | Bloquear inicio adicional sin expulsar sesiones anteriores; atomicidad en ingreso normal y Filament |
| 2.2 | Sesiones visibles y cierre | Registro de dispositivos y cierre controlado; vencimiento consistente |
| 2.3 | Grupos y pertenencia | Administrar miembros; preparado para asignaciones a ediciones |
| 2.4 | Auditoría administrativa | Actor, fecha y cambios; excluir contraseñas y tokens |
| 2.5 | Recuperación de acceso y perfil | Flujo documentado; recuperación y protección de credenciales |
| 2.6 | Permisos de capacitación | Crear/editar/publicar contenidos, exportar y consultar estadísticas por permiso |
| 2.7 | Refinar organización para reutilización | Si esta plantilla también se usa en plantas: países, plantas, membresías múltiples y cargo normalizado; pertenecer no concede permisos. Extensión opcional, no decisión impuesta al sistema de capacitación |

Cierre: probar simultáneamente ingreso desde varios dispositivos, desactivación y permisos en ambos espacios.

## Fase 3 — Cursos, temarios y ediciones

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 3.1 | Catálogo por equipo/modelo y tema | Introducción, objetivos, requisitos, portada |
| 3.2 | Temario plano o módulos | Orden de lecciones y duración calculada solo del contenido accesible |
| 3.3 | Visibilidad por rol y asignaciones | Cursos y lecciones filtrados y autorizados en servidor |
| 3.4 | Borradores y versiones publicadas | Último borrador; versiones publicadas inmutables |
| 3.5 | Ediciones e instructores | Fechas comunes; varios instructores; ampliación de vencimiento |
| 3.6 | Inscripciones y grupos/roles | Revocar solo la fuente retirada; conservar avance e institución histórica |
| 3.7 | Archivo y duplicación | Conservar resultados; copiar estructura y banco sin personas/resultados |

Depende de la fase 2. Cierre: publicar un curso e inscribir participantes de dos instituciones con visibilidades distintas.

## Fase 4 — Editor de contenido interactivo

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 4.1 | Bloques texto/foto/video | Añadir, editar y ordenar visualmente |
| 4.2 | Marcas sobre fotos | Arrastrar flechas y “+”; coordenadas relativas; zoom y desplazamiento |
| 4.3 | Componentes y procedimientos | Modal y recorrido completo o paso a paso; todos los procedimientos del componente visibles |
| 4.4 | Reutilizar o copiar procedimientos | Versiones independientes al duplicar; evitar ciclos en procedimientos anidados |
| 4.5 | Autosave y bloqueo de edición | Un editor; aviso del responsable y bloqueo renovable |
| 4.6 | Previsualización y publicación | Vista por rol; validación de integridad y permiso de publicación |

Cierre: comprobar foto con marcas en escritorio y móvil y conflicto entre dos editores.

## Fase 5 — Videos y progreso

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 5.1 | Archivo privado o importación Drive | Validar origen; credenciales privadas; no subir automáticamente a Drive |
| 5.2 | Procesamiento con cola y FFmpeg | HLS; reintentos y errores claros; conservar segmentos finales |
| 5.3 | Reproducción autenticada | Autorizar segmentos; no exponer URL original |
| 5.4 | Intervalos vistos | Adelantar permitido; exigir todo el contenido a velocidad normal o menor |
| 5.5 | Procedimientos/lecciones completados | Final del contenido y videos terminados; reconocimiento por versión en la misma edición |
| 5.6 | Recorrido secuencial y continuar | Desbloquear lo accesible y abrir la primera lección pendiente, incluso tras cambio de rol |
| 5.7 | Vencimiento | Bloquear contenido, segmentos y evaluación al vencer |

HLS no impide grabar la pantalla ni reconstruir segmentos por un usuario autorizado; no ofrecer DRM inexistente. Cierre: video real, saltos pendientes, permisos y revocación.

## Fase 6 — Evaluaciones y resultados

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 6.1 | Banco de preguntas | Única, múltiple y verdadero/falso, puntos y explicación |
| 6.2 | Sorteo según contenido accesible | Cantidad configurable; mismas preguntas al reanudar |
| 6.3 | Puntaje e intentos | Parcial sin incorrectas en selección múltiple; cero con incorrectas; nota mínima y límite |
| 6.4 | Entrega y retroalimentación | Correctas y explicación solo al finalizar; sin límite temporal |
| 6.5 | Intentos adicionales | Administrador o instructor asignado; registro |
| 6.6 | Cambio de roles | Mantener aprobaciones previas; lecciones nuevas y evaluación complementaria |
| 6.7 | Historial de aprobación | Registrar versión, contenido/roles, nota y fecha; no certificados automáticos |

Cierre: completar recorrido, aprobar, ampliar el rol y aprobar un complemento sin borrar resultados anteriores.

## Fase 7 — Consultas y comunicación

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 7.1 | Consulta privada por lección | Solo participante e instructores asignados/autorizados |
| 7.2 | Adjuntos | Hasta cinco, 10 MB cada uno, tipos permitidos y autorización de descarga |
| 7.3 | Respuestas de instructores | Cualquiera de los asignados; conservar histórico tras vencimiento |
| 7.4 | Notificaciones internas | Pendientes en inicio; sin envío de correo |

## Fase 8 — Estadísticas, exportaciones y operación

| Ítem | Desarrollo | Aceptación |
|---|---|---|
| 8.1 | Inicio del participante | Cursos, avance, continuar, vencimientos y consultas |
| 8.2 | Actividad y accesos | Heartbeat visible/interacción reciente o video; evitar doble conteo |
| 8.3 | Panel por institución/persona | Gráficos y tablas; filtros de grupo, curso, edición y fecha |
| 8.4 | Alcance de informes | Global con permiso; instructor solo sus ediciones |
| 8.5 | Excel de uso | Hojas Avance por edición y Acceso persona; actividad por fecha y avance acumulado |
| 8.6 | Excel Marketing | Una fila por aprobación; varios cursos/fechas; contenido aprobado y complemento pendiente |
| 8.7 | Verificación de operación | Docker real, MariaDB real, respaldo/restauración, dominio/HTTPS, Drive, carga y móviles |
| 8.8 | Plantilla estable v1.0 | Instalación limpia, actualizaciones con historial y manual final Word/Markdown |

## Forma de trabajar en cada entrega

1. Seleccionar un ítem o conjunto pequeño, por ejemplo `2.1 + 2.2`.
2. Cambiar código y añadir migraciones; no editar migraciones ya ejecutadas.
3. Probar comportamiento y permisos de ese cambio.
4. Documentar archivos y pasos de actualización; preservar `.env`, `APP_KEY`, datos y archivos privados.
5. Entregar ZIP completo y, cuando exista una versión anterior instalada, un parche con manifiesto y respaldo previo de los archivos reemplazados.
6. Registrar versión, comprobaciones reales y pendientes.

**Siguiente bloque recomendado: 2.1 y 2.2 — límite y administración de sesiones.**
