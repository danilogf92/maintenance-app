<?php

namespace App\Livewire\Shared;

class EditorLayout
{
    public static function styles(array $layout): string
    {
        $gap = ['compact' => 10, 'normal' => 16, 'wide' => 24][$layout['spacing']];

        return '--record-editor-gap:'.$gap.'px;--record-editor-padding:'.$layout['padding'].'px;'
            .'--record-editor-label-gap:'.$layout['label_gap'].'px;--record-editor-section-gap:'.$layout['section_gap'].'px;'
            .'--record-editor-columns:'.$layout['columns'].';--record-editor-mobile-columns:'.$layout['mobile_columns'].';'
            .'--record-editor-control-height:'.$layout['control_height'].'px;';
    }
}
