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


REM todo: PHP
REM todo: Make directory and file checks work
REM todo: read php location if workbench is installed
REM todo: load workbench if installed

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

REM start or install workbench
IF EXIST $INSTALLATION_PATH/workbench.config (
    REM read php location
    REM load workbench
    exit
) ELSE (
    SET php_path=C:\xampp\php\php.exe
    
    :whileNotPHP
    IF NOT EXIST %php_path% (
        SET /p php_path=PHP could not be found, please enter the correct path to php or leave empty to abort: 
        IF "%php_path == "" (
            REM EXIT
        )
        REM GOTO :whileNotPHP
    )

    SET php_code= ^
        print_r($_SERVER['argv']);

    START /WAIT /B %php_path% -r "%php_code%" %installation_path%
        REM check for contrexx installation
        REM find contrexx version
        REM This will install the current version of the Contrexx Workbench into the following path ($INSTALLATION_PATH). Are you sure? [Y,n]
        REM What name should be used as distributor for components?
        REM download workbench for version using PEAR's HTTP2
        REM uncompress zip file using zip library of contrexx
        REM write workbench.config
)
PAUSE


REM here the windows part ends

