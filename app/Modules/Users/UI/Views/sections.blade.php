<div class="record-editor-content">
 <x-record-editor.validation-summary />
 @foreach($editorTabs as $key => $tab)
  <div id="user-pane-{{ $this->getId() }}-{{ $key }}" role="tabpanel" aria-labelledby="user-tab-{{ $this->getId() }}-{{ $key }}" x-show="activeTab==='{{ $key }}'">
   @foreach($tab['sections'] as $section)
    <section class="record-editor-section">
     @if($section['title'] ?? '')<h3>{{ $section['title'] }}</h3>@endif
     <div class="record-editor-field-grid" @if(isset($section['columns'])) style="--record-editor-columns:{{ (int)$section['columns'] }}" @endif>
      @foreach($section['fields'] as $field)
       @include('users::field', ['definition' => $editorFields[$field], 'width' => $section['widths'][$field] ?? 'auto'])
      @endforeach
     </div>
    </section>
   @endforeach
  </div>
 @endforeach
</div>
