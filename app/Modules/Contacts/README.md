# Contactos

Directorio de personas sin cuentas de acceso. Ruta normal `/operations/contacts`; Admin permanece en `/admin`.

General contiene nombre, cargo, área y estado. Comunicación contiene correo, teléfono, extensión y notas. Vínculos permite asociar una persona con varios clientes e instituciones y asignar una función distinta en cada organización. Las pestañas Contactos de Clientes e Instituciones muestran los mismos vínculos; Nuevo contacto abre el editor y crea el vínculo junto con la persona al guardar.

Solo hay un principal por organización, compartido entre todas las categorías. Asignar otro principal sustituye al anterior. Desvincular requiere confirmación y conserva el registro. La reactivación se hace desde Incluir vínculos desactivados → Reactivar → Guardar vínculo. El contacto y, cuando corresponda, el cliente deben estar activos. Inactivar una persona conserva sus vínculos y quita su condición de principal; reactivarla no restituye automáticamente esa condición.

`contacts.view` exige también `workflow.access`. `contacts.manage` autoriza altas y cambios. Administrador y Admin reciben ambos permisos; Técnico, Engineer y CEO reciben lectura. Cada vínculo exige además permiso de acceso a su cliente o institución. El directorio de personas es global para los usuarios con acceso a Contactos. Los datos de organizaciones sin permiso no se muestran ni se pueden modificar mediante identificadores enviados manualmente.

Las transacciones bloquean persona y organización, y una clave única protege el principal. Los bloqueos optimistas impiden sobrescribir editores desactualizados. La migración añade tablas sin modificar registros existentes. No se crean cuentas ni se envían mensajes. Los datos DEMO se identifican explícitamente y se crean una sola vez, sin sobrescribir cambios del usuario.

Tickets admite un solicitante/contacto opcional desde v11.51.0, limitado a los contactos vinculados al cliente o institución del equipo según permisos. También permite un nombre reportado como texto sin crear contactos ni cuentas. Pendiente para otra entidad: destinatario/firma en Servicios e Informes. No se inventan solicitantes en datos históricos.

## Interfaz v11.50.1

Los vínculos se presentan en tarjetas adaptables al ancho del panel, con acciones visibles sin desplazamiento horizontal. El principal se destaca y el registro que se edita lleva una marca. El formulario se abre solo al vincular o editar; la búsqueda por nombre es desplegable. Escape cancela la configuración o confirmación actual. Se mantienen las pestañas y el panel redimensionable existentes.

Las vistas parciales `UI/Views/link-card.blade.php`, `link-form.blade.php` y `link-confirmation.blade.php` separan cada responsabilidad visual. Los estilos están en `public/css/pages/contacts.css`, cargados con versión para renovar la caché del navegador.
