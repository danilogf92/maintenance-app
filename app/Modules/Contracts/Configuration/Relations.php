<?php

namespace App\Modules\Contracts\Configuration;

class Relations
{
    public static function definitions(): array
    {
        return ['contract_coverages' => ['allowed' => Permissions::allows(), 'component' => 'contract-coverages', 'key' => 'contractId']];
    }
}
