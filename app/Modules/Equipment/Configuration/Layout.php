<?php

namespace App\Modules\Equipment\Configuration;

class Layout
{
    public static function definitions(): array
    {
        return ['spacing' => 'normal', 'padding' => 20, 'label_gap' => 6, 'section_gap' => 24,
            'columns' => 2, 'mobile_columns' => 1, 'control_height' => 40];
    }

    public static function styles(): string
    {
        return \App\Livewire\Shared\EditorLayout::styles(self::definitions());
    }
}
