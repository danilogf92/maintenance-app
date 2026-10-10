$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
function Run-Docker([string[]]$Arguments) {
    & docker @Arguments
    if ($LASTEXITCODE -ne 0) { throw "Docker fallo: docker $($Arguments -join ' ') (codigo $LASTEXITCODE)" }
}
Run-Docker @('info','--format','{{.ServerVersion}}')
Write-Host 'Paso 1: limpieza opcional de recursos huerfanos ANTES del arranque.'
& powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'Limpiar-Huerfanos.ps1')
if ($LASTEXITCODE -ne 0) { throw 'La limpieza fallo.' }
if (-not (Test-Path '.env')) { Copy-Item '.env.example' '.env' }
foreach ($port in @(8611,3611,8610)) {
    $listeners = @(Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue)
    if ($listeners.Count -gt 0) {
        Write-Host "ATENCION: puerto $port ocupado. Si es por este mismo proyecto Docker, se puede reutilizar." -ForegroundColor Yellow
    }
}
Write-Host 'Paso 2: construyendo imagen PHP (sin Tailwind/Vite heredado).'
Run-Docker @('compose','build','app')
Write-Host 'Paso 3: iniciando base de datos.'
Run-Docker @('compose','up','-d','mariadb')
Write-Host 'Paso 4: preparando APP_KEY.'
$envText = Get-Content '.env' -Raw
if ($envText -match '(?m)^APP_KEY=\s*$') {
    Run-Docker @('compose','run','--rm','--no-deps','app','php','artisan','key:generate')
}
Write-Host 'Paso 5: migraciones y datos base.'
Run-Docker @('compose','run','--rm','app','php','artisan','migrate','--seed','--force')
Write-Host 'Paso 6: enlace de archivos.'
Run-Docker @('compose','run','--rm','--no-deps','app','php','artisan','storage:link')
Write-Host 'Paso 7: levantando servicios.'
Run-Docker @('compose','up','-d','--remove-orphans')
Write-Host ''
Write-Host 'Aplicacion: http://localhost:8611/login'
Write-Host 'Admin: http://localhost:8611/admin'
Write-Host 'phpMyAdmin: http://localhost:8610'
Write-Host 'MariaDB (Windows): localhost:3611'
Write-Host 'PRIMER SUPERADMIN: docker compose exec app php artisan template:setup correo@ejemplo.com'
