::Must to convert GBK encoding
@echo off

if "%1" EQU "" (
	::如果当前文件夹参数不存在
	echo Usage: %0 dir_path
	goto :eof
)

::开始调用，使用相对路径
call :process %1
goto :eof

:process
::查询目录中的文件列表
for /f "delims=" %%a in ('dir /a-d /b %1\*.php') do (
	echo FILE：%%a
	if not exist "new_%1" (
		:: 不存在目录进行创建
		mkdir "new_%1"
	)
	:: 开始代码转换
	php -w "%1\%%a" > "new_%1\%%a"
)

::查询文件夹列表
for /f "delims=" %%a in ('dir /ad /b %1') do (
	::判断是否存在文件夹
	if exist "%1\%%a" (
		echo -------------------------------------------
		echo DIR：%1\%%a
		::开始进行递归操作
		call :process %1\%%a
	)
)
goto :eof