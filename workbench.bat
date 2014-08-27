@GOTO WINDOOF \
2>/dev/null

###############################
# Contrexx Workbench install  #
# and wrapper script Unix/Win #
#                             #
# (c) 2013 by Comvation AG    #
###############################


# here the linux part starts

FILENAME="workbench-%s.zip"

# find work dir
if [ "$1" != "" ] && [ -d "$1" ]; then
    INSTALLATION_PATH="$1"
    COMMANDLINE_ARGS=${@:2}
else
    INSTALLATION_PATH=`dirname $(readlink -f $0)`
    COMMANDLINE_ARGS=$@
fi
if [ ! -d "$INSTALLATION_PATH" ]; then
    echo "Path not found: $INSTALLATION_PATH"
fi

if [ ! -f "$INSTALLATION_PATH/config/settings.php" ]; then
    echo "$INSTALLATION_PATH is not a Contrexx installation directory"
    exit
fi

CONTREXX_VERSION=`grep "'coreCmsVersion'" "$INSTALLATION_PATH/config/settings.php" | awk '{print substr($3, 2, index($3,";")-3)}'`
FILENAME=`printf "$FILENAME" $CONTREXX_VERSION`

# start or install workbench
if [ -f "$INSTALLATION_PATH/workbench.config" ]; then
    PHP_PATH=`awk -F= '/php=/{gsub(/^[ \t]+|[ \t]+$/,"");print $1}' $INSTALLATION_PATH/workbench.config`    
    $PHP_PATH -f "$INSTALLATION_PATH/core_modules/Workbench/console.php" $COMMANDLINE_ARGS
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
    
    PHP_CODE=`awk -v installation_path=$INSTALLATION_PATH -v file_name=$FILENAME -v php_path=$PHP_PATH '
escape=0;
/php_code=/{
    seen=1;escape=1
}
!/[\^]/{
    seen=0
}
seen && !escape {
    gsub(/!installation_path!/, installation_path);
    gsub(/!filename!/, file_name);
    gsub(/!php_path!/, php_path);
    gsub(/[\^]/,"");
    print
}
' "$(basename $0)"`

    $PHP_PATH -r "$PHP_CODE"

    ./$(basename $0) $INSTALLATION_PATH $COMMANDLINE_ARGS
fi


# here the linux part ends


exit
:WINDOOF
@ECHO off


REM here the windows part starts

SETLOCAL EnableExtensions EnableDelayedExpansion

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
    GOTO :EOF
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
        ECHO Exit from workbench installation
        EXIT /B
    ) ELSE (
        ECHO Downloading workbench...
	
        SET php_code=^
            require_once '!installation_path!/core/Core/init.php';^
            \DBG::deactivate(^);^
            init('minimal'^);^
            $url = 'http://updatesrv1.contrexx.com/!filename!';^
            try {^
                $request  = new \HTTP_Request2($url^);^
                $response = $request-^>send(^);^
                if (404 == $response-^>getStatus(^)^) {^
                    return false;^
                } else {^
                    try {^
                        $file = new \Cx\Lib\FileSystem\File(ASCMS_DOCUMENT_ROOT . '/!filename!'^);^
                        $file-^>touch(^);^
                        $file-^>write($response-^>getBody(^)^);^
                    } catch(\Cx\Lib\FileSystem\FileSystemException $e^) {}^
                    ^echo 'Extracting Files..'. PHP_EOL;^
                    $archive=new \PclZip(ASCMS_DOCUMENT_ROOT . '/!filename!'^);^
                    if (($files = $archive-^>extract(PCLZIP_OPT_PATH, ASCMS_DOCUMENT_ROOT, PCLZIP_OPT_REMOVE_PATH, '!filename!'^)^) ^^!= 0^) {^
                        foreach ($files as $file^) {^
                            if (^^!in_array($file['status'],array('ok','filtered','already_a_directory'^)^)^) {^
                                \DBG::log($archive-^>errorInfo(true^)^);^
                                \DBG::log('Status '.$file['status']^);^
                                return false;^
                            }^
                        }^
                    }^
                }^
		try {^
                    $file = new \Cx\Lib\FileSystem\File(ASCMS_DOCUMENT_ROOT.'/'.ASCMS_PATH_OFFSET.'/!filename!'^);^
                    $file-^>delete(^);^
                } catch(\Cx\Lib\FileSystem\FileSystemException $e^) {}^
                ^echo 'What name should be used as distributor for components? ';^
                $handle = fopen ('php://stdin','r'^);^
                $line = fgets($handle^);^
                $distributor = trim($line^);^
                ^echo 'Creating Configuration file..'. PHP_EOL;^
                try {^
                    $workBenchConfig = 'php=!php_path!'. PHP_EOL. 'distributor='. $distributor;^
                    $file = new \Cx\Lib\FileSystem\File(ASCMS_DOCUMENT_ROOT . '/workbench.config'^);^
                    $file-^>touch(^);^
                    $file-^>write($workBenchConfig^);^
                } catch(\Cx\Lib\FileSystem\FileSystemException $e^) {}^
            } catch (\Exception $e^) {^
                \DBG::msg($e-^>getMessage(^)^);^
                return false;^
            }^

        REM ECHO "!php_code!"

        START /B /WAIT "Contrexx Workbench" !php_path! -r "!php_code!"

        GOTO :startWorkBench
    )
)
PAUSE


REM here the windows part ends