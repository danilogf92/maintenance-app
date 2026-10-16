<?php

namespace App\Modules\ReportTemplates\Configuration;

class Header
{
    public static function upgrade(array $definition): array
    {
        foreach ($definition['blocks'] ?? [] as $index => $block) {
            if (($block['type'] ?? '') !== 'header') { continue; }
            $definition['blocks'][$index] = array_replace(self::defaults($block), $block);
        }

        return $definition;
    }

    public static function defaults(array $block): array
    {
        $lines = [self::line($block['title'] ?? 'REPORTE DE SERVICIO TÉCNICO', null, '#00858b', 16)];
        foreach ($block['fields'] ?? Blocks::fields('header') as $key) {
            $label = Variables::options()[$key] ?? $key;
            $label = str_replace(' → ', ' ', $label);
            $lines[] = self::line($label.': ', $key, $key === 'report.number' ? '#ed5353' : '#00858b', 10);
        }

        return ['header_repeat' => false, 'header_height' => 40, 'header_columns' => [
            'left' => self::column(28, empty($block['company_logo']) ? 'company' : 'company_custom'),
            'center' => array_replace(self::column(47, 'none', 'left'), ['lines' => $lines]),
            'right' => self::column(25, empty($block['factory_logo']) ? 'factory' : 'factory_custom'),
        ]];
    }

    private static function column(int $width, string $source, string $alignment = 'center'): array
    {
        return ['width' => $width, 'logo_source' => $source, 'logo_height' => 22,
            'alignment' => $alignment, 'border' => false, 'border_color' => '#d6dde3', 'lines' => []];
    }

    public static function line(string $text = '', ?string $variable = null, string $color = '#00858b', int $size = 11): array
    {
        return ['mode' => $variable ? 'variable' : 'manual', 'text' => $text,
            'variable' => $variable ?? 'report.number', 'color' => $color, 'size' => $size, 'bold' => true];
    }
}
