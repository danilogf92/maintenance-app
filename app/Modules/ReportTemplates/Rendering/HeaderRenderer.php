<?php

namespace App\Modules\ReportTemplates\Rendering;

use App\Modules\ReportTemplates\Configuration\Variables;

class HeaderRenderer
{
    public static function columns(array $block, array $data, array $branding, int $index): array
    {
        $columns = $block['header_columns'];
        $total = array_sum(array_column($columns, 'width'));
        foreach ($columns as &$column) {
            $column['percent'] = 100 * $column['width'] / $total;
            $source = $column['logo_source'];
            $images = $branding['images'][$index] ?? [];
            $legacySource = str_replace('_custom', '', $source);
            $column['image'] = array_key_exists($source, $images) ? $images[$source] : ($images[$legacySource] ?? null);
            $column['fallback'] = match ($source) {
                'company', 'company_custom' => $branding['name'],
                'factory', 'factory_custom' => (string) data_get($data, 'equipment.brand', ''),
                default => '',
            };
            foreach ($column['lines'] as &$line) {
                $line['value'] = ($line['text'] ?? '').($line['mode'] === 'variable'
                    ? Variables::value($data, $line['variable'] ?? '') : '');
            }
        }

        return $columns;
    }
}
