@if(!$report)<p class="record-editor-hint">Guarda el informe antes de solicitar su PDF.</p>
@else
 @php($pdf = $report->lastGeneration)
 @php($current = $pdf && (int)$pdf->report_revision === (int)$report->revision)
 @php($pending = $current && $pdf->expires_at->isFuture() && in_array($pdf->status, ['queued','processing']))
 <section class="record-editor-section report-pdf" @if($pending) wire:poll.3s @endif>
  <h3>PDF de {{ $report->code }} · R{{ $report->revision }}</h3>
  <p class="record-editor-hint">Se genera siempre por cola. Ábrelo en otra pestaña para evaluar el diseño o imprimirlo desde el visor PDF.</p>
  <button type="button" class="ops-button" wire:click="generatePdf({{ $editingId }}, '{{ $editorSession }}')" wire:loading.attr="disabled" @disabled($pending)>{{ $pending ? 'Generando PDF…' : 'Generar PDF' }}</button>
  <div class="report-pdf-status" role="status" aria-live="polite">
  @if(!$pdf)<p>Todavía no se ha solicitado un PDF.</p>
  @elseif(!$current)<p>El último PDF corresponde a R{{ $pdf->report_revision }}. Genera el de la revisión actual.</p>
  @elseif($pdf->expires_at->isPast())<p>El enlace temporal expiró. Genera otro PDF.</p>
  @elseif($pdf->status === 'ready')
   <a class="ops-button" href="{{ route('workflow.pdf', ['report' => $report->id, 'generation' => $pdf->id]) }}" target="_blank" rel="noopener"><x-ui.icon name="eye" />Abrir PDF · R{{ $report->revision }}</a>
  @elseif($pdf->status === 'failed')<p>No se pudo generar el PDF. Puedes volver a intentarlo. Si continúa, revisa el worker de la cola reports.</p>
  @else<p>{{ $pdf->status === 'queued' ? 'En cola, esperando al generador.' : 'Procesando el documento.' }}</p>
  @endif
  </div>
  @if($pdfNotice)<p role="status">{{ $pdfNotice }}</p>@endif
  <p class="record-editor-hint">El documento sigue siendo borrador. La emisión final, aprobación y firma están pendientes.</p>
 </section>
@endif
