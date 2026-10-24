<?php
namespace App\Modules\DataManagement\Support;
use App\Modules\DataManagement\Configuration\EquipmentHistories;
class EquipmentHistoryPackage extends CatalogPackage
{
    protected function inbox(): string { return 'data-management/history-inbox'; }
    protected function prepareCommand(): string { return 'PrepararHistoriales.cmd'; }
    protected function definitions(): array { return array_map(fn ($definition) => $definition['table'], EquipmentHistories::all()); }
}
