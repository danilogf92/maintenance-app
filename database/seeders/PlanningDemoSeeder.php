<?php
namespace Database\Seeders;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\MaintenancePlans\Models\MaintenancePlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PlanningDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('testing')) { $this->command?->warn('La carga de demostraciones está deshabilitada.'); return; }

        DB::transaction(function () {
            if (!$equipment = Equipment::where('code', 'DEMO-EQ-1')->first()) { return; }
            $plan = MaintenancePlan::firstOrCreate(['code' => 'DEMO-PM-1'], ['name' => 'DEMO · Preventivo trimestral',
                'equipment_id' => $equipment->id, 'technician_id' => $equipment->created_by, 'service_type' => 'Mantenimiento preventivo',
                'first_month' => today()->addMonth()->format('Y-m'), 'first_date' => today()->addMonth()->startOfMonth(), 'interval_months' => 3, 'interval_value' => 3, 'interval_unit' => 'months',
                'contract_reference' => 'DEMO · Referencia ficticia', 'notes' => 'Ejemplo de planificación. No representa un contrato real.', 'active' => true]);
            if (!$plan->wasRecentlyCreated) { return; }
            for ($i = 1; $i <= 4; $i++) {
                $plan->visits()->create(['sequence' => $i, 'scheduled_month' => $plan->first_date->copy()->addMonths(($i-1)*3)->format('Y-m'), 'scheduled_for' => $plan->first_date->copy()->addMonthsNoOverflow(($i-1)*3), 'technician_id' => $plan->technician_id]);
            }
            $plan->schedule_version = 1; $plan->save();
        });
    }
}
