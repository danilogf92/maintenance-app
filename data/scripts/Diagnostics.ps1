function Add-ReaderIssue([string]$Severity, [string]$Code, [string]$Path, [string]$Message) {
    $script:Issues.Add([ordered]@{ severity = $Severity; code = $Code; path = $Path; message = $Message })
    Write-Host ("[{0}] {1}: {2}" -f $Severity, $Path, $Message)
}
function Write-ReaderJson([string]$Path, $Value) {
    $json = ConvertTo-Json -InputObject $Value -Depth 18
    [IO.File]::WriteAllText($Path, $json, (New-Object Text.UTF8Encoding($false)))
}
function Save-ReaderDiagnostics([string]$RunDirectory, [string]$RunId, [string]$Started, [string]$Source, $Tables, [bool]$Fatal) {
    $errorCount = @($script:Issues | Where-Object { $_.severity -eq 'error' }).Count
    $warningCount = @($script:Issues | Where-Object { $_.severity -eq 'warning' }).Count
    $state = 'completado'
    if ($Fatal) { $state = 'fallido' } elseif ($errorCount -gt 0 -or $warningCount -gt 0) { $state = 'completado_con_observaciones' }
    Write-ReaderJson (Join-Path $RunDirectory 'errors.json') ([ordered]@{
        run_id = $RunId; errors = $errorCount; warnings = $warningCount; issues = @($script:Issues.ToArray())
    })
    $status = [ordered]@{ run_id = $RunId; operation = 'inventario'; status = $state; started_at = $Started;
        finished_at = [DateTime]::UtcNow.ToString('o'); source = $Source; tables = @($Tables).Count;
        errors = $errorCount; warnings = $warningCount; imported_records = 0; database_modified = $false }
    Write-ReaderJson (Join-Path $RunDirectory 'status.json') $status
    Write-ReaderJson (Join-Path $RunDirectory 'ack.json') ([ordered]@{
        run_id = $RunId; operation = 'inventario'; status = $state; imported = $false;
        requires_selection = $true; tables = @($Tables | ForEach-Object {
            [ordered]@{ folder = $_.folder; records_valid = $_.records_valid; records_invalid = $_.records_invalid;
                status = $_.status; imported = $false; proposed_entity = $_.proposed_entity }
        })
    })
    return $status
}
