<label class="record-editor-field {{ $width === 'full' ? 'record-editor-field-full' : '' }}"><span>{{ $definition['label'] }}</span>
<input type="text" id="protocol-field-{{ $field }}" value="{{ $this->$field }}" readonly aria-readonly="true"></label>
