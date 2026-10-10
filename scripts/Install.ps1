param([string]$Destination = '')
$ErrorActionPreference = 'Stop'
$sourceRoot = Split-Path $PSScriptRoot -Parent
$projectRoot = $sourceRoot
if ($Destination) {
    $projectRoot = [IO.Path]::GetFullPath($Destination)
    if ($projectRoot.TrimEnd('\') -ne $sourceRoot.TrimEnd('\')) {
        if (Test-Path $projectRoot) { throw 'El destino ya existe. Seleccione una carpeta nueva para la plantilla.' }
        if ($projectRoot.StartsWith($sourceRoot.TrimEnd('\') + '\',[StringComparison]::OrdinalIgnoreCase)) { throw 'El destino debe estar fuera de la carpeta del paquete.' }
        New-Item -ItemType Directory -Path $projectRoot | Out-Null
        Get-ChildItem -Force $sourceRoot | Where-Object { $_.Name -notin @('.env','vendor','node_modules','.git') } | Copy-Item -Destination $projectRoot -Recurse -Force
    }
}
Set-Location $projectRoot
function Run-Docker {
    & docker @args
    if ($LASTEXITCODE -ne 0) { throw "Docker fallo con codigo $LASTEXITCODE. La carpeta queda disponible para revisar y reintentar." }
}
Run-Docker version
if (-not (Test-Path '.env')) { Copy-Item '.env.example' '.env' }
Run-Docker compose build app
Run-Docker compose up -d mariadb
Run-Docker compose run --rm app composer install --no-interaction --prefer-dist
$envContent = Get-Content '.env' -Raw
if ($envContent -match '(?m)^APP_KEY=\s*$') { Run-Docker compose run --rm app php artisan key:generate }
Run-Docker compose run --rm app php artisan migrate --seed --force
Run-Docker compose run --rm app php artisan storage:link
Run-Docker compose --profile tools up -d
Write-Host 'Instalacion lista. Crear el SuperAdmin con:'
Write-Host 'docker compose exec app php artisan template:setup su-correo@empresa.com'
Write-Host 'Aplicacion: http://localhost:8080 | phpMyAdmin: http://localhost:8081'
