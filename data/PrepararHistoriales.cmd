@echo off
setlocal
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0PrepararHistoriales.ps1" -Config "%~dp0configuracion.ini"
set "historyExit=%errorlevel%"
echo.
pause
exit /b %historyExit%
