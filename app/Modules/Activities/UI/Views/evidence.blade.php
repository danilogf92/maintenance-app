<div x-data="{
 busy:false,progress:0,message:'',
 init(){this.$watch('$wire.editorSession',()=>{this.message='';this.progress=0;});},
 upload(event){
  const files=Array.from(event.target.files);event.target.value='';
  if(!files.length||this.busy)return;
  if(files.some(file=>file.size>1024*1024)){this.message='Cada fotografía debe pesar como máximo 1 MiB.';return;}
  if(files.length+this.$wire.photos.length>4){this.message='Máximo cuatro fotografías por actividad.';return;}
  const id=Number(this.$wire.editingId),session=this.$wire.editorSession;
  this.busy=true;this.progress=0;this.message='';
  this.$wire.uploadMultiple('uploads',files,async()=>{
   try{
    const result=await this.$wire.attachPhotos(id,session);
    this.message=result==='stale'?'La ficha cambió. Selecciona las fotografías otra vez.':'';
   }catch(error){this.message='No se pudieron guardar las fotografías. Revisa el formato, tamaño y los campos pendientes.';}
   finally{this.busy=false;}
  },()=>{this.busy=false;this.message='La carga falló. Puedes seleccionar las fotografías otra vez.';},
  event=>{this.progress=event.detail.progress;});
 }
}">
 <p class="evidence-hint" style="margin-bottom:14px">Hasta 4 fotografías PNG, JPEG o WebP de 1 MiB cada una. Solo los usuarios autorizados pueden consultarlas.</p>
 @if(!$editingId)
  <p class="evidence-hint">Completa el servicio y la actividad para guardar el registro antes de añadir fotografías.</p>
 @elseif($canManage)
  <div class="evidence-upload">
   <label for="activity-photo-upload">Añadir fotografías</label>
   <input id="activity-photo-upload" type="file" multiple accept="image/png,image/jpeg,image/webp"
    @input.stop @change.stop="upload($event)" :disabled="busy || $wire.photos.length>=4">
   <p class="evidence-hint" x-show="busy" x-text="'Cargando fotografías… '+progress+' %'" aria-live="polite"></p>
  </div>
 @endif
 <p class="evidence-status" x-show="message" x-text="message" role="status"></p>
 @if($photoNotice)<p class="evidence-status" role="status">{{ $photoNotice }}</p>@endif
 @error('uploads')<p role="alert" class="evidence-status">{{ $message }}</p>@enderror
 @error('uploads.*')<p role="alert" class="evidence-status">{{ $message }}</p>@enderror
 <div class="evidence-grid">
 @foreach(($editingId ? $photos : []) as $index => $path)
  <figure class="evidence-card" wire:key="evidence-{{ $editingId }}-{{ md5($path) }}">
   <a class="evidence-image" href="{{ route('workflow.photo', [$editingId, $index]) }}?v={{ $lockVersion }}" target="_blank" rel="noopener" aria-label="Ampliar fotografía {{ $index + 1 }}">
    <img src="{{ route('workflow.photo', [$editingId, $index]) }}?v={{ $lockVersion }}" alt="Evidencia de la actividad, fotografía {{ $index + 1 }}" loading="lazy">
   </a>
   <figcaption><span>Fotografía {{ $index + 1 }}</span>
    @if($canManage)<div class="evidence-actions">
     <button type="button" aria-label="Mover fotografía {{ $index + 1 }} antes" wire:click="movePhoto({{ $index }}, -1, {{ $editingId }}, '{{ $editorSession }}')" wire:loading.attr="disabled" :disabled="busy || @js($index === 0)"><x-ui.icon name="arrow-up" /></button>
     <button type="button" aria-label="Mover fotografía {{ $index + 1 }} después" wire:click="movePhoto({{ $index }}, 1, {{ $editingId }}, '{{ $editorSession }}')" wire:loading.attr="disabled" :disabled="busy || @js($index === count($photos) - 1)"><x-ui.icon name="arrow-down" /></button>
     <button type="button" aria-label="Retirar fotografía {{ $index + 1 }}" wire:click="requestRemovePhoto({{ $index }}, {{ $editingId }}, '{{ $editorSession }}')" wire:loading.attr="disabled" :disabled="busy"><x-ui.icon name="trash" /></button>
    </div>@endif
   </figcaption>
  </figure>
 @endforeach
 </div>
 @if($editingId && !$photos)<p class="evidence-hint">Esta actividad todavía no tiene fotografías.</p>@endif
</div>
