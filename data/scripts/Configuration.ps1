function Get-SourceConfiguration([string]$Path) {
    if (-not (Test-Path -LiteralPath $Path -PathType Leaf)) { throw 'No existe configuracion.ini.' }
    $values = @{}
    foreach ($line in [IO.File]::ReadAllLines($Path, [Text.Encoding]::UTF8)) {
        if ($line -match '^\s*(#|;|$)') { continue }
        if ($line -notmatch '^\s*dir\s*=\s*"([^"\r\n]+)"\s*$' -or $values.ContainsKey('dir')) {
            throw 'Configuración inválida. Usa una sola línea dir="RUTA_COMPLETA".'
        }
        $values['dir'] = $Matches[1]
    }
    if (-not $values.ContainsKey('dir') -or -not [IO.Path]::IsPathRooted($values['dir'])) {
        throw 'dir debe indicar una ruta absoluta.'
    }
    if (-not (Test-Path -LiteralPath $values['dir'] -PathType Container)) { throw 'La carpeta indicada en dir no existe o no es accesible.' }
    return (Get-Item -LiteralPath $values['dir'] -Force).FullName
}
function Get-RelativeName([string]$Root, [string]$Path) {
    if ($Root.TrimEnd([char[]]@('/', '\')) -eq $Path.TrimEnd([char[]]@('/', '\'))) { return '.' }
    $prefix = $Root.TrimEnd([char[]]@('/', '\')) + [IO.Path]::DirectorySeparatorChar
    if (-not $Path.StartsWith($prefix, [StringComparison]::OrdinalIgnoreCase)) { throw 'Se detectó una ruta fuera del origen permitido.' }
    return $Path.Substring($prefix.Length).Replace('\', '/')
}
function Get-NormalizedName([string]$Name) {
    $chars = foreach ($character in $Name.Normalize([Text.NormalizationForm]::FormD).ToCharArray()) {
        if ([Globalization.CharUnicodeInfo]::GetUnicodeCategory($character) -ne [Globalization.UnicodeCategory]::NonSpacingMark) { $character }
    }
    return (($(-join $chars).ToLowerInvariant() -replace '^\d+[\s_-]*', '') -replace '[^\p{L}\p{Nd}]+', ' ').Trim()
}
function Get-Correspondences([string]$Path) {
    $result = @{}
    $data = [IO.File]::ReadAllText($Path, [Text.Encoding]::UTF8) | ConvertFrom-Json
    foreach ($property in $data.PSObject.Properties) {
        if ($property.Value -isnot [string] -or $property.Value -notmatch '^[a-z][a-z0-9_]*$') { throw 'Correspondencia de entidad inválida.' }
        $key = Get-NormalizedName $property.Name
        if ($result.ContainsKey($key) -and $result[$key] -ne $property.Value) { throw 'Hay correspondencias contradictorias para el mismo nombre.' }
        $result[$key] = $property.Value
    }
    return $result
}
