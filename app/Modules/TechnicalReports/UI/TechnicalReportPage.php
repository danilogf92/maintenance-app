<?php
namespace App\Modules\TechnicalReports\UI;

use App\Modules\Workflow\UI\TabbedRecordPage;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\TechnicalReports\Configuration\{Columns, TableColumns};
use App\Modules\TechnicalReports\Queries\ReportQuery;
use App\Modules\Workflow\Actions\SaveRecord;
use App\Modules\Activities\Queries\ActivityOptions;
use Illuminate\Database\Eloquent\Model;

class TechnicalReportPage extends TabbedRecordPage
{
    use ReportPdfActions;

    public string $service_id = '';
    public string $report_template_id = '';
    public string $title = 'Informe técnico de mantenimiento';
    public string $serviceSearch = '';

    protected function viewModule(): string { return 'technical_reports'; }
    protected function relatedModule(): string { return ''; }
    protected function relatedModules(): array { return []; }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { }
    protected function tableColumns(): array { return array_intersect_key(TableColumns::definitions(), $this->viewFields()); }
    protected function viewFields(): array
    {
        $columns = Columns::columns();
        if (!Permissions::allows('services')) { unset($columns['service']); }
        return $columns;
    }
    public function resetColumns(): void
    {
        $this->visibleColumns = array_values(array_intersect(['code', 'title', 'service', 'template', 'revision'], array_keys($this->viewFields())));
        $this->saveViewSettings();
    }
    public function mount(): void
    {
        parent::mount();
        if (request()->filled('new_for_service')) {
            abort_if(request()->filled('record'), 422); Permissions::authorize('technical_reports', 'manage');
            $id = filter_var(request('new_for_service'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($id, 404); VisibleRecords::query('services')->findOrFail($id);
            $this->create(); $this->service_id = (string)$id;
        }
    }
    protected function fillRecord(Model $record): void
    {
        parent::fillRecord($record); $this->serviceSearch = ''; $this->pdfNotice = '';
    }
    protected function resetRecord(): void
    {
        parent::resetRecord(); $this->serviceSearch = ''; $this->pdfNotice = '';
    }
    protected function persistRecord(): Model
    {
        Permissions::authorize('technical_reports', 'manage');
        if ($this->editingId) {
            $record = VisibleRecords::query('technical_reports')->findOrFail($this->editingId);
            $same = collect($this->recordData())->every(fn ($value, $key) => (string)$record->$key === (string)$value);
            if ($same && (int)$record->lock_version === $this->lockVersion) { return $record; }
        }
        return app(SaveRecord::class)->execute('technical_reports', [...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }
    public function render()
    {
        $items = app(ReportQuery::class)->records($this->search);
        $report = $this->formOpen && $this->editingId ? VisibleRecords::query('technical_reports')->with('lastGeneration')->findOrFail($this->editingId) : null;
        return view('technical-reports::page', array_merge(compact('items', 'report'), $this->operationViewsData(), $this->prepareTable($items), TechnicalReportEditor::configuration(), [
            'canManage' => Permissions::allows('technical_reports', 'manage'),
            'serviceOptions' => $this->formOpen ? ActivityOptions::services($this->serviceSearch, $this->service_id) : [],
            'reportContext' => $this->formOpen ? ActivityOptions::context($this->service_id) : [],
            'templateOptions' => $this->formOpen ? ReportQuery::templates() : [],
        ]))->layout('components.layouts.app');
    }
}
