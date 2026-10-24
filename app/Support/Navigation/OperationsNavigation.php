<?php

namespace App\Support\Navigation;

use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
use Illuminate\Support\Facades\Gate;

class OperationsNavigation
{
    public static function groups(): array
    {
        $items = [];
        $add = function (string $key, string $label, string $icon, bool $allowed, ?string $url = null) use (&$items): void {
            if ($allowed) {
                $items[$key] = ['label' => $label, 'icon' => $icon,
                    'url' => $url ?? route('operations.'.$key),
                    'active' => request()->routeIs('operations.'.$key)];
            }
        };
        foreach (\App\Modules\Workflow\Configuration\Modules::all() as $key => $definition) {
            $add($key, $definition['label'], $definition['ui_icon'], WorkflowPermissions::allows($key));
        }
        foreach (\App\Modules\Catalogs\Configuration\Catalogs::all() as $key => $definition) {
            $add($key, $definition['label'], $definition['icon'], WorkflowPermissions::allows($key));
        }
        $add('institutions', 'Instituciones', 'building', Gate::allows('institutions.manage'));
        $add('contacts', 'Contactos', 'users', \App\Modules\Contacts\Configuration\Permissions::allows());
        $add('contracts', 'Contratos', 'file-text', \App\Modules\Contracts\Configuration\Permissions::allows());
        $planning = \App\Modules\MaintenancePlans\Configuration\Permissions::allows();
        $add('maintenance_plans', 'Planificación', 'calendar', $planning);
        $add('maintenance_calendar', 'Calendario', 'calendar', $planning);
        $add('field_engineers', 'Ingenieros de servicio', 'users', \App\Modules\FieldEngineers\Configuration\Permissions::allows());
        $add('maintenance_protocols', 'Protocolos', 'activity', \App\Modules\MaintenanceProtocols\Configuration\Permissions::allows());
        $add('users', 'Usuarios', 'users', Gate::allows('users.manage'));
        $add('data-review', 'Datos por completar', 'activity', \App\Modules\DataReview\Queries\ReviewStatus::allowed());
        $add('report_templates', 'Plantillas de informes ↗', 'file-text',
            \App\Modules\ReportTemplates\Configuration\Permissions::allows(auth()->user()), url('/admin/report-templates'));

        $definitions = [
            'clients-contacts' => ['Clientes y contactos', 'briefcase', ['clients', 'institutions', 'contacts']],
            'equipment-catalogs' => ['Equipos y catálogos', 'cpu', ['equipment', 'manufacturers', 'brands', 'equipment_models']],
            'contracts-planning' => ['Contratos y planificación', 'calendar', ['contracts', 'maintenance_plans', 'maintenance_calendar']],
            'technical-service' => ['Servicio técnico', 'wrench', ['tickets', 'services', 'maintenance_protocols', 'activities', 'technical_reports', 'field_engineers']],
            'administration-data' => ['Administración y datos', 'settings', ['users', 'report_templates', 'data-review']],
        ];
        $groups = [];
        foreach ($definitions as $id => [$label, $icon, $keys]) {
            $children = array_intersect_key($items, array_flip($keys));
            // Use the explicit menu order, independently of module registration order.
            $children = array_replace(array_fill_keys($keys, null), $children);
            $children = array_filter($children);
            if ($children) {
                $groups[] = compact('id', 'label', 'icon') + ['items' => $children,
                    'active' => collect($children)->contains('active', true)];
            }
        }
        return $groups;
    }
}
