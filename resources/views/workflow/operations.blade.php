<section class="workflow-page" @if($module === 'technical_reports') wire:poll.5s @endif>
    <link rel="stylesheet" href="{{ asset('css/pages/workflow.css') }}?v=11.39.1">
    <header class="workflow-toolbar"><div><h1><x-ui.icon :name="\App\Modules\Workflow\Configuration\Modules::get($module)['ui_icon']" />{{ $label }}</h1><p>Operaciones · {{ $records->total() }} registros</p></div>
        <input aria-label="Buscar registros" wire:model.live.debounce.350ms="search" placeholder="Buscar por código o descripción">
        @if($canManage)<button type="button" wire:click="create"><x-ui.icon name="plus" />Nuevo registro</button>@endif
    </header>
    @if($notice)<p class="workflow-notice" role="status">{{ $notice }}</p>@endif
    <div class="workflow-content">
        <div class="workflow-list"><table><thead><tr>@foreach($columns as $key => $title)<th>{{ $title }}</th>@endforeach<th>Acciones</th></tr></thead>
            <tbody>@forelse($records as $record)<tr wire:key="row-{{ $record->id }}" @class(['selected' => $editingId === $record->id])>
                @foreach($columns as $key => $title)<td>
                    @if($key === 'active'){{ $record->active ? 'Sí' : 'No' }}
                    @elseif($key === 'status'){{ \App\Modules\Workflow\Configuration\EditorFields::options('status', $module)[$record->status] ?? $record->status }}
                    @else{{ \Illuminate\Support\Str::limit((string) data_get($record, $key), 100) }}@endif
                </td>@endforeach
                <td><button type="button" wire:click="edit({{ $record->id }})"><x-ui.icon :name="$canManage ? 'pencil' : 'eye'" />{{ $canManage ? 'Editar' : 'Ver' }}</button>
                    @if($module === 'technical_reports')
                        <button type="button" wire:click="generatePdf({{ $record->id }})" wire:loading.attr="disabled"><x-ui.icon name="printer" />Generar PDF</button>
                        @php($pdf = $record->lastGeneration)
                        @if($pdf && $pdf->expires_at->isFuture() && (int)$pdf->report_revision === (int)$record->revision)
                            @if($pdf->status === 'ready')<a href="{{ route('workflow.pdf', [$record->id, $pdf->id]) }}" target="_blank" rel="noopener"><x-ui.icon name="external-link" />Abrir PDF ↗</a>
                            @else<span>{{ ['queued' => 'En cola', 'processing' => 'Procesando', 'failed' => 'Falló: vuelve a generar'][$pdf->status] ?? $pdf->status }}</span>@endif
                        @endif
                    @endif
                </td></tr>@empty<tr><td colspan="{{ count($columns)+1 }}">Todavía no hay registros.</td></tr>@endforelse</tbody></table>
            <div class="workflow-pagination">{{ $records->links() }}</div>
        </div>
        @if($formOpen)<aside class="workflow-editor" aria-label="Ficha del registro">
            <form wire:submit="save"><header><h2>{{ $editingId ? ($canManage ? 'Editar registro' : 'Consultar registro') : 'Nuevo registro' }}</h2>
                <button type="button" wire:click="$set('formOpen', false)"><x-ui.icon name="x" />Cerrar</button></header>
                @error('form.lock_version')<p role="alert">{{ $message }}</p>@enderror
                @foreach($fields as $key => $definition)
                    @php($type = $definition[1] ?? 'text')
                    @php($options = \App\Modules\Workflow\Configuration\EditorFields::options($type, $module))
                    <label for="field-{{ $key }}">{{ $definition[0] }}</label>
                    @if($type === 'textarea')<textarea id="field-{{ $key }}" wire:model="form.{{ $key }}" rows="3" @disabled(!$canManage)></textarea>
                    @elseif(in_array($type, ['status','priority','clients','equipment','tickets','services','institutions','templates','technicians']))
                        <select id="field-{{ $key }}" wire:model="form.{{ $key }}" @disabled(!$canManage)><option value="">Seleccionar…</option>@foreach($options as $id => $text)<option value="{{ $id }}">{{ $text }}</option>@endforeach</select>
                    @else<input id="field-{{ $key }}" type="{{ $type }}" wire:model="form.{{ $key }}" @disabled(!$canManage)>@endif
                    @error('form.'.$key)<p class="workflow-error" role="alert">{{ $message }}</p>@enderror
                @endforeach
                @if($module === 'activities')
                    <p>Fotografías privadas · hasta 4 imágenes de 1 MiB</p>
                    @foreach($form['photos'] ?? [] as $index => $photo)
                        <div>@if($editingId)<a href="{{ route('workflow.photo', [$editingId, $index]) }}" target="_blank" rel="noopener">Ver fotografía {{ $index + 1 }} ↗</a>@endif</div>
                    @endforeach
                    @if($canManage)<input type="file" wire:model="uploads" multiple accept="image/png,image/jpeg,image/webp" aria-label="Fotografías de evidencia">@endif
                    @error('uploads')<p role="alert">{{ $message }}</p>@enderror
                    @error('uploads.*')<p role="alert">{{ $message }}</p>@enderror
                @endif
                @if($module === 'technical_reports')<p>Guardar captura los datos del servicio en una nueva revisión del borrador.</p>@endif
                @if($canManage)<button type="submit" wire:loading.attr="disabled"><x-ui.icon name="check" />Guardar</button>@endif
            </form>
        </aside>@endif
    </div>
</section>
