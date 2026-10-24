<?php

namespace App\Modules\Contracts\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'file-text', 'sections' => [
            ['title' => 'Contrato', 'fields' => ['number', 'name', 'client_id', 'active', 'notes'], 'widths' => ['name' => 'full', 'notes' => 'full']]]],
            'equipment' => ['label' => 'Equipos y cumplimiento', 'icon' => 'cpu', 'relation' => 'contract_coverages']];
    }
}
