@if(!$record)<p class="record-editor-hint">Guarda el fabricante para añadir su logo.</p>
@else
 <section class="record-editor-section">
  <h3>Logo del fabricante</h3>
  @if($record->logo_path)<img class="catalog-logo" src="{{ Storage::disk('public')->url($record->logo_path) }}" alt="Logo de {{ $record->name }}">
  @else<p class="record-editor-hint">Sin logo. El informe mostrará el nombre disponible.</p>@endif
  @if($canManage)
   <label class="catalog-upload">Añadir o reemplazar logo
    <span class="catalog-button" style="display:inline-block;margin:8px 0">Seleccionar logo</span>
    <input class="sr-only" type="file" aria-label="Añadir logo" accept="image/png,image/jpeg,image/webp" @input.stop @change.stop="
     const file=$event.target.files[0],id=$wire.editingId,session=$wire.editorSession;
     if(file){$wire.upload('logoUpload',file,()=>{$wire.attachLogo(id,session)},()=>{});} $event.target.value='';">
   </label>
   @if($record->logo_path)<button type="button" class="catalog-button" wire:click="requestRemoveLogo({{ $editingId }}, '{{ $editorSession }}')">Retirar logo</button>@endif
  @endif
  @error('logoUpload')<p role="alert">{{ $message }}</p>@enderror
  @error('logo')<p role="alert">{{ $message }}</p>@enderror
  @if($logoNotice)<p role="status">{{ $logoNotice }}</p>@endif
  <p class="record-editor-hint">PNG, JPEG o WebP, hasta 1 MiB. Se incorpora automáticamente al crear o actualizar la revisión del informe de un equipo vinculado.</p>
 </section>
@endif
