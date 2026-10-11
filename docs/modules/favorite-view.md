# Pestaña favorita

Abre Configurar vista (engranaje o clic derecho sobre una pestaña) y presiona **Abrir esta vista al entrar**. Una estrella identifica la favorita. El cambio se guarda al instante.

Hay una única favorita por persona y módulo: elegir otra reemplaza la anterior. Usuarios e Instituciones pueden tener favoritas distintas. Al volver a entrar o recargar la página se abre siempre la favorita; navegar a otra pestaña no cambia esa preferencia.

Si nunca elegiste una, la primera pestaña es la favorita inicial. Si borras una pestaña personalizada favorita, se selecciona la primera existente como nueva favorita. `is_default` identifica pestañas iniciales protegidas contra borrado; no significa favorita.

| Archivo | Responsabilidad |
|---|---|
| app/Modules/Shared/FavoriteView.php | Elegir/recuperar favorita y verificar usuario/módulo |
| app/Models/OperationViewPreference.php | Preferencia persistente |
| app/Livewire/Shared/HasRecordViews.php | Abrir favorita al montar y exponer acción |
| components/record-editor/view-settings | Botón de elección |
| components/record-editor/workspace-navigation | Estrella en pestaña |
| migration create_operation_view_preferences | Tabla, FK y unicidad por usuario/módulo |

La selección usa transacción y bloqueo por usuario; el índice único asegura que no se creen dos preferencias para el mismo módulo. No cambia ni elimina pestañas o registros existentes. La actualización crea únicamente la nueva tabla de preferencias.
