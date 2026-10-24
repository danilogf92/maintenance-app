param([string]$Config = (Join-Path $PSScriptRoot 'configuracion.ini'))
$ErrorActionPreference = 'Stop'
foreach ($helper in @('Configuration', 'Diagnostics', 'Discovery', 'CatalogRecords')) { . (Join-Path $PSScriptRoot ('scripts/' + $helper + '.ps1')) }
$script:Issues = New-Object 'Collections.Generic.List[object]'
$id = [Guid]::NewGuid().ToString('N'); $project = Split-Path $PSScriptRoot -Parent
$inbox = Join-Path $project 'storage/app/private/data-management/history-inbox'
$directory = Join-Path $inbox $id; $diagnostic = Join-Path $PSScriptRoot ('resultado/historiales-' + $id)
$definitions = [ordered]@{
    equipment_institution_histories = @{ table = 'QC'; name = 'Centro_Asociado' }
    equipment_client_histories = @{ table = 'RC'; name = 'Clientes_Asociados' }
}
$tables = [ordered]@{}; $lock = $null
try {
    New-Item -ItemType Directory -Path $directory -Force | Out-Null
    New-Item -ItemType Directory -Path $diagnostic -Force | Out-Null
    $lock = [IO.File]::Open((Join-Path $inbox 'preparar.lock'), [IO.FileMode]::OpenOrCreate, [IO.FileAccess]::ReadWrite, [IO.FileShare]::None)
    & (Join-Path $PSScriptRoot 'PrepararConfiguracion.ps1')
    $source = Get-SourceConfiguration $Config
    $mappings = Get-Correspondences (Join-Path $PSScriptRoot 'correspondencias.json')
    $directories = @(Find-SanitizedDirectories $source)
    foreach ($entity in $definitions.Keys) {
        $definition = $definitions[$entity]
        $candidates = @($directories | Where-Object {
            $key = Get-NormalizedName (Get-Item -LiteralPath $_).Parent.Name
            if ($mappings.ContainsKey($key)) { $mappings[$key] -eq $entity }
            else { $key -eq (Get-NormalizedName $definition.name) }
        })
        if ($candidates.Count -ne 1) { throw ('Se requiere una sola carpeta DataSanitized para ' + $definition.name + ' (' + $definition.table + ').') }
        $target = Join-Path $directory ($entity + '.jsonl')
        $count = Copy-CatalogRecords $candidates[0] $target $definition.table
        $tables[$entity] = [ordered]@{ source_table = $definition.table; source_name = (Get-Item -LiteralPath $candidates[0]).Parent.Name;
            records = $count; sha256 = (Get-FileHash -LiteralPath $target -Algorithm SHA256).Hash.ToLowerInvariant() }
        Write-Host ($definition.table + ': ' + $count + ' registros preparados.')
    }
    if (@($script:Issues | Where-Object { $_.severity -eq 'error' }).Count -gt 0) { throw 'Hay errores de lectura; revisa errors.json.' }
    Write-ReaderJson (Join-Path $directory 'package.json') ([ordered]@{ version = 1; id = $id; created_at = [DateTime]::UtcNow.ToString('o'); tables = $tables })
    Write-ReaderJson (Join-Path $diagnostic 'ack.json') ([ordered]@{ operation = 'preparacion_historiales'; status = 'preparado'; imported = $false; package_id = $id; tables = $tables })
    Write-ReaderJson (Join-Path $diagnostic 'errors.json') ([ordered]@{ issues = @($script:Issues.ToArray()) })
    Write-Host 'Preparado. En Admin > Gestion de datos > Carga de datos pulsa Cargar historiales y vincular equipos.'
} catch {
    if (Test-Path -LiteralPath $diagnostic) {
        Add-ReaderIssue 'error' 'history_preparation_failed' $Config $_.Exception.Message
        Write-ReaderJson (Join-Path $diagnostic 'errors.json') ([ordered]@{ issues = @($script:Issues.ToArray()) })
        Write-ReaderJson (Join-Path $diagnostic 'ack.json') ([ordered]@{ operation = 'preparacion_historiales'; status = 'fallido'; imported = $false; package_id = $id })
    }
    if (Test-Path -LiteralPath (Join-Path $directory 'package.json')) { Remove-Item -LiteralPath (Join-Path $directory 'package.json') -Force }
    throw
} finally { if ($lock) { $lock.Dispose() } }
