@echo off
setlocal
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0LeerDatos.ps1" -Config "%~dp0configuracion.ini"
set "readerExit=%errorlevel%"
echo.
pause
exit /b %readerExit%
