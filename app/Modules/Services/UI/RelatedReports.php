<?php

namespace App\Modules\Services\UI;

class RelatedReports extends RelatedServiceRecords
{
    protected function module(): string { return 'technical_reports'; }
    protected function fields(): array { return ['id', 'code', 'service_id', 'title', 'revision', 'created_at']; }
    protected function searchField(): string { return 'title'; }
    protected function viewName(): string { return 'services::Relations.reports'; }
}
