function Find-SanitizedDirectories([string]$Root) {
    $pending = New-Object 'Collections.Generic.Stack[string]'
    $found = New-Object 'Collections.Generic.List[string]'
    $pending.Push($Root)
    while ($pending.Count -gt 0) {
        $current = $pending.Pop()
        try {
            $info = Get-Item -LiteralPath $current -Force
            if (($info.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) {
                Add-ReaderIssue 'warning' 'link_skipped' $current 'Se omitió un enlace o punto de unión.'; continue
            }
            if ($info.Name -ieq 'DataSanitized') { $found.Add($info.FullName); continue }
            foreach ($child in Get-ChildItem -LiteralPath $current -Directory -Force -ErrorAction Stop) {
                if ($child.Name -iin @('DataFull', 'DataSanitizedBasic', '_adjuntos', '.git', 'node_modules', 'vendor', 'resultado')) { continue }
                $pending.Push($child.FullName)
            }
        } catch { Add-ReaderIssue 'error' 'directory_unreadable' $current 'No se pudo leer esta carpeta.' }
    }
    return @($found.ToArray() | Sort-Object)
}
function Get-SanitizedFiles([string]$Root) {
    $pending = New-Object 'Collections.Generic.Stack[string]'; $pending.Push($Root)
    $files = New-Object 'Collections.Generic.List[object]'
    while ($pending.Count -gt 0) {
        $current = $pending.Pop()
        try {
            foreach ($entry in Get-ChildItem -LiteralPath $current -Force -ErrorAction Stop) {
                if (($entry.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) {
                    Add-ReaderIssue 'warning' 'link_skipped' $entry.FullName 'Se omitió un enlace o punto de unión.'; continue
                }
                if ($entry.PSIsContainer) { $pending.Push($entry.FullName) } else { $files.Add($entry) }
            }
        } catch { Add-ReaderIssue 'error' 'files_unreadable' $current 'No se pudo enumerar el contenido.' }
    }
    return $files.ToArray()
}
