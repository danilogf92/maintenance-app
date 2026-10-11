<?php

namespace App\Modules\Institutions\Configuration;

class Layout
{
    public static function definitions(): array
    {
        return ['spacing' => 'normal', 'padding' => 20, 'label_gap' => 6, 'section_gap' => 24,
            'columns' => 2, 'mobile_columns' => 1, 'control_height' => 40];
    }

    public static function styles(): string
    {
        $layout = self::definitions();
        $gap = ['compact' => 10, 'normal' => 16, 'wide' => 24][$layout['spacing']];

        return '--institution-gap:'.$gap.'px;--institution-padding:'.$layout['padding'].'px;'
            .'--institution-label-gap:'.$layout['label_gap'].'px;--institution-section-gap:'.$layout['section_gap'].'px;'
            .'--institution-columns:'.$layout['columns'].';--institution-mobile-columns:'.$layout['mobile_columns'].';'
            .'--institution-control-height:'.$layout['control_height'].'px;';
    }
}
