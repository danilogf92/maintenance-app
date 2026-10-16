<?php

namespace App\Modules\ReportTemplates\Rendering;

use App\Models\BrandSetting;
use App\Modules\ReportTemplates\Configuration\Rules;

class TemplateRenderer
{
    public function execute(array $definition, array $data, ?array $branding = null): string
    {
        $definition = Rules::validate($definition);
        $branding ??= $this->captureBranding($definition, $data);
        $blocks = array_map(function ($block, $index) use ($branding, $data) {
            $block['preview_index'] = $index;
            $block['company_image'] = $branding['images'][$index]['company'] ?? null;
            $block['factory_image'] = $branding['images'][$index]['factory'] ?? null;
            if ($block['type'] === 'header') {
                $block['rendered_columns'] = HeaderRenderer::columns($block, $data, $branding, $index);
            }

            return $block;
        }, $definition['blocks'], array_keys($definition['blocks']));

        return view('report-print::document', ['page' => $definition['page'], 'blocks' => $blocks,
            'data' => $data, 'brandName' => $branding['name'],
            'repeatedHeader' => collect($blocks)->first(fn ($block) => $block['type'] === 'header' && $block['enabled'] && ($block['header_repeat'] ?? false))])->render();
    }

    public function captureBranding(array $definition, array $data = []): array
    {
        $brand = BrandSetting::current();
        $images = array_map(fn ($block) => $block['type'] === 'header' ? [
            'company' => ImageData::fromPublicPath($brand?->logo_path),
            'factory' => ImageData::fromPublicPath(data_get($data, 'equipment.factory_logo')),
            'company_custom' => ImageData::fromPublicPath($block['company_logo']),
            'factory_custom' => ImageData::fromPublicPath($block['factory_logo']),
        ] : [], $definition['blocks']);

        return ['name' => $brand?->name ?? config('app.name'), 'images' => $images];
    }
}
