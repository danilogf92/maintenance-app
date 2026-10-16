<?php

namespace App\Modules\ReportTemplates\UI\Pages;

use App\Models\Institution;
use App\Modules\Reports\Actions\QueueReportPdf;
use App\Modules\Reports\Models\ReportGeneration;
use App\Modules\ReportTemplates\Actions\PublishReportTemplate;
use App\Modules\ReportTemplates\Actions\SaveReportTemplate;
use App\Modules\ReportTemplates\Configuration\Fields;
use App\Modules\ReportTemplates\Configuration\DataTable;
use App\Modules\ReportTemplates\Configuration\Header;
use App\Modules\ReportTemplates\Configuration\Permissions;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\ReportTemplates\Queries\TemplateSampleData;
use App\Modules\ReportTemplates\Rendering\PreviewRenderer;
use App\Modules\ReportTemplates\UI\Concerns\EditsBlocks;
use App\Modules\ReportTemplates\UI\Concerns\EditsDataItems;
use App\Modules\ReportTemplates\UI\Concerns\SelectsDataItems;
use App\Modules\ReportTemplates\UI\Concerns\SelectsPreviewElement;
use App\Modules\ReportTemplates\UI\ReportTemplateResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Livewire\Attributes\Locked;
use Illuminate\Validation\ValidationException;

class EditReportTemplate extends Page
{
    use EditsBlocks, SelectsPreviewElement, EditsDataItems, SelectsDataItems;

    protected static string $resource = ReportTemplateResource::class;
    protected string $view = 'report-templates::editor';
    protected static ?string $title = 'Editar plantilla';
    protected \Filament\Support\Enums\Width|string|null $maxContentWidth = \Filament\Support\Enums\Width::Full;
    #[Locked] public int $templateId;
    #[Locked] public int $lockVersion = 0;
    #[Locked] public ?string $generationId = null;
    public array $metadata = [];
    public array $definition = [];
    public array $blockData = [];
    public int $selected = 0;
    public string $newBlock = 'text';
    public int $sampleCount = 4;
    public ?int $institutionId = null;

    public function boot(): void { Permissions::authorize(); }

    public function mount(int|string $record): void
    {
        $template = ReportTemplate::findOrFail($record);
        $this->templateId = $template->id;
        $this->lockVersion = $template->lock_version;
        $this->metadata = $template->only(['name', 'description']);
        $this->definition = Header::upgrade(DataTable::upgrade($template->definition));
        $this->generationId = ReportGeneration::where('user_id', auth()->id())
            ->where('report_template_id', $template->id)->where('expires_at', '>', now())->latest()->value('id');
        $this->blockData = $this->definition['blocks'][0];
        $this->form->fill($this->blockData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(Fields::block($this->blockData['type'] ?? 'header'))->statePath('blockData');
    }

    public function save(): void
    {
        $this->syncBlock();
        $record = app(SaveReportTemplate::class)->execute($this->templateId, $this->lockVersion, $this->metadata, $this->definition);
        $this->lockVersion = $record->lock_version;
        Notification::make()->success()->title('Borrador guardado')->send();
    }

    public function publish(): void
    {
        $this->save();
        app(PublishReportTemplate::class)->execute($this->templateId);
        Notification::make()->success()->title('Nueva versión publicada')->send();
    }

    public function testPdf(): void
    {
        $this->syncBlock();
        $generation = app(QueueReportPdf::class)->execute($this->templateId, $this->definition, $this->sampleCount, $this->institutionId);
        $this->generationId = $generation->id;
        Notification::make()->success()->title('PDF enviado a la cola')->send();
    }

    public function getViewData(): array
    {
        $html = '';
        try {
            $data = app(TemplateSampleData::class)->execute(min(4, $this->sampleCount), $this->institutionId);
            $html = app(PreviewRenderer::class)->execute($this->definition, $data, $this->selected, $this->selectedColumn, $this->selectedLine, $this->dataColumnIndex, $this->dataRowIndex);
        } catch (ValidationException) {
            $html = '<p>Completa las propiedades para actualizar la vista previa.</p>';
        }
        $generation = $this->generationId ? ReportGeneration::where('user_id', auth()->id())->find($this->generationId) : null;

        return ['previewHtml' => $html, 'generation' => $generation,
            'institutions' => Institution::orderBy('name')->get(['id', 'name']),
            'templates' => ReportTemplate::orderBy('name')->get(['id', 'name'])];
    }
}
