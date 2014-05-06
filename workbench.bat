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
SET filename=workbench-#.zip

REM find work dir
IF NOT "%1" == "" (
    IF EXIST %1\NUL (
        SET installation_path=%1
        SHIFT
    ) ELSE (
        SET installation_path=%CD%
    )
) ELSE (
    SET installation_path=%CD%
)

IF NOT EXIST !installation_path! (
    ECHO Path not found !installation_path!
    EXIT
)

for /f "delims=" %%a in ('FINDSTR "coreCmsVersion" !installation_path!\config\settings.php') DO @SET version=%%a
SET contrexx_version=%version:~-7,-2%
SET filename=%filename:#=!contrexx_version!%
REM ECHO %filename%


:getCommandLineArgs
if "%1"=="" (GOTO :startWorkBench)
SET commandline_args=!commandline_args! %1
SHIFT	
GOTO :getCommandLineArgs


REM start or install workbench
:startWorkBench
IF EXIST "!installation_path!\workbench.config" (

    for /f "delims=" %%G in ('FINDSTR "php" !installation_path!\workbench.config') DO (
        SET php_path=%%G
        SET php_path=!php_path:~4!
    )

    START /B /WAIT "Contrexx Workbench" !php_path! -f !installation_path!\core_modules\Workbench\console.php !commandline_args!
	
) ELSE (
    SET php_path=C:\xampp\php\php.exe
    REM SET php_path=C:\wamp\bin\php\php5.3.5\php.exe
    
    :whileNotPHP
    IF NOT EXIST "!php_path!" (
            SET /p php_path= "PHP could not be found, please enter the correct path to php or leave empty to abort: "

            GOTO :whileNotPHP
    )
	
    SET /P answer= "This will install the current version of the Contrexx Workbench into the following path (%installation_path%). Are you sure? [Y,n] "

    IF "!answer!" == "n" (
	    ECHO "Exit from workbench installation"
        EXIT
    ) ELSE (
        SET /p distributer= "What name should be used as distributor for components? "
        ECHO Downloading workbench...
	
        SET php_code=^
            require_once '!installation_path!/core/Core/init.php';^
            \DBG::deactivate(^);^
            init(minimal^);^
            $url = '!download_url!!filename!';^
            try {^
                $request  = new \HTTP_Request2($url^);^
                $response = $request-^>send(^);^
                if (404 == $response-^>getStatus(^)^) {^
                    return false;^
                } else {^
                    file_put_contents('!installation_path!\!filename!', $response-^>getBody(^)^);^
                    $archive=new \PclZip('!installation_path!\!filename!'^);^
                    if (($files = $archive-^>extract(PCLZIP_OPT_PATH, '!installation_path!', PCLZIP_OPT_REMOVE_PATH, '!filename!'^)^) ^^!= 0^) {^
                        foreach ($files as $file^) {^
                            if (^^!in_array($file['status'],array('ok','filtered','already_a_directory'^)^)^) {^
                                    \DBG::log($archive-^>errorInfo(true^)^);^
                                    return false;^
                            }^
                        }^
                    }^
                }^
            } catch (\Exception $e^) {^
                \DBG::msg($e-^>getMessage(^)^);^
                return false;^
            }^
            @unlink('!installation_path!\!filename!'^);

        REM ECHO "!php_code!"

        START /B /WAIT "Contrexx Workbench" !php_path! -r "!php_code!"

        ECHO Creating config file...

        (
        Echo php=!php_path!
        Echo distributer=!distributer!
        ) > !installation_path!\workbench.config

        GOTO :startWorkBench
    )
)
PAUSE


REM here the windows part ends

