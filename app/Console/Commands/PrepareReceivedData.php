<?php
namespace App\Console\Commands;
use App\Modules\DataReview\Actions\RemoveDemonstrationData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class PrepareReceivedData extends Command
{
    protected $signature='data:prepare';
    protected $description='Elimina demostraciones identificadas y conserva datos recibidos y cuentas reales.';
    public function handle(): int
    {
        $result=app(RemoveDemonstrationData::class)->execute();
        Storage::disk('local')->put('data-review/cleanup.json',json_encode($result,JSON_PRETTY_PRINT));
        $this->info('Demostraciones eliminadas: '.array_sum($result['removed']).'. Registros protegidos: '.array_sum(array_map('count',$result['protected'])).'.');
        $this->info('Consulta Datos por completar para revisar archivos y relaciones pendientes.');
        return self::SUCCESS;
    }
}
