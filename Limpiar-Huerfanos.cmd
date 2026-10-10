@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\Limpiar-Huerfanos.ps1"
exit /b %errorlevel%
