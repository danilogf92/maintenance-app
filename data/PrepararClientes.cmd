@echo off
setlocal
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0PrepararClientes.ps1" -Config "%~dp0configuracion.ini"
set "prepareExit=%errorlevel%"
echo.
pause
exit /b %prepareExit%
