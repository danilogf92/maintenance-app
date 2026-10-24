param(
    [string]$Config = (Join-Path $PSScriptRoot 'configuracion.ini'),
    [string]$Output = (Join-Path $PSScriptRoot 'resultado')
)
$ErrorActionPreference = 'Stop'
foreach ($scriptFile in @('Configuration', 'Diagnostics', 'Discovery', 'Inventory')) {
    . (Join-Path $PSScriptRoot ('scripts/' + $scriptFile + '.ps1'))
}
$script:Issues = New-Object 'Collections.Generic.List[object]'
$tables = New-Object 'Collections.Generic.List[object]'
$source = ''; $fatal = $false
$runId = [DateTime]::UtcNow.ToString('yyyyMMdd-HHmmss') + '-' + [Guid]::NewGuid().ToString('N').Substring(0, 8)
$started = [DateTime]::UtcNow.ToString('o')
$runDirectory = Join-Path $Output $runId
New-Item -ItemType Directory -Path $runDirectory -Force | Out-Null
$lock = $null
try {
    $lock = [IO.File]::Open((Join-Path $Output 'lector.lock'), [IO.FileMode]::OpenOrCreate, [IO.FileAccess]::ReadWrite, [IO.FileShare]::None)
    & (Join-Path $PSScriptRoot 'PrepararConfiguracion.ps1')
    $source = Get-SourceConfiguration $Config
    $mappings = Get-Correspondences (Join-Path $PSScriptRoot 'correspondencias.json')
    Write-Host ('Origen: ' + $source)
    Write-Host 'Buscando exclusivamente DataSanitized, incluidas las subtablas...'
    $directories = @(Find-SanitizedDirectories $source)
    foreach ($directory in $directories) {
        try {
            $table = Get-TableInventory $source $directory $mappings
            $tables.Add($table)
            Write-Host ('{0}: {1} registros; {2} adjuntos; {3}' -f $table.name, $table.records_valid, $table.attachments, $table.status)
        } catch {
            Add-ReaderIssue 'error' 'table_inventory_failed' $directory $_.Exception.Message
            $tables.Add([ordered]@{ name = (Get-Item -LiteralPath $directory).Parent.Name;
                folder = (Get-RelativeName $source $directory); source_table_ids = @(); proposed_entity = $null;
                import = $false; records_source = @(); records_valid = 0; records_invalid = 0;
                fields = @(); attachments = 0; attachment_bytes = [long]0; status = 'fallido'; files = @() })
        }
    }
    $duplicates = @($tables.ToArray() | Where-Object { $_.source_table_ids.Count -gt 0 } | Group-Object { $_.source_table_ids -join ',' } | Where-Object { $_.Count -gt 1 })
    foreach ($group in $duplicates) {
        Add-ReaderIssue 'warning' 'repeated_source_table' $source ('La tabla de origen ' + $group.Name + ' aparece en varias carpetas. Se requiere elegir o comparar sus copias.')
    }
    if ($directories.Count -eq 0) { Add-ReaderIssue 'error' 'no_tables' $source 'No se encontró ninguna carpeta llamada DataSanitized.' }
} catch {
    $fatal = $true
    Add-ReaderIssue 'error' 'scan_failed' $Config $_.Exception.Message
} finally { if ($lock) { $lock.Dispose() } }
$inventory = [ordered]@{ version = 1; run_id = $runId; operation = 'inventario'; source = $source;
    data_directory = 'DataSanitized'; imported_records = 0; tables = @($tables.ToArray()) }
Write-ReaderJson (Join-Path $runDirectory 'carpetas.json') $inventory
$lines = New-Object 'Collections.Generic.List[string]'
$lines.Add('# Carpetas de datos detectadas'); $lines.Add(''); $lines.Add('Origen: ' + $source); $lines.Add('')
foreach ($table in $tables) {
    $entity = $table.proposed_entity; if (-not $entity) { $entity = 'pendiente de correspondencia' }
    $lines.Add('- **' + $table.name + '** → ' + $entity)
    $lines.Add('  - Carpeta: `' + $table.folder + '`')
    $lines.Add('  - Registros válidos: ' + $table.records_valid + '; inválidos: ' + $table.records_invalid)
    $lines.Add('  - Adjuntos: ' + $table.attachments + '; estado: ' + $table.status)
}
[IO.File]::WriteAllLines((Join-Path $runDirectory 'carpetas.md'), $lines.ToArray(), (New-Object Text.UTF8Encoding($false)))
$status = Save-ReaderDiagnostics $runDirectory $runId $started $source $tables.ToArray() $fatal
$zipPath = Join-Path $Output ('diagnostico-' + $runId + '.zip')
try {
    Compress-Archive -LiteralPath @((Join-Path $runDirectory 'carpetas.json'), (Join-Path $runDirectory 'carpetas.md'),
        (Join-Path $runDirectory 'status.json'), (Join-Path $runDirectory 'errors.json'), (Join-Path $runDirectory 'ack.json')) -DestinationPath $zipPath -Force
    Write-Host ('Diagnóstico para compartir: ' + $zipPath)
} catch {
    Add-ReaderIssue 'error' 'diagnostic_zip_failed' $runDirectory 'No se pudo crear el ZIP. Comparte los JSON de esta carpeta.'
    $status = Save-ReaderDiagnostics $runDirectory $runId $started $source $tables.ToArray() $fatal
}
Write-Host ('Resultado: ' + $status.status + '. Inventario guardado en: ' + $runDirectory)
Write-Host 'No se han importado, actualizado ni borrado datos del sistema.'
if ($status.errors -gt 0) { exit 1 }
exit 0
