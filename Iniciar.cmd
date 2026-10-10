@echo off
setlocal
cd /d "%~dp0"
echo === MAINTENANCE APP - INICIO ===
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\Iniciar.ps1"
set "RC=%errorlevel%"
if not "%RC%"=="0" echo ERROR: Inicio fallido. Revise el mensaje anterior.
exit /b %RC%
