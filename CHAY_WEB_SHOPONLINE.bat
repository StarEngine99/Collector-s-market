@echo off
title Khoi chay ShopOnline - Cho Figure va The Bai

echo ========================================================
echo   Dang khoi chay may chu ShopOnline (Laravel)...
echo   Dia chi web: http://localhost:8000
echo ========================================================

rem Chuyen thu muc vao ShopOnline\public
cd /d "%~dp0ShopOnline\public"

rem Mo trinh duyet
start "" "http://localhost:8000"

rem Chay PHP server voi cau hinh MySQL va server router
php -c "..\php.ini" -S 127.0.0.1:8000 "..\server.php"

pause
