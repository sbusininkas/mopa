@echo off
REM Queue Worker Launcher for MOPA Timetable Generation
REM Start this script to process background jobs

echo Starting Laravel Queue Worker...
echo Queue: timetables
echo Tries: 1
echo Timeout: 120 seconds
echo.
echo Keep this window open while using the application.
echo Press Ctrl+C to stop the worker.
echo.

cd /d "c:\xampp\htdocs\mopa"
php artisan queue:work --queue=timetables --tries=1 --timeout=120

pause
