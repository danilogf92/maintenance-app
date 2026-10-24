<?php
namespace App\Modules\DataManagement\Configuration;
class SourceHints
{
    public static function for(array $source): array
    {
        $id = count($source['source_table_ids']) === 1 ? $source['source_table_ids'][0] : '';
        return match ($id) {
            'QC' => ['target' => 'equipment_institution_histories', 'note' => 'Centros asociados: centers → institución, equipment → equipo, assigned_date → fecha. La carga conserva el historial y valida la asignación actual.'],
            'RC' => ['target' => 'equipment_client_histories', 'note' => 'Clientes asociados: clients → cliente, equipments → equipo, assignation_datr → fecha. La carga conserva el historial y valida la asignación actual.'],
            'A' => ['target' => 'clients', 'note' => 'Nombre comercial → name; RUC → tax_id; correo de facturación → email. Carga disponible en Carga de datos; ubicaciones y contactos se revisan por separado.'],
            'B' => ['target' => 'institutions', 'note' => 'Nombre y dirección tienen destino en Carga de datos. city_id y contact_id requieren resolver sus entidades de origen.'],
            'LB' => ['target' => 'activities', 'note' => 'report_id debe resolverse contra el servicio; distinguir descripción, hallazgo y fotos. Falta el adaptador de esta carpeta.'],
            'DB' => ['target' => 'contract_equipment_coverages', 'note' => 'Asociación posible, pendiente de confirmar. La cobertura requiere first_month y end_month; esta fuente solo trae contrato y equipo.'],
            'X' => ['target' => 'contact_links', 'note' => 'Posible vínculo de cliente/contacto. Primero se necesitan los contactos y sus identificadores verificados.'],
            'V' => ['target' => 'ticket_field_engineer', 'note' => 'Asignaciones históricas FSE. No son visitas del calendario. La fecha recibida requiere conservación específica.'],
            'WB' => ['target' => null, 'note' => 'El desarrollo guarda recomendaciones en services.recommendations. Los campos value_1/2/3 necesitan interpretación antes de transformar.'],
            'SC' => ['target' => null, 'note' => 'Firmas históricas del cliente: falta la entidad de firma y su relación con servicio/contacto. No se reutilizan para nuevas firmas.'],
            'OE' => ['target' => null, 'note' => 'Componentes incluidos en la bitácora: todavía no hay una entidad operativa equivalente.'],
            'P' => ['target' => null, 'note' => 'Documentación de modelos: requiere entidad documental y acceso privado a los adjuntos.'],
            'D' => ['target' => 'maintenance_contracts', 'note' => 'Verificar número, nombre, cliente y fechas. No convertir duración a meses sin confirmar su unidad.'],
            'LE' => ['target' => null, 'note' => 'Reportería no se asigna a informes técnicos por coincidencia del nombre. Requiere revisar su finalidad.'],
            'C' => ['target' => 'equipment', 'note' => 'La carga anterior mantiene pendientes cliente e institución; hacen falta sus historiales para fijar la relación actual.'],
            'T' => ['target' => 'field_engineers', 'note' => 'Ingenieros de servicio separados de usuarios de acceso. No se crean cuentas automáticamente.'],
            'GB' => ['target' => 'maintenance_protocols', 'note' => 'Carga incluida en versiones anteriores. Revisar versión, vigencia y componentes pendientes.'],
            'HB' => ['target' => 'maintenance_protocol_steps', 'note' => 'Carga incluida; los pasos sin padre confirmado siguen pendientes.'],
            'F' => ['target' => 'tickets', 'note' => 'Carga incluida; las referencias de origen no equivalen a los IDs locales.'],
            'K' => ['target' => 'services', 'note' => 'Carga incluida; conservar vínculo al ticket, técnico y documentos históricos.'],
            'N' => ['target' => 'brands', 'note' => 'Carga de marcas incluida; revisar vínculos con fabricante.'],
            'O' => ['target' => 'equipment_models', 'note' => 'Carga de modelos incluida; verificar marcas y documentación asociada.'],
            default => ['target' => null, 'note' => 'Sin equivalencia confirmada. Se conserva como pendiente; no se creará una entidad por similitud de nombre.'],
        };
    }
}
