<?php

namespace App\Modules\Services\UI;

class RelatedActivities extends RelatedServiceRecords
{
    protected function module(): string { return 'activities'; }
    protected function fields(): array { return ['id', 'code', 'service_id', 'description', 'result', 'photos', 'created_at']; }
    protected function searchField(): string { return 'description'; }
    protected function viewName(): string { return 'services::Relations.activities'; }
}
