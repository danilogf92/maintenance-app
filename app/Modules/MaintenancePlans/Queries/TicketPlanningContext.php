<?php
namespace App\Modules\MaintenancePlans\Queries;
use App\Modules\MaintenancePlans\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
class TicketPlanningContext
{
    public static function get(int $ticketId): ?array
    {
        if (!Permissions::allows()) { return null; }
        $ticket = VisibleRecords::query('tickets')->with('maintenanceVisit')->findOrFail($ticketId);
        if (!$visit = $ticket->maintenanceVisit) { return null; }
        $plan = PlanQuery::scoped()->find($visit->maintenance_plan_id);
        return $plan ? ['id' => $plan->id, 'name' => $plan->name, 'month' => \App\Modules\MaintenancePlans\Configuration\Periods::label($visit->scheduled_month)] : null;
    }
}
