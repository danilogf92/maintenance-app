@if($errors->any())
<div role="alert" data-editor-validation style="padding:12px 14px;border:1px solid #fecaca;border-radius:9px;background:#fff1f2;color:#9f1239;font-size:13px;line-height:1.5;">
    <strong>Revisa estos campos para guardar automáticamente:</strong>
    <ul style="margin:6px 0 0;padding-left:18px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
