$ErrorActionPreference = 'Stop'
Write-Host 'LIMPIEZA GLOBAL DE DOCKER: contenedores detenidos y volumenes SIN USO.' -ForegroundColor Yellow
Write-Host 'ADVERTENCIA: puede eliminar volumenes con datos de otros proyectos detenidos.' -ForegroundColor Yellow
Write-Host 'No se eliminan contenedores activos ni volumenes asociados a contenedores existentes.'
& docker container ls -a
& docker volume ls
$confirm = Read-Host 'Escriba LIMPIAR para ejecutar docker container prune y docker volume prune -a; ENTER para omitir'
if ($confirm -cne 'LIMPIAR') { Write-Host 'Limpieza omitida.'; exit 0 }
& docker container prune -f
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
& docker volume prune -a -f
exit $LASTEXITCODE
