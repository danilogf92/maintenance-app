<style>
@page {
    size: {{ $page['paper'] }} {{ $page['orientation'] }};
    margin: {{ $page['margin'] }}mm;
    @if($repeatedHeader)
    margin-top: {{ $page['margin'] + $repeatedHeader['header_height'] }}mm;
    @top-center { content: element(reportheader); width: 100%; vertical-align: bottom; padding-bottom: 4mm; }
    @endif
    @top-left { content: element(reportbrand); color: #555; font-size: 8pt; }
    @bottom-left { content: element(reportfooter); color: #555; font-size: 8pt; }
    @bottom-right { content: "Página " counter(page) " de " counter(pages); color: #555; font-size: 8pt; }
}
* { box-sizing: border-box; }
body { margin: 0; font-family: {{ \App\Modules\ReportTemplates\Configuration\TextStyle::fonts()[$page['font_family'] ?? 'sans'] }}, sans-serif; font-size: {{ $page['font_size'] }}pt; color: #263440; }
h1 { font-size: 17pt; margin: 0 0 3mm; color: {{ $page['color'] }}; }
h2 { font-size: var(--report-font-size, 11pt); margin: 0 0 3mm; color: {{ $page['color'] }}; break-after: avoid; }
p { margin: 0 0 2mm; overflow-wrap: anywhere; }
.running-header { position: running(reportheader); }
.configured-header { width: 100%; border: 0; margin: 0; border-collapse: separate; border-spacing: 0; }
.configured-header td { background: transparent; vertical-align: middle; padding: 2mm; }
.header-logo { max-width: 100%; object-fit: contain; }
.header-line { margin-bottom: 1.5mm; line-height: 1.25; overflow-wrap: anywhere; }
.running-brand { position: running(reportbrand); }
.running-footer { position: running(reportfooter); }
.new-page { break-before: page; }
.branding { width: 100%; border: 0; margin-bottom: 4mm; }
.branding td { border: 0; background: transparent; vertical-align: middle; }
.branding img { max-width: 42mm; max-height: 16mm; }
.brand-name { font-weight: bold; font-size: 14pt; color: {{ $page['color'] }}; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th, td { border: 0.2mm solid #d6dde3; padding: 2mm; vertical-align: top; overflow-wrap: anywhere; }
th { background: #edf2f5; text-align: left; font-size: var(--report-font-size, 8pt); }
thead { display: table-header-group; }
tr { break-inside: avoid; }
.fields { margin-bottom: 3mm; }
.fields td { background: #fafbfc; }
.field-label { display: block; font-size: 7.5pt; color: #546271; margin-bottom: 1mm; }
.photos td { border: 0; padding: 1mm; }
.photo-item { break-inside: avoid; }
.photo-item img { display: block; width: 100%; object-fit: contain; }
.photo-item figcaption { font-size: var(--report-font-size, 7.5pt); padding-top: 1mm; overflow-wrap: anywhere; }
figure { margin: 0; }
.empty-photo { padding: 4mm 1mm; background: #f0f2f4; font-size: 8pt; }
.signatures { margin-top: 8mm; break-inside: avoid; }
.signatures td { border: 0; padding-top: 12mm; text-align: center; }
.signature-line { border-top: .2mm solid #7c8a94; padding-top: 2mm; }
li { margin-bottom: 2mm; }
.section-separator { padding: 1.2mm 2mm; margin: 0; border-radius: 1mm; break-after: avoid; }
.data-columns { border-collapse: separate; border-spacing: 1mm 0; margin: 0; width: 100%; }
.data-columns > thead > tr > th, .data-columns > tbody > tr > td { border: 0; padding: 0; background: transparent; }
.data-section-title { margin: 0 0 1mm; padding: 1mm 2mm; border-radius: 1mm; font-size: var(--report-font-size, 10pt); text-align: center; }
.data-values { table-layout: auto; }
.data-values td { border: 0; padding: 1mm; background: transparent; vertical-align: top; }
.data-values .data-label { width: 36%; text-align: right; font-weight: bold; }
.data-value { white-space: pre-wrap; }
.data-item { width: 100%; padding: 1mm; overflow-wrap: anywhere; line-height: 1.35; }
.data-item-label { font-weight: bold; margin-right: 1mm; }
.data-columns.with-borders .data-item { border: .2mm solid #d6dde3; }
.data-columns.with-borders .data-values td { border: .2mm solid #d6dde3; }

@media screen { body { padding: {{ $page['margin'] }}mm; background: white; } .running-brand, .running-footer { display: none; } .running-header { position: static; margin-bottom: 6mm; } }
</style>

<style>
.individual-signature { display: inline-block; max-width: 100%; vertical-align: middle; break-inside: avoid; text-align: center; white-space: normal; }
.signature-space { display: block; width: 100%; }
.signature-space img { display: block; max-width: 100%; max-height: 100%; margin: 0 auto; object-fit: contain; }
.signature-name, .signature-caption { display: block; margin-top: 1mm; overflow-wrap: anywhere; }
</style>
