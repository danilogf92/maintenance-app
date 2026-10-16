<?php

namespace App\Modules\ReportTemplates\Configuration;

class Blocks
{
    public static function options(): array
    {
        return ['header' => 'Encabezado', 'institution' => 'Cliente e institución',
            'equipment' => 'Datos del equipo', 'components' => 'Componentes',
            'activities' => 'Actividades y fotografías', 'parts' => 'Repuestos',
            'separator' => 'Título / separador', 'data_table' => 'Datos en columnas (cliente / servicio)',
            'recommendations' => 'Recomendaciones', 'text' => 'Texto libre', 'signatures' => 'Firmas', 'signature' => 'Firma individual'];
    }

    public static function make(string $type): array
    {
        $block = ['type' => $type, 'title' => $type === 'header' ? 'INFORME TÉCNICO' : (self::options()[$type] ?? 'Bloque'),
            'show_title' => true, 'show_table_header' => true, 'enabled' => true, 'hide_empty' => true, 'page_break' => false,
            'font_size' => null, 'spacing' => 6, 'columns' => 2, 'photo_columns' => 2, 'photo_height' => 35,
            'repeat_header' => true, 'fields' => self::fields($type),
            'table_columns' => ['description', 'result', 'photos'], 'text' => '',
            'company_logo' => null, 'factory_logo' => null];

        if ($type === 'separator') {
            $block = array_replace($block, ['title' => 'TÍTULO DE SECCIÓN', 'font_size' => 11,
                'separator_background' => '#00858b', 'separator_color' => '#ffffff', 'separator_alignment' => 'center', 'spacing' => 2]);
        }
        if ($type === 'signature') { $block['signature'] = Signature::defaults(); $block['show_title'] = false; }
        if ($type === 'data_table') { $block = array_replace($block, DataTable::defaults()); }

        return $type === 'header' ? array_replace($block, Header::defaults($block)) : $block;
    }

    public static function fields(string $type): array
    {
        return match ($type) {
            'header' => ['report.number', 'report.date', 'ticket.number', 'service.type'],
            'institution' => ['institution.name', 'institution.city', 'client.name', 'client.tax_id'],
            'equipment' => ['equipment.name', 'equipment.brand', 'equipment.model', 'equipment.serial', 'equipment.location', 'equipment.status'],
            default => [],
        };
    }

    public static function defaults(): array
    {
        return ['page' => Layout::defaults(), 'blocks' => array_map(self::make(...),
            ['header', 'institution', 'equipment', 'components', 'activities', 'parts', 'recommendations', 'signatures'])];
    }

    public static function tableOptions(): array
    {
        return ['description' => 'Descripción / actividad', 'result' => 'Resultado / estado', 'photos' => 'Fotografías'];
    }
}
