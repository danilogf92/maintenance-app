<nav class="record-editor-tabs" role="tablist" aria-label="Información del usuario">
 @foreach($editorTabs as $key => $tab)
  @php($hasErrors = collect($tab['sections'])->pluck('fields')->flatten()->contains(fn($field) => $errors->has($field) || $errors->has($field.'.*')))
  <button type="button" role="tab" id="user-tab-{{ $this->getId() }}-{{ $key }}" aria-controls="user-pane-{{ $this->getId() }}-{{ $key }}"
   data-record-relation="{{ $tab['relation'] ?? '' }}" @record-navigation-select.stop="activeTab='{{ $key }}'"
    :aria-selected="activeTab==='{{ $key }}'" :tabindex="activeTab==='{{ $key }}'?0:-1"
   :class="{'is-active':activeTab==='{{ $key }}'}" @click="activeTab='{{ $key }}'"
   @keydown.arrow-right.prevent="$el.nextElementSibling?.focus()" @keydown.arrow-left.prevent="$el.previousElementSibling?.focus()">
   <x-ui.icon :name="$tab['icon'] ?? 'info'" class="record-tab-icon" /><span>{{ $tab['label'] }}</span> @if($hasErrors)<span class="record-editor-tab-error" title="Campos pendientes">●</span>@endif
  </button>
 @endforeach
</nav>
