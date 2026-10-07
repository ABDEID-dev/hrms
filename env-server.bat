@echo off
powershell -ExecutionPolicy Bypass -File "%~dp0switch-env.ps1" server
pause
