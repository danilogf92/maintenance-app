# Módulo Institution — Instituciones

| Archivo | Responsabilidad |
|---|---|
| app/Livewire/Institution/InstitutionPage.php | Estado de pantalla y carga de campos |
| app/Modules/Institution/InstitutionManager.php | Validación, permiso de guardado/borrado y relaciones |
| app/Modules/Institution/InstitutionQuery.php | Búsqueda y filtro por tipo |
| app/Modules/Institution/InstitutionDefinition.php | Columnas y pestañas iniciales |
| routes/modules/institution.php | Ruta protegida |
| resources/views/livewire/institution | index, toolbar, filters, table, form y fields |

La página hereda el editor compartido. Para añadir un campo, actualiza su migración/modelo, `InstitutionManager::FIELDS`, las reglas, el estado de `InstitutionPage` y el input. Para mostrarlo en tabla, modifica la definición y el caso de columna.

El teléfono admite 40 caracteres, igual que la columna de base de datos. Una institución con usuarios asociados no se elimina. El servicio comprueba la relación dentro de una transacción con bloqueo del registro; la clave foránea sigue protegiendo la base.

Los formularios Filament CreateInstitution/EditInstitution delegan también en este servicio. La URL `/operations/institutions` y su nombre se conservan para los enlaces existentes. El código propio está separado de Operations.
