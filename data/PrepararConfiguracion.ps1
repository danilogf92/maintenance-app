param([string]$Root = $PSScriptRoot)
$ErrorActionPreference = 'Stop'
foreach ($name in @('configuracion.ini', 'correspondencias.json')) {
    $target = Join-Path $Root $name
    if (-not (Test-Path -LiteralPath $target)) {
        $template = Join-Path $Root ($name + '.example')
        if (-not (Test-Path -LiteralPath $template -PathType Leaf)) { throw ('Falta la plantilla: ' + $name + '.example') }
        Copy-Item -LiteralPath $template -Destination $target
    }
}
