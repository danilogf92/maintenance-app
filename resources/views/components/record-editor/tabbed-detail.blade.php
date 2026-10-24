<x-record-editor.tabbed-editor-styles />
<aside wire:ignore.self x-show.important="$wire.formOpen" x-cloak
 class="ops-detail-panel {{ $editorPrefix }}-editor relative flex h-full min-h-0 shrink-0 flex-col border-l border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
 :style="panelStyle()" style="{{ $editorLayoutStyle }}"
 x-data="{activeTab:@js(array_key_first($editorTabs)),relations:@js(collect($editorTabs)->mapWithKeys(fn($tab,$key)=>[$key=>$tab['relation'] ?? null])->all()),
 init(){this.$watch('$wire.editorSession',()=>this.activeTab=@js(array_key_first($editorTabs)));},
 isRelationTab(){return !!this.relations[this.activeTab];},
 async selectTab(key){this.activeTab=key;if(this.relations[key])await this.$wire.loadEditorRelation(this.relations[key]);}
 }"
 @keydown.escape.window="if ($wire.formOpen) $wire.closeForm()">
 <x-record-editor.record-form novalidate :style="'min-height:0;'"
  x-bind:style="{height:isRelationTab()?'auto':'100%',flex:isRelationTab()?'0 0 auto':'1 1 0%'}">
  <header class="record-editor-header">
   <x-record-editor.mobile-title :label="$editorLabel" :record-id="$editingId" />
   <x-record-editor.drawer-handle />
   @if(!$canManage)<span class="record-editor-hint">Solo lectura</span>@endif
   <x-record-editor.record-save-status />
   <x-record-editor.drawer-close />
  </header>
  <nav class="record-editor-tabs" role="tablist" aria-label="{{ $editorAriaLabel }}">
  @foreach($editorTabs as $key => $tab)
   @if(!isset($tab['relation']) || ($editorRelations[$tab['relation']]['allowed'] ?? $canViewRelation))
   @php($hasErrors = collect($tab['sections'] ?? [])->pluck('fields')->flatten()->contains(fn($field) => $errors->has($field)))
   <button type="button" role="tab" id="{{ $editorPrefix }}-tab-{{ $key }}" aria-controls="{{ $editorPrefix }}-pane-{{ $key }}"
    data-record-relation="{{ $tab['relation'] ?? '' }}" @record-navigation-select.stop="activeTab='{{ $key }}'"
    :aria-selected="activeTab==='{{ $key }}'" :tabindex="activeTab==='{{ $key }}'?0:-1"
    :class="{'is-active':activeTab==='{{ $key }}'}" @click="selectTab('{{ $key }}')"
    @keydown.arrow-right.prevent="$el.nextElementSibling?.focus()" @keydown.arrow-left.prevent="$el.previousElementSibling?.focus()">
    <x-ui.icon :name="$tab['icon'] ?? 'info'" class="record-tab-icon" /><span>{{ $tab['label'] }}</span> @if($hasErrors)<span class="record-editor-tab-error" title="Campos pendientes">●</span>@endif
   </button>
   @endif
  @endforeach
  </nav>
  <div class="record-editor-content" x-show="!isRelationTab()">
   <x-record-editor.validation-summary />
   @error('lock_version')<button type="button" wire:click="cancel" class="record-editor-hint">Descartar cambios y cerrar</button>@enderror
   @if($code)<p class="record-editor-hint" style="margin-bottom:16px">Código: {{ $code }}</p>@endif
   @if(!$editingId && !empty($editorHint))<p class="record-editor-hint" style="margin-bottom:16px">{{ $editorHint }}</p>@endif
   @foreach($editorTabs as $key => $tab)
    @if(!isset($tab['relation']))
    <div id="{{ $editorPrefix }}-pane-{{ $key }}" role="tabpanel" aria-labelledby="{{ $editorPrefix }}-tab-{{ $key }}" x-show="activeTab==='{{ $key }}'">
     @foreach($tab['sections'] as $section)
     <section class="record-editor-section">
      @if($section['title'] ?? '')<h3>{{ $section['title'] }}</h3>@endif
      <div class="record-editor-field-grid" @if(isset($section['columns'])) style="--record-editor-columns:{{ (int)$section['columns'] }}" @endif>
      @foreach($section['fields'] as $field)
       @include($editorFieldsView, ['definition' => $editorFields[$field], 'width' => $section['widths'][$field] ?? 'auto'])
      @endforeach
      </div>
     </section>
     @endforeach
     @if(isset($tab['view']))@include($tab['view'])@endif
    </div>
    @endif
   @endforeach
  </div>
 </x-record-editor.record-form>
 <div style="min-height:0;flex:1;overflow:auto;padding:var(--record-editor-padding)" class="{{ $editorPrefix }}-editor-relations" x-show="isRelationTab()">
 @foreach($editorTabs as $key => $tab)
  @if(isset($tab['relation']) && ($editorRelations[$tab['relation']]['allowed'] ?? $canViewRelation))
  <div id="{{ $editorPrefix }}-pane-{{ $key }}" role="tabpanel" aria-labelledby="{{ $editorPrefix }}-tab-{{ $key }}" x-show="activeTab==='{{ $key }}'">
   @if(!$editingId)<p>{{ $editorDraftMessage }}</p>
   @elseif($formOpen && ($loadedEditorRelations[$tab['relation']] ?? false))
    @livewire($editorRelations[$tab['relation']]['component'] ?? $editorRelationComponent, [($editorRelations[$tab['relation']]['key'] ?? $editorRelationKey) => $editingId], key($editorPrefix.'-relation-'.$tab['relation'].'-'.$editingId))
   @else<p>Abre esta pestaña para cargar sus registros.</p>@endif
  </div>
  @endif
 @endforeach
 </div>
</aside>
