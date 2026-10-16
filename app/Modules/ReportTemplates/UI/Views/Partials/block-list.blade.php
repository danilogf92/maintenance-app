<aside class="rt-panel rt-blocks" x-show="blocksOpen">
    <details open class="rt-fold">
    <summary>Bloques</summary>
    <div class="rt-fold-content">
    <ol>
    @foreach($definition['blocks'] as $index => $block)
        <li wire:key="block-{{ $index }}-{{ $block['type'] }}" class="{{ $selected === $index ? 'rt-selected' : '' }}">
            <button class="rt-block-select" type="button" wire:click="selectBlock({{ $index }})" @click="propertiesOpen = true; previewOpen = true" aria-pressed="{{ $selected === $index ? 'true' : 'false' }}">
                <span class="rt-number">{{ $index + 1 }}</span><span>{{ $block['title'] ?: 'Sin título' }} @if(!$block['enabled'])<small>Oculto</small>@endif</span>
            </button>
            <div class="rt-block-tools">
                <button type="button" wire:click="moveBlock({{ $index }}, -1)" @disabled($index === 0) aria-label="Subir bloque {{ $index + 1 }}">↑</button>
                <button type="button" wire:click="moveBlock({{ $index }}, 1)" @disabled($index === count($definition['blocks']) - 1) aria-label="Bajar bloque {{ $index + 1 }}">↓</button>
                <button type="button" wire:click="removeBlock({{ $index }})" wire:confirm="¿Quitar este bloque de la plantilla?" aria-label="Quitar bloque {{ $index + 1 }}">×</button>
            </div>
        </li>
    @endforeach
    </ol>
    <label for="rt-new-block">Nuevo bloque</label>
    <select id="rt-new-block" wire:model="newBlock">@foreach(\App\Modules\ReportTemplates\Configuration\Blocks::options() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
    <x-filament::button color="gray" wire:click="addBlock" icon="heroicon-o-plus">Agregar bloque</x-filament::button>
    </div></details>
</aside>
