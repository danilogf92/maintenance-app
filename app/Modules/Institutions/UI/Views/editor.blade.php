@include('institutions::editor-styles')
<aside wire:ignore.self x-show.important="$wire.formOpen" x-cloak
 class="ops-detail-panel institution-editor relative flex h-full min-h-0 shrink-0 flex-col border-l border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
 :style="panelStyle()" style="{{ $editorLayoutStyle }}"
 x-data="{activeTab:@js(array_key_first($editorTabs)),relations:@js(collect($editorTabs)->mapWithKeys(fn($tab,$key)=>[$key=>$tab['relation'] ?? null])->all()),
 init(){this.$watch('$wire.editorSession',()=>this.activeTab=@js(array_key_first($editorTabs)));},
 isRelationTab(){return !!this.relations[this.activeTab];},
 async selectTab(key){this.activeTab=key;if(this.relations[key])await this.$wire.loadEditorRelation(this.relations[key]);}
 }"
 @keydown.escape.window="if ($wire.formOpen && !document.querySelector('.institution-user-dialog[open]')) $wire.closeForm()">
 <x-record-editor.record-form novalidate :style="'min-height:0;'"
  x-bind:style="{height:isRelationTab()?'auto':'100%',flex:isRelationTab()?'0 0 auto':'1 1 0%'}">
  <header class="institution-editor-header">
   <x-record-editor.mobile-title label="Institución" :record-id="$editingId" />
   <x-record-editor.drawer-handle />
   <x-record-editor.record-save-status />
   <x-record-editor.drawer-close />
  </header>
  <nav class="institution-editor-tabs" role="tablist" aria-label="Información de la institución">
  @foreach($editorTabs as $key => $tab)
   @if(!isset($tab['relation']) || auth()->user()->can($editorRelations[$tab['relation']]['permission']))
   @php($hasErrors = collect($tab['sections'] ?? [])->pluck('fields')->flatten()->contains(fn($field) => $errors->has($field)))
   <button type="button" role="tab" id="institution-tab-{{ $key }}" aria-controls="institution-pane-{{ $key }}"
    :aria-selected="activeTab==='{{ $key }}'" :tabindex="activeTab==='{{ $key }}'?0:-1"
    :class="{'is-active':activeTab==='{{ $key }}'}" @click="selectTab('{{ $key }}')"
    @keydown.arrow-right.prevent="$el.nextElementSibling?.focus()" @keydown.arrow-left.prevent="$el.previousElementSibling?.focus()">
    {{ $tab['label'] }} @if($hasErrors)<span class="institution-tab-error" title="Campos pendientes">●</span>@endif
   </button>
   @endif
  @endforeach
  </nav>
  <div class="institution-editor-content" x-show="!isRelationTab()">
   <x-record-editor.validation-summary />
   @foreach($editorTabs as $key => $tab)
    @if(!isset($tab['relation']))
    <div id="institution-pane-{{ $key }}" role="tabpanel" aria-labelledby="institution-tab-{{ $key }}" x-show="activeTab==='{{ $key }}'">
     @foreach($tab['sections'] as $section)
     <section class="institution-section">
      @if($section['title'] ?? '')<h3>{{ $section['title'] }}</h3>@endif
      <div class="institution-field-grid" @if(isset($section['columns'])) style="--institution-columns:{{ (int)$section['columns'] }}" @endif>
      @foreach($section['fields'] as $field)
       @include('institutions::field', ['definition' => $editorFields[$field], 'width' => $section['widths'][$field] ?? 'auto'])
      @endforeach
      </div>
     </section>
     @endforeach
    </div>
    @endif
   @endforeach
  </div>
 </x-record-editor.record-form>
 <div class="institution-editor-relations" x-show="isRelationTab()">
 @foreach($editorTabs as $key => $tab)
  @if(isset($tab['relation']) && auth()->user()->can($editorRelations[$tab['relation']]['permission']))
  <div id="institution-pane-{{ $key }}" role="tabpanel" aria-labelledby="institution-tab-{{ $key }}" x-show="activeTab==='{{ $key }}'">
   @if(!$editingId)<p>Completa los datos obligatorios en General para guardar la institución y ver sus relaciones.</p>
   @elseif($formOpen && in_array($tab['relation'], $loadedEditorRelations, true))
    @if($tab['relation'] === 'users')
     @livewire('institution-related-users', ['institutionId' => $editingId], key('institution-users-'.$editingId))
    @endif
   @else<p>Abre esta pestaña para cargar sus registros.</p>@endif
  </div>
  @endif
 @endforeach
 </div>
</aside>
