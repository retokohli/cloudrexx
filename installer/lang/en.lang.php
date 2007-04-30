<?php
/**
 * Installer language file
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Astalavista Development Team <thun@astalvista.ch>
 * @version       1.0.0
 * @package     contrexx
 * @subpackage  installer
 * @todo        Edit PHP DocBlocks!
 */

// help
$_ARRLANG['TXT_HELP_MSG'] = "Encountered installation problems with [NAME] you can proceed with the following options: <br /><ul><li>Search for help in [FORUM]</li><li>View previous answers in  [SUPPORT]</li><li>Send us an email at [EMAIL] with a description of the problem and the edition of the [PHPINFO]</li></ul>";
$_ARRLANG['TXT_PHP_INFO'] = "PHP configuration";
$_ARRLANG['TXT_FORUM'] = "Forum";
$_ARRLANG['TXT_SUPPORT'] = "Support area";

// titles
$_ARRLANG['TXT_REQUIREMENTS'] = "System requirements";
$_ARRLANG['TXT_LICENSE'] = "License conditions";
$_ARRLANG['TXT_CONFIGURATION'] = "Configuration";
$_ARRLANG['TXT_INSTALLATION'] = "Installation";
$_ARRLANG['TXT_SYSTEM_CONFIGURATION'] = "System configuration";
$_ARRLANG['TXT_ADMIN_ACCOUNT'] = "Administrator account";
$_ARRLANG['TXT_TERMINATION'] = "Termination";
$_ARRLANG['TXT_HELP'] = "Help";

// welcome
$_ARRLANG['TXT_WELCOME'] = "Welcome";
$_ARRLANG['TXT_WELCOME_MSG'] = "<b>Welcome to the Contrexx Web Installer</b><br />You will install the [EDITION] version [VERSION] of [NAME].";
$_ARRLANG['TXT_LANGUAGE'] = "Language";
$_ARRLANG['TXT_NEW_VERSION'] = "A newer version of [NAME] is released.<br />Newest version: [VERSION]";

// general
$_ARRLANG['TXT_NEXT'] = "Next";
$_ARRLANG['TXT_BACK'] = "Back";
$_ARRLANG['TXT_CANCEL'] = "Cancel";
$_ARRLANG['TXT_STOP'] = "Stop";
$_ARRLANG['TXT_USERNAME'] = "User name";
$_ARRLANG['TXT_PASSWORD'] = "Password";
$_ARRLANG['TXT_GENERAL'] = "General";
$_ARRLANG['TXT_FILL_OUT_ALL_FIELDS'] = "You have to fill in all fields!";
$_ARRLANG['TXT_FTP_PASSIVE_MODE_FAILED'] = "Could not turn on the passive mode!";

// license
$_ARRLANG['TXT_READ_LICENCE'] = "Please read/accept license to continue installation";
$_ARRLANG['TXT_MUST_ACCEPT_LICENCE'] = "You have to accept the license agreement to proceed with the installation!";
$_ARRLANG['TXT_ACCEPT_LICENSE'] = "I accept the Contrexx license";

// requirements
$_ARRLANG['TXT_SOFTWARE_REQUIREMENTS'] = "Software requirements";
$_ARRLANG['TXT_PHP'] = "PHP";
$_ARRLANG['TXT_PHP_VERSION'] = "PHP version";
$_ARRLANG['TXT_MYSQL_VERSION'] = "MySQL version";
$_ARRLANG['TXT_PHP_EXTENSIONS'] = "PHP extensions";
$_ARRLANG['TXT_PHP_CONFIGURATION'] = "PHP configuration";
$_ARRLANG['TXT_ALLOW_URL_FOPEN'] = "allow_url_fopen";
$_ARRLANG['TXT_GD_VERSION'] = "GD (Graphics Draw) version";
$_ARRLANG['TXT_FTP_SUPPORT'] = "FTP support";
$_ARRLANG['TXT_YES'] = "Yes";
$_ARRLANG['TXT_NO'] = "No";
$_ARRLANG['TXT_ON'] = "On";
$_ARRLANG['TXT_OFF'] = "Off";
$_ARRLANG['TXT_PHP_VERSION_REQUIRED'] = "PHP version [VERSION] or later is required!  ";
$_ARRLANG['TXT_MYSQL_VERSION_REQUIRED'] = "MySQL extension version [VERSION] or later for PHP is required!";
$_ARRLANG['TXT_GD_VERSION_REQUIRED'] = "GD extension version [VERSION] or later for PHP is required!  ";
$_ARRLANG['TXT_ALLOW_URL_FOPEN_FOR_RSS_REQUIRED'] = "As long as the directive \"allow_url_fopen\" is turned off in the PHP configuration, the module <i>News Syndication</i> cannot be used!  ";
$_ARRLANG['TXT_FTP_SUPPORT_REQUIRED'] = "FTP support for PHP is not permitted on this server.This is necessary because PHP runs in Safemode! Change the PHP configuration with the option ('--enable ftp ') or installing the system manually.";

// configuration
$_ARRLANG['TXT_FTP_PATH_CONFIG'] = "FTP path configuration";
$_ARRLANG['TXT_DOCUMENT_ROOT_DESCRIPTION'] = " Sending the Web server index file from the configured directory.";
$_ARRLANG['TXT_DOCUMENT_ROOT'] = "Document Root directory";
$_ARRLANG['TXT_OFFSET_PATH'] = "Web path";
$_ARRLANG['TXT_OFFSET_PATH_DESCRIPTION'] = "Provide the path to the index file, relative to the Document Root, where you have unpacked the [NAME].";
$_ARRLANG['TXT_DATABASE'] = "MySQL database";
$_ARRLANG['TXT_HOSTNAME'] = "Hostname";
$_ARRLANG['TXT_DATABASE_NAME'] = "Database name";
$_ARRLANG['TXT_TABLE_PREFIX'] = "Table prefix";
$_ARRLANG['TXT_FTP_PATH'] = "Path to the Document Root directory";
$_ARRLANG['TXT_FTP_PATH_DESCRIPTION'] = "Provide the path to where you putted the files of the Contrexx WCMS on the FTP server.";
$_ARRLANG['TXT_FTP'] = "FTP";
$_ARRLANG['TXT_DOCUMENT_ROOT_NEEDED'] = "You must provide the local path to the Contrexx WCMS on your Web server!";
$_ARRLANG['TXT_DB_HOSTNAME_NEEDED'] = "You must provide the computer name of your database server!";
$_ARRLANG['TXT_DB_USERNAME_NEEDED'] = "You must provide a valid user name which exists on the database server to process request!  ";
$_ARRLANG['TXT_DB_DATABASE_NEEDED'] = "You must define or select an existing database!  ";
$_ARRLANG['TXT_DB_TABLE_PREFIX_NEEDED'] = "You must define a prefix for the tables in the database!  ";
$_ARRLANG['TXT_FTP_HOSTNAME_NEEDED'] = "You must provide the computer name of your FTP server! ";
$_ARRLANG['TXT_FTP_USERNAME_NEEDED'] = "You must provide a valid user name which exists on the FTP server to process request!  ";
$_ARRLANG['TXT_USE_FTP'] = "Use FTP ";
$_ARRLANG['TXT_PATH_DOES_NOT_EXIST'] = "The path \"[PATH]\" does not exist!";
$_ARRLANG['TXT_CANNOT_FIND_FIlE'] = "Cannot find the file \"[FILE]\" in the WCMS directory!";
$_ARRLANG['TXT_DIRECTORY_ON_FTP_DOES_NOT_EXIST'] = "The directory \"[DIRECTORY]\" does not exist on the FTP server!";
$_ARRLANG['TXT_FILE_ON_FTP_DOES_NOT_EXIST'] = "The file \"[FILE]\" does not exist on the FTP server!";
$_ARRLANG['TXT_USE_PASSIVE_FTP'] = "Passive mode";
$_ARRLANG['TXT_FTP_DESCRIPTION'] = "This option enables file manipulations over the FTP protocol. FTP must be used if the Web page is running on a Unix family of operating systems and the PHP installation runs in Safe mode.";
$_ARRLANG['TXT_DB_TABLE_PREFIX_INVALID'] = "The table prefix may only consist of alphanumeric signs (a-z/A-Z/0-9) and the following special character: _";
$_ARRLANG['TXT_OPEN_BASEDIR_TMP_MISSING'] = "The directory tree couldn't be showed on this server, due that the PHP-directive open_basedir is active, but does not include the required temp path (/tmp)!";
$_ARRLANG['TXT_OPEN_BASEDIR_MISS_CONFIGURED'] = "The directory tree couldn't be showed on this server, due that the PHP-directive open_basedir is miss configured!";
$_ARRLANG['TXT_DATABASE_CONNECTION_COLLATION'] = "Connection charset";

// installation
$_ARRLANG['TXT_COULD_NOT_CHANGE_PERMISSIONS'] = "Could not change the permissions:  ";
$_ARRLANG['TXT_CANNOT_OPEN_FILE'] = "Cannot open the file [FILENAME]";
$_ARRLANG['TXT_CANNOT_CREATE_FILE'] = "Cannot create file %s";
$_ARRLANG['TXT_CANNOT_CONNECT_TO_DB_SERVER'] = "No connection to the database server!  ";
$_ARRLANG['TXT_DATABASE_ALREADY_EXISTS'] = "A database file name \"[DATABASE]\" exists already!  ";
$_ARRLANG['TXT_DATABASE_DOES_NOT_EXISTS'] = "The database file name \"[DATABASE]\" does not exist! ";
$_ARRLANG['TXT_COULD_NOT_CREATE_DATABASE'] = "The database could not be created!  ";
$_ARRLANG['TXT_CANNOT_CONNECT_TO_FTP_HOST'] = "No connection to the FTP server!  ";
$_ARRLANG['TXT_FTP_AUTH_FAILED'] = "The authentication to the FTP server failed with the provided user name and password!";
$_ARRLANG['TXT_FTP_PATH_DOES_NOT_EXISTS'] = "The path to the Contrexx WCMS on the FTP server does not exist!";
$_ARRLANG['TXT_COULD_NOT_READ_SQL_DUMP_FILE'] = "Could not open the SQL-Dump file \"[FILENAME]\"!  ";
$_ARRLANG['TXT_SQL_QUERY_ERROR'] = "SQL query error:  ";
$_ARRLANG['TXT_CORRECT_THE_FOLLOWING_ERROR'] = "To repeat the installation step, correct the following errors and reload the site:";
$_ARRLANG['TXT_SET_PERMISSIONS'] = "Write-permission set";
$_ARRLANG['TXT_SUCCESSFULLY'] = "Successful";
$_ARRLANG['TXT_FAILED'] = "Failed";
$_ARRLANG['TXT_CREATE_DATABASE'] = "Create database";
$_ARRLANG['TXT_DATABASE_CREATED'] = "Database created";
$_ARRLANG['TXT_CREATE_DATABASE_TABLES'] = "Database tables created";
$_ARRLANG['TXT_TABLE_NOT_AVAILABLE'] = "Table \"[TABLE]\" is not available";
$_ARRLANG['TXT_CREATE_DATABAES_TABLE_MANUALLY'] = "Generate the necessary table(s) and join the contents into the table (s) with the aid of <a href=\"[FILEPATH]\" title=\"SQL-Datei\">SQL file</a>!";
$_ARRLANG['TXT_CHECK_DATABASE_TABLES'] = "Check database structure";
$_ARRLANG['TXT_PRESS_REFRESH_TO_CONTINUE_INSTALLATION'] = "Press <b>Next</b> in order to proceed the installation steps!  ";
$_ARRLANG['TXT_REFRESH'] = "Update";
$_ARRLANG['TXT_CREATE_CONFIG_FILE'] = "Create configuration file";
$_ARRLANG['TXT_SET_WRITE_PERMISSION_TO_FILES'] = "You must assign write-permission to the following files:";
$_ARRLANG['TXT_CREATE_VERSION_FILE'] = "Create version file";
$_ARRLANG['TXT_COULD_NOT_GATHER_ALL_DATABASE_TABLES'] = "Could not find the available database tables!";
$_ARRLANG['TXT_NO_DB_UTF8_SUPPORT_MSG'] = "Your database server doesn't support the character set UTF-8! You need the a version of Contrexx which uses the latin1 character set instead!";

// system configuration
$_ARRLANG['TXT_ADMIN_EMAIL'] = "Administrator email address ";
$_ARRLANG['TXT_ADMIN_NAME'] = "Administrator name";
$_ARRLANG['TXT_NEWS_SYTEM'] = "News system";
$_ARRLANG['TXT_RSS_TITLE'] = "RSS title";
$_ARRLANG['TXT_RSS_DESCRIPTION'] = "RSS description";
$_ARRLANG['TXT_CONTACT'] = "Contact form";
$_ARRLANG['TXT_CONTACT_EMAIL'] = "Email address";
$_ARRLANG['TXT_COULD_NOT_SET_ADMIN_EMAIL'] = "Could not set the administrator email address!  ";
$_ARRLANG['TXT_COULD_NOT_SET_NEWSLETTER_EMAILS'] = "Could not configure email addresses in the newsletter module!  ";
$_ARRLANG['TXT_COULD_NOT_SET_NEWSLETTER_SENDER'] = "The module was not able to set the sender name in the newsletter! ";
$_ARRLANG['TXT_COULD_NOT_SET_ADMIN_NAME'] = "Could not set the administrator name!  ";
$_ARRLANG['TXT_COULD_NOT_SET_RSS_TITLE'] = "Could not set the RSS title!  ";
$_ARRLANG['TXT_COULD_NOT_SET_RSS_DESCRIPTION'] = "Failed to set the RSS description!  ";
$_ARRLANG['TXT_COULD_NOT_SET_CONTACT_EMAIL'] = "Could not set the email address of the contact forms!  ";
$_ARRLANG['TXT_DOMAIN_URL'] = "Domain URL";
$_ARRLANG['TXT_DOMAIN_URL_EXPLANATION'] = "Provide here the domain name for the installation steps, for example 'www.yourdomain.com' (without http: // or additional paths)";
$_ARRLANG['TXT_COULD_NOT_SET_DOMAIN_URL'] = "Could not set the domain URL!  ";
$_ARRLANG['TXT_SET_VALID_DOMAIN_URL'] = "Provide the domain URL without 'http://' or additional path information!  ";
$_ARRLANG['TXT_SETTINGS_ERROR_WRITABLE'] = "The file %s could not be written.  Review you the file access premission (e.g. 666). ";

// admin account
$_ARRLANG['TXT_ADMIN_ACCOUNT_DESC'] = "Provide an administrator's user name and password.  ";
$_ARRLANG['TXT_SET_USERNAME'] = "You must define a user name!  ";
$_ARRLANG['TXT_SET_PASSWORD'] = "You must define a password!";
$_ARRLANG['TXT_PASSWORD_LENGTH_DESC'] = "(min. 6 characters)";
$_ARRLANG['TXT_PASSWORD_LENGTH'] = "Password must be at least 6 characters in length!  ";
$_ARRLANG['TXT_PASSWORD_NOT_VERIFIED'] = "The confirmation password does not match the password above!  ";
$_ARRLANG['TXT_PASSWORD_VERIFICATION'] = "Confirm password";
$_ARRLANG['TXT_EMAIL'] = "Email";
$_ARRLANG['TXT_EMAIL_VERIFICATION'] = "Confirm email";
$_ARRLANG['TXT_SET_EMAIL'] = "You must provide a valid email address!  ";
$_ARRLANG['TXT_EMAIL_NOT_VERIFIED'] = "The confirmation email address does not match the email address above!  ";
$_ARRLANG['TXT_PASSWORD_LIKE_USERNAME'] = "The password must not be similar or the same as the user name!  ";
$_ARRLANG['TXT_CREATE_ADMIN_ACCOUNT'] = "Create administrator account";
$_ARRLANG['TXT_COULD_NOT_CREATE_ADMIN_ACCOUNT'] = "Could not create the administrator account!";

// termination
$_ARRLANG['TXT_CONGRATULATIONS'] = "Congratulation";
$_ARRLANG['TXT_CONGRATULATIONS_MESSAGE'] = "The Contrexx WCMS [VERSION] [EDITION] was installed successfully on your system.  ";
$_ARRLANG['TXT_INTERNET_SITE_FOR_VISITORS'] = "Internet site for visitors";
$_ARRLANG['TXT_INTERNET_SITE_MESSAGE'] = "The Web site is available immediately at the following address: <br /><b>[WEB_URL]</b>";
$_ARRLANG['TXT_ADMIN_SITE'] = "Administrator area";
$_ARRLANG['TXT_ADMIN_SITE_MESSAGE'] = "The site can be administered at the following address: <br /><b>[ADMIN_URL]</b>";
?>
