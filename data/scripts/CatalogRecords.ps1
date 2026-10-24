function Write-CatalogRow($Row, [string]$Expected, $State, $Writer, [string]$Raw = '') {
    if ($null -eq $Row -or $Row -isnot [System.Management.Automation.PSCustomObject]) { throw 'Registro de origen inválido.' }
    $identity = [string]$Row.ninox_id
    if ([string]$Row.ninox_table -cne $Expected -or $identity -notmatch '^[1-9][0-9]{0,17}$') { throw 'La tabla o el identificador de un registro no corresponde al destino.' }
    if ($State.identities.ContainsKey($identity)) { throw 'Se ha repetido un identificador. No se preparó la carga.' }
    $State.identities[$identity] = $true
    if ($Raw) { $Writer.WriteLine($Raw) } else { $Writer.WriteLine((ConvertTo-Json -InputObject $Row -Depth 64 -Compress)) }
    $State.count++
}
function Copy-CatalogRecords([string]$Source, [string]$Destination, [string]$Expected) {
    $files = @(Get-SanitizedFiles $Source)
    $selected = @()
    foreach ($candidate in @('export/records.jsonl', 'records.jsonl', 'export/records.json', 'records.json')) {
        $selected = @($files | Where-Object { (Get-RelativeName $Source $_.FullName) -eq $candidate })
        if ($selected.Count -gt 0) { break }
    }
    if ($selected.Count -eq 0) {
        $selected = @($files | Where-Object { (Get-RelativeName $Source $_.FullName) -match '^records/[^/]+\.json$' } | Sort-Object Name)
    }
    if ($selected.Count -eq 0) { throw 'No se encontraron registros compatibles en DataSanitized.' }
    $state = @{ count = 0; identities = @{} }
    $writer = New-Object IO.StreamWriter($Destination, $false, (New-Object Text.UTF8Encoding($false)))
    try {
        foreach ($file in $selected) {
            if ($file.Extension -ieq '.jsonl') {
                $reader = New-Object IO.StreamReader($file.FullName, [Text.Encoding]::UTF8, $true)
                try {
                    while ($null -ne ($line = $reader.ReadLine())) {
                        if ([string]::IsNullOrWhiteSpace($line)) { continue }
                        try { $row = ConvertFrom-Json -InputObject $line -ErrorAction Stop }
                        catch { throw 'Hay JSON inválido en el origen. Corrige el archivo antes de preparar.' }
                        Write-CatalogRow $row $Expected $state $writer $line
                    }
                } finally { $reader.Dispose() }
            } else {
                try { $rows = @((ConvertFrom-Json -InputObject ([IO.File]::ReadAllText($file.FullName, [Text.Encoding]::UTF8)) -ErrorAction Stop)) }
                catch { throw 'Hay JSON inválido en el origen. Corrige el archivo antes de preparar.' }
                foreach ($row in $rows) { Write-CatalogRow $row $Expected $state $writer }
            }
        }
    } finally { $writer.Dispose() }
    if ($state.count -eq 0) { throw 'La tabla está vacía. No se publica el paquete.' }
    if ($state.count -gt 50000 -or (Get-Item -LiteralPath $Destination).Length -gt 50MB) { throw 'El paquete excede el tamaño admitido para esta fase de carga.' }
    return $state.count
}
