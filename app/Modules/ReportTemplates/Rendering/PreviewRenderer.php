<?php

namespace App\Modules\ReportTemplates\Rendering;

/** Adds a trusted selection bridge only to the editor preview, never to PDFs. */
class PreviewRenderer
{
    public function execute(array $definition, array $data, int $selected, ?string $column, ?int $line, int $dataColumn = 0, ?int $dataRow = null): string
    {
        $html = app(TemplateRenderer::class)->execute($definition, $data);
        $bridge = view('report-templates::Partials.preview-bridge', compact('selected', 'column', 'line', 'dataColumn', 'dataRow'))->render();

        return str_replace('</body>', $bridge.'</body>', $html);
    }
}
