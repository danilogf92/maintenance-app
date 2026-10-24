# Entidades: implementadas y pendientes al revisar v11.48.0

Consulta ESTADO_DESARROLLO.md para el orden de interfaces y GOOGLE_LOGIN.md para activar el acceso adicional.

## Implementadas para el borrador

| Entidad | Situación |
| --- | --- |
| Usuarios / roles / permisos | Existentes; nuevos permisos del flujo operativo |
| Instituciones | Existente; utilizada como ubicación del equipo |
| Clientes | Registro, contacto básico, dirección y activo |
| Equipos | Cliente, institución, marca/modelo relacionados, serie, área y estado |
| Fabricantes, marcas y modelos | Catálogos relacionados, logo del fabricante e integración con Equipos/Informes |
| Tickets | Equipo, técnico, asunto, prioridad, fecha y estado |
| Servicios / visitas | Ticket, técnico, fecha, tipo libre, resumen, recomendaciones y estado |
| Actividades / hallazgos | Servicio, descripción, resultado y fotos privadas |
| Informes técnicos | Borrador con revisión y copia cifrada de datos/diseño; PDF por cola |
| Plantillas y parámetros de empresa | Existentes; se reutilizan para los informes reales |

## Entidades y extensiones pendientes

1. **Contactos:** varias personas, cargos y vínculos con clientes/instituciones. Los datos básicos de contacto actuales no sustituyen esta entidad.
2. **Componentes:** elementos asociados al equipo e historial.
3. **Documentación del equipo:** manuales, certificados, archivos privados y clasificación.
4. **Contratos:** cobertura, condiciones y equipos incluidos.
5. **Cronogramas de mantenimiento:** visitas programadas y cumplimiento.
6. **Tipos de servicio:** catálogo administrable. Hoy se registra texto libre en el servicio.
7. **Protocolos y pasos:** listas de trabajo por marca/modelo/tipo de servicio.
8. **Recomendaciones como entidad:** catálogo y seguimiento. Hoy son texto por líneas dentro del servicio.
9. **Repuestos:** catálogo, códigos y características.
10. **Repuestos instalados/retirados:** movimientos e historial por equipo.
11. **Firmas de cada informe:** captura nueva, firmante, permisos y finalización. El campo de diseño no sustituye el registro de firma.
12. **Órdenes de facturación y rubros:** agrupación de servicios/cargos para facturar.
13. **Historial de propietarios y ubicaciones:** transferencias de equipo sin sobrescribir relaciones previas.
14. **Subtareas:** distribución del trabajo de tickets y servicios.
15. **Aprobaciones y versiones finales:** revisión, emisión, correcciones e historial persistente de todas las versiones del informe.
16. **Auditoría:** quién cambió cada campo y sus valores previos; actualmente se guardan creador, fechas y control de concurrencia, no una auditoría completa.
17. **Estadísticas y exportaciones:** indicadores técnicos/comerciales y Excel según rol.
18. **Migración Ninox:** mapeo definitivo, importación de registros/archivos, relaciones y conciliación.

No se incorporan automáticamente notificaciones, correo con Google, firmas/collages de otros proyectos o integraciones externas. Se definirán como trabajos adicionales cuando corresponda. Los permisos por institución/equipo colaborador también requieren su definición específica; no están implícitos en las seis entidades iniciales.

## Orden sugerido para continuar

Revisar el flujo con registros representativos → completar contactos → protocolos y documentación → repuestos/contratos/cronogramas → firmas y aprobación final → migración/auditoría/indicadores. Ajustar este orden según lo que se valide en el borrador.
