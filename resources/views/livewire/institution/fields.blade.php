{{-- Fields and their placement are now configured in Modules/Institutions/Configuration. --}}
@foreach($editorFields as $field => $definition)
@include('institutions::field', ['width' => 'auto'])
@endforeach
