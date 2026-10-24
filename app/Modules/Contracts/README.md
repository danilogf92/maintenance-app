# Contratos de mantenimiento

Menú normal → Contratos, `/operations/contracts`. Utiliza la tabla completa y el editor lateral con pestañas del sistema. No hay paginación ni registros de demostración.

## Estructura

- `maintenance_contracts`: número real, nombre, cliente opcional, condiciones generales, activo y revisión.
- `contract_equipment_coverages`: un equipo dentro de un contrato, mes de inicio, mes final y requerimientos propios. Un contrato admite muchos equipos y cada equipo puede tener una vigencia distinta. El mes final está incluido en la cobertura.
- `maintenance_plans.contract_coverage_id`: relación opcional entre un plan y esa cobertura. Cada cobertura admite varios planes con tipos de mantenimiento y frecuencias diferentes. Un plan sin esta relación es independiente por equipo.

La cobertura no se comparte entre equipos ni se calcula a partir de la duración de otro equipo. No se asignan fechas o contratos por inferencia a los datos importados. Las referencias de texto históricas permanecen como texto y no sustituyen la selección de una cobertura real.

## Flujo

1. Crea el contrato con su número y nombre. El cliente es opcional mientras se completan sus relaciones reales.
2. Abre Equipos y cumplimiento → Añadir equipo. Selecciona un equipo existente, sus meses de cobertura y requerimientos particulares.
3. Usa Crear plan para este equipo: abre Planificación con el equipo, la cobertura y su vigencia seleccionados.
4. Define el tipo de mantenimiento y su frecuencia en días, semanas, meses o años. La vigencia del plan debe quedar dentro de la cobertura.
5. La cantidad comprometida total es opcional. Si se deja vacía, se calcula con la frecuencia y toda la vigencia del plan. No es una cantidad anual. Una cantidad explícita no puede superar las visitas posibles con esa frecuencia y vigencia.
6. En Visitas, programa los próximos períodos. Genera el ticket cuando corresponda y registra sus servicios por el flujo existente.
7. Al poner un servicio del ticket en Completado, la visita pasa a contar como cumplida. No requiere aprobar un informe ni resolver el ticket.

## Cumplimiento

Una visita cuenta una sola vez si tiene al menos un servicio completado en su ticket. Dos servicios completados no suman dos visitas. Si se reabre o cancela el único servicio completado, el cumplimiento se recalcula. Los servicios históricos sin vínculo explícito con una visita no se atribuyen a contratos nuevos.

Por equipo se muestran comprometidas, programadas no canceladas, cumplidas, pendientes totales, visitas por programar y canceladas. Las vencidas del resumen corresponden a visitas programadas cuyo período ya terminó y que no tienen servicio completado. Lo aún no programado se muestra por separado; no se considera cumplido. Sin planes visibles no hay un compromiso calculable.

Las cifras respetan los planes que puede consultar el usuario. El administrador con `workflow.view_all` puede ver el conjunto; un lector con alcance limitado no recibe cifras de planes ajenos. Un contrato inactivo conserva su historial y bloquea nuevas coberturas, programación y generación de tickets asociados.

## Integridad y permisos

`contracts.view/manage` requiere acceso al flujo y lectura de planificación/equipos. El seeder añade lectura y gestión a Admin/Administrador y lectura a Técnico/Engineer/CEO; SuperAdmin mantiene acceso global. No elimina permisos personalizados ni crea cuentas.

Los identificadores se validan en servidor y por alcance. No se puede seleccionar la cobertura de otro equipo ni modificar una visita de otro plan. Un cliente conocido y distinto en contrato/equipo se rechaza; no se altera la propiedad del equipo. Si la relación de cliente del equipo está pendiente, la selección explícita permite añadirlo sin inventar o modificar esa relación.

No se borran contratos/coberturas desde la interfaz. Una cobertura con planes conserva su equipo y vigencia; sus requerimientos pueden editarse. Un contrato con equipos conserva su cliente conocido. Un cliente pendiente puede completarse si no contradice las relaciones conocidas de los equipos. Las revisiones y bloqueos transaccionales rechazan cambios desactualizados.

## Dónde cambiar

Configuration: campos, pestañas, columnas, relaciones y permisos. Actions/SaveContract y SaveCoverage: reglas y persistencia. Queries/ContractQuery: alcance. Queries/Compliance: cómputo por servicio completado. UI: tabla, editor y panel de equipos. `public/css/pages/contracts.css`: presentación del resumen y agenda.
