<?php
global $_ARRAYLANG;

// Let's start with module info:
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEPATH'] = 'Websites path';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEPATH_TOOLTIP'] = 'The Websites path specifies the absolute path in the file system where the data-directories of the websites are stored. Do specify the path without a trailing slash. I.e.: /var/www/websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNAVAILABLEPREFIXES'] = 'Unavailable website names';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITENAMEMAXLENGTH'] = 'Maximal length of website names';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITENAMEMINLENGTH'] = 'Minimal length of website names';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MULTISITEPROTOCOL'] = 'MultiSite API Operation';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEPREFIX'] = 'Database prefix for websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEPREFIX_TOOLTIP'] = 'Set the database prefix that shall be used to address the databases of the website websites. The database prefix will also be used as the database-name prefix when creating a new website website. The database prefix must not exceed the maximal length of 54 characters and must follow the MySQL Identifier Scheme.
I.e.: cloudrexx_website_';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEUSERPREFIX'] = 'Database user prefix for websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEUSERPREFIX_TOOLTIP'] = 'The database user prefix will be used as the username-prefix when adding a new database user for a newly created website website. The database user prefix must not exceed the maximal length of 6 characters.
I.e.: clx_i_';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MODE']='MultiSite operation mode';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTWEBSITEIP']='defaultWebsiteIp';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MODE_TOOLTIP']='Select the mode in which the <i>MultiSite</i> component shall operate in.<br>Modes:<ul><li><b>none:</b> <i>MultiSite</i> functionality is not in use. This Contrexx installation will act as a regular website.</li><li><b>manager:</b> This Contrexx installation shall act as the <i>Website Manager Server</i>.</li><li><b>service:</b> This Contrexx installation shall act as a <i>Website Service Server</i>.</li><li><b>hybrid:</b> This Contrexx installation shall act as the <i>Website Manager Server</i> as well as a <i>Website Service Server</i>.</li></ul>';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTCODEBASE']='Default CodeBase';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTCODEBASE_TOOLTIP']='Specify the path to the <b>CodeBase</b> that shall be used for new <i>Website Websites</i>.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MULTISITEDOMAIN']='Domain of MultiSite system';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MULTISITEDOMAIN_TOOLTIP']='Set the domain that shall be used by the MultiSite system. New Websites will be created as subdomains of the specified domain. I.e.: if this option is set to <b>example.com</b>,then a new Website, called <i>foo</i>, will be accessable through the subdomain <b>foo.example.com</b>.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKHOST']='Hostname of Plesk server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKLOGIN']='Login to Plesk server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKPASSWORD']=' Password to Plesk server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKIP']='IP-Address for webspaces';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKMASTERSUBSCRIPTIONID']='ID of master subscription';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKMASTERSUBSCRIPTIONID_TOOLTIP']='Specify the <b>ID</b> of the <i>Plesk Subscription</i> by which this Contrexx installation is managed by.';

$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITECONTROLLER']='Subscription controller';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKWEBSITESSUBSCRIPTIONID']='ID of website subscription';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKWEBSITESSUBSCRIPTIONID_TOOLTIP']='Specify the <b>ID</b> of the <i>Plesk Subscription</i> by which the databases of the <i>Website Websites</i> shall be managed by.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHOSTNAME']='Website Manager hostname';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHOSTNAME_TOOLTIP']='Specify the <b>hostname</b> of the <i>Website Manager Server</i> by which this <i>Website Service Server</i> is managed by.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERSECRETKEY']='Secret Key of Website Manager';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERINSTALLATIONID']='Installation-ID of Website Manager';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHMETHOD']='HTTP Authentication Method';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHUSERNAME']='HTTP Authentication Username';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHPASSWORD']='HTTP Authentication Password';


$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_DEFAULT'] = 'Default';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'] = 'MultiSite';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DESCRIPTION'] = 'MultiSite erlaubt die Erstellung mehrerer unabhängiger Webseiten mit einer einzigen Installation von Contrexx';

$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEHOST'] = 'Database host for websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEHOST_TOOLTIP'] = 'Set the hostname (or IP address) of the database server that shall be used for the databases of the Websites';

$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHOSTNAME'] = 'Website Service hostname';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICESECRETKEY'] = 'Secret Key of Website Service';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEINSTALLATIONID'] = 'Installation-ID of Website Service';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHTTPAUTHMETHOD'] = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHMETHOD'];
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHTTPAUTHUSERNAME'] = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHUSERNAME'];
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHTTPAUTHPASSWORD'] = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHPASSWORD'];
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITESTATE'] = 'Status';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CODEBASEREPOSITORY'] = 'Repository for Contrexx Code Bases';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTCODEBASE_SUCCESSFUL_CREATION']      = "Default Code Base has been set successfully";

// Here come the ACTs:
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_DEFAULT'] = 'Websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_STATISTICS'] = 'Statistics';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS'] = 'Settings';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_EMAIL'] = 'E-mails';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_WEBSITE_SERVICE_SERVERS'] = 'Website Service Servers';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_CODEBASES'] = 'Code Bases';

// Now our content specific values:
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_ALREADY_EXISTS'] = 'The name %s has been taken already. Please choose an other name for your own site.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_SHORT'] = 'Site name must be at least %s characters.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_LONG'] = 'Site name can be no longer than %s characters.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_WRONG_CHARS'] = 'The name must contain only lowercase letters (a-z) and numbers.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EMAIL_IN_USE'] = 'That email is already registered - %s.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LOGIN'] = 'Log in';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATED'] = 'Thank you for the registration! Please check your inbox (ended up in spam folder?). We sent an email to you with your credentials and first hints. Good luck.';

// Registration Module
$_ARRAYLANG['TXT_MULTISITE_TITLE']='Get started with your own website now';
$_ARRAYLANG['TXT_MULTISITE_EMAIL_ADDRESS']='Email Address';
$_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS']='Site Address';
$_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS_SCHEME']='The name must contain only lowercase letters (a-z) and numbers and must be at least %1$s characters, but no longer than %2$s characters.';
$_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON']='Create Site';

//http text
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEHTTPAUTHMETHOD'] = "HTTP Authentication Method";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEHTTPAUTHUSERNAME'] = "HTTP Authentication Username";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEHTTPAUTHPASSWORD'] = "HTTP Authentication Password";

//website user Id
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEUSERID'] = "User ID of Website Owner";
