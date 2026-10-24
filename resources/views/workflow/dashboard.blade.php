<x-filament-panels::page>
    <p>Clientes → Equipos → Tickets → Servicios → Actividades → Informes técnicos.</p>
    <p>Registra los datos en ese orden. Desde cada registro puedes abrir el siguiente paso. Los informes actuales son borradores y sus PDF se generan mediante cola.</p>
    <ul style="display:grid;gap:16px;margin-top:20px">
        @foreach(\App\Modules\Workflow\Configuration\Modules::all() as $module => $definition)
            @if(\App\Modules\Workflow\Configuration\Permissions::allows($module))
            <li><a style="font-weight:600;text-decoration:underline" href="{{ \App\Modules\Workflow\UI\Resources::get($module)::getUrl('index') }}">{{ $definition['label'] }}</a></li>
            @endif
        @endforeach
    </ul>
    <p style="margin-top:24px">Pendientes: catálogos de marcas/modelos, contactos, componentes y protocolos, documentos técnicos, contratos y cronogramas, repuestos, firmas manuscritas, aprobación/emisión final, facturación, migración Ninox, auditoría completa e indicadores.</p>
</x-filament-panels::page>
