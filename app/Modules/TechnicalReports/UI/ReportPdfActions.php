<?php
namespace App\Modules\TechnicalReports\UI;

use App\Modules\TechnicalReports\Actions\QueueTechnicalPdf;
use App\Modules\Workflow\Actions\SaveRecord;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\Attributes\Locked;

trait ReportPdfActions
{
    #[Locked] public string $pdfNotice = '';

    protected function ownsPdfAction(int $id, string $session): bool
    {
        return $this->formOpen && $this->editingId === $id && $session !== '' && $session === $this->editorSession;
    }

    public function refreshReportData(int $id, string $session): void
    {
        Permissions::authorize('technical_reports', 'manage');
        if (!$this->ownsPdfAction($id, $session)) { return; }
        $record = app(SaveRecord::class)->execute('technical_reports', [...$this->recordData(), 'lock_version' => $this->lockVersion], $id);
        $this->fillRecord($record); $this->recordDirty = false;
        $this->pdfNotice = 'Nueva revisión creada con los datos actuales del servicio y la plantilla.';
    }

    public function generatePdf(int $id, string $session): void
    {
        if (!$this->ownsPdfAction($id, $session) || !$this->finishCurrentRecord()) { return; }
        $report = VisibleRecords::query('technical_reports')->with('lastGeneration')->findOrFail($id);
        $last = $report->lastGeneration;
        if ($last && (int)$last->report_revision === (int)$report->revision && $last->expires_at->isFuture() && in_array($last->status, ['queued', 'processing'])) {
            $this->pdfNotice = 'Esta revisión ya tiene un PDF en proceso.'; return;
        }
        app(QueueTechnicalPdf::class)->execute($id);
        $this->pdfNotice = 'PDF enviado a la cola. Puedes continuar trabajando.';
    }
}
