<?php

namespace App\Modules\ReportTemplates\Configuration;

class Layout
{
    public static function defaults(): array
    {
        return ['paper' => 'A4', 'orientation' => 'portrait', 'margin' => 15,
            'font_family' => 'sans', 'font_size' => 10, 'color' => '#086375', 'footer' => 'Informe técnico', 'repeat_brand' => true];
    }
}
