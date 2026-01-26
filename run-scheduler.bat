@echo off
cd /d "d:\Kuliah\ProjectKP\ManagerProject\ManajemenProjek"
php artisan schedule:run >> storage\logs\scheduler.log 2>&1
