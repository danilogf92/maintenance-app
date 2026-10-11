<?php

namespace App\Console\Commands;

use App\Support\Demo\ResetDemoData;
use Illuminate\Console\Command;

class RefreshDemoData extends Command
{
    protected $signature = 'maintenance:demo-refresh {--count=500}';

    protected $description = 'Reemplaza datos locales de usuarios e instituciones, conservando SuperAdmins';

    public function handle(): int
    {
        $count = filter_var($this->option('count'), FILTER_VALIDATE_INT);
        if ($count === false || $count < 1 || $count > 500) {
            $this->error('count debe estar entre 1 y 500.');

            return self::FAILURE;
        }
        $result = app(ResetDemoData::class)->execute($count);
        $this->info('SuperAdmins conservados: '.$result['preserved_admins'].'. Instituciones y usuarios de prueba creados: '.$count.'.');

        return self::SUCCESS;
    }
}
