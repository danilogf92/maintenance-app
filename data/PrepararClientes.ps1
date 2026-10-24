param([string]$Config = (Join-Path $PSScriptRoot 'configuracion.ini'))
$ErrorActionPreference = 'Stop'
foreach ($helper in @('Configuration', 'Diagnostics', 'Discovery', 'CatalogRecords')) { . (Join-Path $PSScriptRoot ('scripts/' + $helper + '.ps1')) }
$script:Issues = New-Object 'Collections.Generic.List[object]'
$packageId = [Guid]::NewGuid().ToString('N')
$projectRoot = Split-Path $PSScriptRoot -Parent
$inbox = Join-Path $projectRoot 'storage/app/private/data-management/inbox'
$directory = Join-Path $inbox $packageId
$diagnostic = Join-Path $PSScriptRoot ('resultado/preparacion-' + $packageId)
$tables = [ordered]@{}
$lock = $null

try {
    New-Item -ItemType Directory -Path $inbox -Force | Out-Null
    New-Item -ItemType Directory -Path $directory -Force | Out-Null
    New-Item -ItemType Directory -Path $diagnostic -Force | Out-Null
    $lock = [IO.File]::Open((Join-Path $inbox 'preparar.lock'), [IO.FileMode]::OpenOrCreate, [IO.FileAccess]::ReadWrite, [IO.FileShare]::None)
    & (Join-Path $PSScriptRoot 'PrepararConfiguracion.ps1')
    $source = Get-SourceConfiguration $Config
    $mappings = Get-Correspondences (Join-Path $PSScriptRoot 'correspondencias.json')
    $directories = @(Find-SanitizedDirectories $source)
    foreach ($entity in @('clients', 'institutions')) {
        $candidates = @($directories | Where-Object {
            $name = (Get-Item -LiteralPath $_).Parent.Name
            $key = Get-NormalizedName $name
            $mappings.ContainsKey($key) -and $mappings[$key] -eq $entity
        })
        if ($candidates.Count -ne 1) { throw ('Debe existir una sola carpeta DataSanitized con correspondencia a ' + $entity + '. Revisa data/correspondencias.json.') }
        $expected = 'A'; if ($entity -eq 'institutions') { $expected = 'B' }
        $target = Join-Path $directory ($entity + '.jsonl')
        $count = Copy-CatalogRecords $candidates[0] $target $expected
        $tables[$entity] = [ordered]@{ source_table = $expected; source_name = (Get-Item -LiteralPath $candidates[0]).Parent.Name;
            records = $count; sha256 = (Get-FileHash -LiteralPath $target -Algorithm SHA256).Hash.ToLowerInvariant() }
        Write-Host ($entity + ': ' + $count + ' registros preparados.')
    }
    if (@($script:Issues | Where-Object { $_.severity -eq 'error' }).Count -gt 0) { throw 'No se pudo revisar todo el origen. Consulta errors.json.' }
    Write-ReaderJson (Join-Path $directory 'package.json') ([ordered]@{ version = 1; id = $packageId; created_at = [DateTime]::UtcNow.ToString('o'); tables = $tables })
    Write-ReaderJson (Join-Path $diagnostic 'ack.json') ([ordered]@{ operation = 'preparacion_clientes_centros'; status = 'preparado'; imported = $false; package_id = $packageId; tables = $tables })
    Write-ReaderJson (Join-Path $diagnostic 'errors.json') ([ordered]@{ issues = @($script:Issues.ToArray()) })
    Write-Host 'Preparado. En Admin > Gestion de datos > Carga de datos pulsa Cargar clientes y centros.'
    Write-Host 'No se han modificado registros de la aplicacion ni archivos del origen.'
} catch {
    if (Test-Path -LiteralPath $diagnostic) {
        Add-ReaderIssue 'error' 'catalog_preparation_failed' $Config $_.Exception.Message
        Write-ReaderJson (Join-Path $diagnostic 'errors.json') ([ordered]@{ issues = @($script:Issues.ToArray()) })
        Write-ReaderJson (Join-Path $diagnostic 'ack.json') ([ordered]@{ operation = 'preparacion_clientes_centros'; status = 'fallido'; imported = $false; package_id = $packageId })
    }
    if (Test-Path -LiteralPath (Join-Path $directory 'package.json')) { Remove-Item -LiteralPath (Join-Path $directory 'package.json') -Force }
    throw
} finally { if ($lock) { $lock.Dispose() } }
