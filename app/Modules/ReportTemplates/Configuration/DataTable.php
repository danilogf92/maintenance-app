<?php

namespace App\Modules\ReportTemplates\Configuration;

class DataTable
{
    public static function upgrade(array $definition): array
    {
        foreach ($definition['blocks'] ?? [] as $index => $block) {
            if (($block['type'] ?? '') === 'data_table') {
                $definition['blocks'][$index] = self::upgradeBlock($block);
            }
        }

        return $definition;
    }

    private static function upgradeBlock(array $block): array
    {
        $block = array_replace(self::defaults(), $block);
        if (! is_array($block['data_sections'] ?? null)) { return $block; }
        foreach ($block['data_sections'] as &$section) {
            if (! is_array($section)) { continue; }
            $section['item_alignment'] ??= 'legacy';
            if (! is_array($section['fields'] ?? null)) { continue; }
            foreach ($section['fields'] as &$field) {
                if (! is_array($field)) { continue; }
                $field['item_alignment'] ??= 'inherit';
                $field['item_indent'] ??= 0;
                if (($field['mode'] ?? '') === 'signature') {
                    $field['signature'] = array_replace(Signature::defaults(), array_filter($field['signature'] ?? [], fn ($value) => $value !== null));
                }
            }
            unset($field);
        }
        unset($section);

        return $block;
    }

    public static function defaults(): array
    {
        return ['show_title' => false, 'show_labels' => true, 'bordered' => false,
            'label_color' => '#00858b', 'value_color' => '#263440',
            'section_background' => '#00858b', 'section_color' => '#ffffff',
            'data_sections' => [
                ['title' => 'DATOS DEL CLIENTE', 'item_alignment' => 'left', 'show_title' => true, 'fields' => [
                    self::field('Cliente:', 'client.name'), self::field('RUC:', 'client.tax_id'),
                    self::field('Correo:', 'institution.contact_email'), self::field('Teléfono:', 'institution.contact_phone'),
                ]],
                ['title' => 'INFORMACIÓN DEL SERVICIO', 'item_alignment' => 'left', 'show_title' => true, 'fields' => [
                    self::field('Usuario:', 'institution.name'), self::field('Fecha:', 'report.date'),
                    self::field('Rubro:', 'service.type'), self::field('Responsable técnico:', 'technician.name'),
                ]],
            ]];
    }

    public static function signatureField(): array
    {
        return ['label' => 'Firma:', 'mode' => 'signature', 'variable' => 'technician.name', 'text' => '',
            'item_alignment' => 'inherit', 'item_indent' => 0, 'signature' => Signature::defaults()];
    }

    public static function field(string $label, string $variable): array
    {
        return ['label' => $label, 'mode' => 'variable', 'variable' => $variable, 'text' => '', 'item_alignment' => 'inherit', 'item_indent' => 0];
    }
}
