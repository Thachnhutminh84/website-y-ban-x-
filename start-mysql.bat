@echo off
echo Dang kiem tra va khoi dong MySQL...
echo.

REM Kiem tra XAMPP
if exist "C:\xampp\mysql_start.bat" (
    echo Tim thay XAMPP, dang khoi dong MySQL...
    cd /d C:\xampp
    call mysql_start.bat
    goto :end
)

REM Kiem tra MySQL Service
net start | find "MySQL" > nul
if %errorlevel% equ 0 (
    echo MySQL dang chay.
    goto :end
)

REM Thu khoi dong MySQL service
echo Dang thu khoi dong MySQL service...
net start MySQL
if %errorlevel% equ 0 (
    echo MySQL da duoc khoi dong thanh cong!
    goto :end
)

net start MySQL80
if %errorlevel% equ 0 (
    echo MySQL da duoc khoi dong thanh cong!
    goto :end
)

echo.
echo KHONG TIM THAY MYSQL!
echo Vui long:
echo 1. Mo XAMPP Control Panel va nhan Start MySQL
echo 2. Hoac mo WAMP va dam bao service dang chay
echo 3. Hoac cai dat MySQL neu chua co
echo.

:end
pause
