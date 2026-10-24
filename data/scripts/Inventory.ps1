function Read-InventoryRow($Row, $State, [string]$Path, [int]$Line) {
    if ($null -eq $Row -or $Row -isnot [System.Management.Automation.PSCustomObject]) {
        $State.invalid++; Add-ReaderIssue 'error' 'invalid_record' $Path ("Registro inválido en posición {0}." -f $Line); return
    }
    $State.valid++
    foreach ($property in $Row.PSObject.Properties) { $State.fields[$property.Name] = $true }
    if ($Row.PSObject.Properties['ninox_table']) { $State.tables[[string]$Row.ninox_table] = $true }
    $identity = $null
    if ($Row.PSObject.Properties['ninox_id']) { $identity = [string]$Row.ninox_id }
    elseif ($Row.PSObject.Properties['id']) { $identity = [string]$Row.id }
    if ($identity) {
        if ($State.identities.ContainsKey($identity)) {
            Add-ReaderIssue 'error' 'duplicate_identity' $Path ("Identificador repetido: {0}; posición {1}." -f $identity, $Line)
        }
        $State.identities[$identity] = $true
    }
}
function Read-InventoryRecords([string]$Path, $State) {
    if ([IO.Path]::GetExtension($Path) -ieq '.jsonl') {
        $reader = $null; $lineNumber = 0
        try {
            $reader = New-Object IO.StreamReader($Path, [Text.Encoding]::UTF8, $true)
            while ($null -ne ($line = $reader.ReadLine())) {
                $lineNumber++; if ([string]::IsNullOrWhiteSpace($line)) { continue }
                try { $row = ConvertFrom-Json -InputObject $line -ErrorAction Stop }
                catch { $State.invalid++; Add-ReaderIssue 'error' 'invalid_json' $Path ("JSON inválido en línea {0}." -f $lineNumber); continue }
                Read-InventoryRow $row $State $Path $lineNumber
            }
        } catch { Add-ReaderIssue 'error' 'records_unreadable' $Path 'No se pudo completar la lectura de los registros.' }
        finally { if ($reader) { $reader.Dispose() } }
    } else {
        try {
            $rows = @((ConvertFrom-Json -InputObject ([IO.File]::ReadAllText($Path, [Text.Encoding]::UTF8)) -ErrorAction Stop))
            $position = 0
            foreach ($row in $rows) { $position++; Read-InventoryRow $row $State $Path $position }
        } catch { $State.invalid++; Add-ReaderIssue 'error' 'invalid_json' $Path 'JSON inválido o archivo no accesible.' }
    }
}
function Get-TableInventory([string]$Source, [string]$Directory, $Mappings) {
    $issueStart = $script:Issues.Count
    $parent = (Get-Item -LiteralPath $Directory).Parent
    $name = $parent.Name
    $key = Get-NormalizedName $name
    $entity = $null
    if ($Mappings.ContainsKey($key)) { $entity = $Mappings[$key] }
    $files = @(Get-SanitizedFiles $Directory)
    $catalog = @($files | Sort-Object FullName | ForEach-Object {
        $relative = Get-RelativeName $Directory $_.FullName
        $kind = 'metadata'
        if ($relative -match '^files/') { $kind = 'attachment' }
        elseif ($relative -match '(^|/)(records\.jsonl|records\.json)$|^records/.+\.json$') { $kind = 'records' }
        [ordered]@{ path = $relative; kind = $kind; bytes = $_.Length }
    })
    $recordFiles = @()
    foreach ($candidate in @('export/records.jsonl', 'records.jsonl', 'export/records.json', 'records.json')) {
        $match = @($files | Where-Object { (Get-RelativeName $Directory $_.FullName) -eq $candidate })
        if ($match.Count -gt 0) { $recordFiles = $match; break }
    }
    if ($recordFiles.Count -eq 0) {
        $recordFiles = @($files | Where-Object { (Get-RelativeName $Directory $_.FullName) -match '^records/[^/]+\.json$' } | Sort-Object Name)
    }
    $state = @{ valid = 0; invalid = 0; fields = @{}; tables = @{}; identities = @{} }
    if ($recordFiles.Count -eq 0) { Add-ReaderIssue 'error' 'records_missing' $Directory 'No se encontró export/records.jsonl ni registros JSON compatibles.' }
    foreach ($file in $recordFiles) { Read-InventoryRecords $file.FullName $state }
    if ($recordFiles.Count -gt 0 -and $state.valid -eq 0 -and $state.invalid -eq 0) {
        Add-ReaderIssue 'warning' 'records_empty' $Directory 'La tabla no contiene registros.'
    }
    if (-not $entity) { Add-ReaderIssue 'warning' 'mapping_missing' $parent.FullName 'Falta la correspondencia de esta tabla; se conserva para selección.' }
    $attachments = @($catalog | Where-Object { $_.kind -eq 'attachment' })
    $attachmentBytes = [long]0
    foreach ($attachment in $attachments) { $attachmentBytes += [long]$attachment['bytes'] }
    $tableIssues = @($script:Issues.ToArray() | Select-Object -Skip $issueStart)
    $status = 'detectado'
    if (@($tableIssues | Where-Object { $_.severity -eq 'error' }).Count -gt 0) { $status = 'con_errores' }
    elseif ($tableIssues.Count -gt 0) { $status = 'con_observaciones' }
    return [ordered]@{ name = $name; folder = (Get-RelativeName $Source $Directory);
        source_table_ids = @($state.tables.Keys | Sort-Object); proposed_entity = $entity; import = $false;
        records_source = @($recordFiles | ForEach-Object { Get-RelativeName $Directory $_.FullName });
        records_valid = $state.valid; records_invalid = $state.invalid; fields = @($state.fields.Keys | Sort-Object);
        attachments = $attachments.Count; attachment_bytes = $attachmentBytes;
        status = $status; files = $catalog }
}
