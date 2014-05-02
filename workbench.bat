@GOTO WINDOOF \
2>/dev/null

###############################
# Contrexx Workbench install  #
# and wrapper script Unix/Win #
#                             #
# (c) 2013 by Comvation AG    #
###############################


# here the linux part starts


# find work dir
if [ "$1" != "" ]; then
    INSTALLATION_PATH="$1"
    COMMANDLINE_ARGS=${@:2}
else
    INSTALLATION_PATH=`dirname $(readlink -f $0)`
    COMMANDLINE_ARGS=$@
fi
if [ ! -d "$INSTALLATION_PATH" ]; then
    echo "Path not found: $INSTALLATION_PATH"
fi

# start or install workbench
if [ -f "$INSTALLATION_PATH/workbench.config" ]; then
    PHP_PATH=`awk -F= '/php=/{print $2}' workbench.config`
    $PHP_PATH "$INSTALLATION_PATH/core_modules/Workbench/console.php" $COMMANDLINE_ARGS
    exit
else
    PHP_PATH="php"
    while ! command -v "$PHP_PATH" &> /dev/null
    do
        read -p "PHP could not be found, please enter the correct path to php or leave empty to abort: " path
        if [ "$path" == "" ]; then
            exit
        fi
        PHP_PATH=$path
    done
    #$PHP_PATH -r "`awk 'seen&&/"/{seen=0} seen{print} /-r "/{seen = 1}' "$(basename $0)"`"
    $PHP_PATH -r "`awk 'seen{print} /php_code=/{seen = 1} !/[\^]/{seen=0}' "$(basename $0)"`"
    # maybe rename this file to "workbench"
fi


# here the linux part ends


exit
:WINDOOF
@ECHO off


REM here the windows part starts

SETLOCAL EnableExtensions EnableDelayedExpansion

SET download_url=http://updatesrv1.contrexx.com/
SET filename=workbench-#.tar.gz

REM find work dir
IF NOT "%1" == "" (
    SET installation_path=%1
    SHIFT
) ELSE (
    SET installation_path=%CD%
)
IF NOT EXIST %installation_path% (
    ECHO Path not found %installation_path%
    EXIT
)

for /f "delims=" %%a in ('FINDSTR "coreCmsVersion" %installation_path%\config\settings.php') DO @SET version=%%a
SET contrexx_version=%version:~-7,-2%
SET filename=%filename:#=!contrexx_version!%
REM ECHO %filename%

REM start or install workbench

IF EXIST %installation_path%\workbench.config (
    REM read php location
    REM load workbench
    EXIT
) ELSE (
    SET php_path=C:\xampp\php\php.exe
    REM SET php_path="C:\wamp\bin\php\php5.3.5\php.exe"
    
    :whileNotPHP
    IF NOT EXIST "!php_path!" (	    
            SET /p php_path= "PHP could not be found, please enter the correct path to php or leave empty to abort: "

            GOTO :whileNotPHP
    )
	
    SET /P answer= "This will install the current version of the Contrexx Workbench into the following path (%installation_path%). Are you sure? [Y,n] "

    IF "%answer%" == "n" (
        REM "QUIT THE PROCESS"
        REM EXIT
    ) ELSE (
        SET /p distributer= "What name should be used as distributor for components? "
        ECHO Downloading workbench...
	
        SET php_code= "require_once '%installation_path%/core/Core/init.php';init('minimal');"
        REM ECHO !php_code!
		
        START /WAIT "Contrexx Workbench" !php_path! -r !php_code!
	
        REM download workbench for version using PEAR's HTTP2
        REM uncompress zip file using zip library of contrexx
        REM write workbench.config	
    )
)
PAUSE


REM here the windows part ends

