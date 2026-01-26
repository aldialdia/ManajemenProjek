@echo off
cd /d "c:\Users\nn\OneDrive - Universitas Andalas\Dokumen\Semester 5\KP\ManajemenProjek"
php artisan schedule:run >> storage\logs\scheduler.log 2>&1
