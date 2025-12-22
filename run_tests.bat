@echo off
cd /d "c:\Users\User\Desktop\FYP 2\laravel12_bootstrap"
echo Running Tests...
php artisan test > test_results.log 2>&1
echo.
echo Test Results:
type test_results.log
echo.
echo Results saved to test_results.log
pause
