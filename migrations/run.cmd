@echo off
php -v >nul 2>&1
if errorlevel 1 (
    echo PHP n'est pas installé ou pas dans le PATH.
    pause
    exit /b
)
php "%~dp0main.php"
