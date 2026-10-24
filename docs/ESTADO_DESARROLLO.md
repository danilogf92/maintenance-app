# Maintenance APP — revisión del desarrollo v11.48.0

Estado basado en el código de esta entrega. Este documento y PENDIENTES_ENTIDADES.md describen Maintenance APP; los planes históricos de capacitación que permanecen en FASES.md no son el alcance de esta aplicación.

## Ya disponible para presentar el borrador

- Presentación pública y acceso por contraseña; opción Google con vinculación explícita y configuración OAuth pendiente en la instalación real.
- Instituciones, Usuarios, Clientes, Equipos, Tickets, Servicios, Actividades e Informes técnicos con fichas organizadas en pestañas, iconos, selección y editor lateral.
- Vistas personales, columnas, ordenación, filtros de columna y agrupaciones en las tablas modernizadas.
- Relaciones navegables: cliente → equipos → tickets → servicios. Alcance según permisos y técnico asignado.
- Actividades ya incorpora General, Actividad y resultado y Evidencias privadas. Informes técnicos incorpora General, Plantilla, Revisión y PDF, con los datos reales relacionados capturados por revisión. Servicios incorpora General, Intervención, Actividades e Informes.
- Plantillas de informes, estilos configurables y PDF temporal por cola; la emisión final con firmas/aprobaciones no está terminada.
- Datos DEMO relacionados y actualización con un comando, sin respaldos automáticos.

## Interfaces principales completadas

| Orden | Entidad | Trabajo pendiente |
| --- | --- | --- |
| 1 | Informes técnicos | Disponible: datos, plantilla, revisión, actualización explícita y PDF por cola con apertura para imprimir |

Las interfaces reutilizan el editor de Instituciones. Fabricantes, marcas y modelos ya están disponibles con logo e integración en Equipos/Informes. La siguiente entidad recomendada es Contactos.

## Después: entidades y reglas del negocio

1. Contactos múltiples de clientes e instituciones y sus cargos.
2. Documentación y componentes del equipo, con archivos y relaciones.
3. Tipos de servicio y protocolos con pasos y resultados.
4. Catálogo de repuestos e historial de piezas instaladas o retiradas.
5. Contratos, equipos cubiertos y cronogramas de mantenimiento.
6. Recomendaciones con seguimiento; hoy son texto dentro del servicio.
7. Transferencias de propietario y ubicación con historial; hoy se protege la relación cuando hay trabajo registrado.
8. Firmas por informe, revisión, aprobación, emisión y correcciones con versiones conservadas.
9. Auditoría completa, indicadores por rol y exportaciones.
10. Migración Ninox: campos definitivos, relaciones, archivos, conciliación y validación con datos reales autorizados.
11. Validación del despliegue Windows/Docker y luego del servidor, recuperación operativa y documentación de usuario/administrador antes de la entrega productiva.

Órdenes de facturación, subtareas y nuevas integraciones se mantienen pendientes de validación de alcance. El login Google no incluye invitaciones, recuperación automática de contraseña ni envío desde Gmail. Ver el inventario completo en PENDIENTES_ENTIDADES.md.

## Criterio de borrador frente a entrega final

El borrador permite mostrar navegación y recorrer registros relacionados. La entrega productiva requiere validar reglas reales, completar las entidades contratadas, conciliar la migración y aprobar informes/firmas, permisos, operación y documentación. No se presenta como terminado lo que solo tiene campos de diseño o datos DEMO.
