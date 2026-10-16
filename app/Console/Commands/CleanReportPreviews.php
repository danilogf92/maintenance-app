<?php

namespace App\Console\Commands;

use App\Modules\Reports\Models\ReportGeneration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanReportPreviews extends Command
{
    protected $signature = 'reports:clean-previews';
    protected $description = 'Elimina PDF y datos temporales de pruebas expiradas';

    public function handle(): int
    {
        $count = 0;
        ReportGeneration::where('expires_at', '<', now())->where(function ($query) {
            $query->where('status', '!=', 'processing')->orWhere('updated_at', '<', now()->subMinutes(10));
        })->chunkById(100, function ($records) use (&$count) {
            foreach ($records as $record) {
                Storage::disk('local')->deleteDirectory($record->directory());
                $record->delete(); $count++;
            }
        });
        foreach (Storage::disk('local')->directories('report-previews') as $directory) {
            if (! ReportGeneration::whereKey(basename($directory))->exists()) {
                Storage::disk('local')->deleteDirectory($directory);
            }
        }
        $this->info("Pruebas temporales eliminadas: {$count}");

        return self::SUCCESS;
    }
}
