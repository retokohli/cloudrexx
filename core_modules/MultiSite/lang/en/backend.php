<?php
global $_ARRAYLANG;

// Let's start with module info:
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE'] = 'MultiSite';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DESCRIPTION'] = 'MultiSite erlaubt die Erstellung mehrerer unabhängiger Webseiten mit einer einzigen Installation von Contrexx';

// configuration options
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEPATH'] = 'Websites path';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEPATH_TOOLTIP'] = 'The Websites path specifies the absolute path in the file system where the data-directories of the websites are stored. Do specify the path without a trailing slash. I.e.: /var/www/websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNAVAILABLEPREFIXES'] = 'Unavailable website names';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITENAMEMAXLENGTH'] = 'Maximal length of website names';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITENAMEMINLENGTH'] = 'Minimal length of website names';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SENDSETUPERROR'] = 'Send setup error reports';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SENDSETUPERROR_TOOLTIP'] = 'Activate to send error reports of the <i>Website setup process</i> to the email address specified by option <i>Email of administrator</i> in tab <i>Contact Information</i> in section <i>Administration > Global Configuration</i>.';
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
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MARKETINGWEBSITEDOMAIN']='Domain of Marketing Website';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MARKETINGWEBSITEDOMAIN_TOOLTIP']='Set the Domain where the <i>Marketing Website</i> of the MultiSite system is located. Invalid HTTP-Requests to this Contrexx installation will automatically be forwarded to the given Domain.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DASHBOARDNEWSSRC']='URL to dashboard news RSS feed';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CUSTOMERPANELDOMAIN']='Domain of Customer Panel';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CUSTOMERPANELDOMAIN_TOOLTIP']='Set the Domain where the <i>Customer Pnael</i> of the MultiSite system is located.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKHOST']='Hostname of Plesk server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKLOGIN']='Login to Plesk server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKPASSWORD']=' Password to Plesk server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKMASTERSUBSCRIPTIONID']='ID of master subscription';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKMASTERSUBSCRIPTIONID_TOOLTIP']='Specify the <b>ID</b> of the <i>Plesk Subscription</i> by which this Contrexx installation is managed by.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_OPTIONS_SET_BY_WEBSITE_MANAGER'] =' - Those options are set by the Website Manager';

$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITECONTROLLER']='Subscription controller';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTWEBSITESERVICESERVER']='Default Website Service Server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTMAILSERVICESERVER']='Default Mail Service Server';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTPIMPRODUCT'] = 'Default Product on Sign-Up';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKWEBSITESSUBSCRIPTIONID']='ID of website subscription';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PLESKWEBSITESSUBSCRIPTIONID_TOOLTIP']='Specify the <b>ID</b> of the <i>Plesk Subscription</i> by which the databases of the <i>Website Websites</i> shall be managed by.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHOSTNAME']='Website Manager hostname';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHOSTNAME_TOOLTIP']='Specify the <b>hostname</b> of the <i>Website Manager Server</i> by which this <i>Website Service Server</i> is managed by. The hostname must be set to the <i>Main Domain</i> of the <i>Website Manager Server</i>.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERSECRETKEY']='Secret Key of Website Manager';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERINSTALLATIONID']='Installation-ID of Website Manager';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHMETHOD']='HTTP Authentication Method';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHUSERNAME']='HTTP Authentication Username';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHPASSWORD']='HTTP Authentication Password';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEHOST'] = 'Database host for websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEDATABASEHOST_TOOLTIP'] = 'Set the hostname (or IP address) of the database server that shall be used for the databases of the Websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHOSTNAME'] = 'Website Service hostname';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICESECRETKEY'] = 'Secret Key of Website Service';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEINSTALLATIONID'] = 'Installation-ID of Website Service';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHTTPAUTHMETHOD'] = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHMETHOD'];
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHTTPAUTHUSERNAME'] = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHUSERNAME'];
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SERVICEHTTPAUTHPASSWORD'] = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MANAGERHTTPAUTHPASSWORD'];
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITESTATE'] = 'Status';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITENAME']  = 'Website Name';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEFTPUSER']  = 'Websites FTP user';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CODEBASEREPOSITORY'] = 'Repository for Contrexx Code Bases';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEFTPPATH'] = 'Websites FTP path';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEHTTPAUTHMETHOD'] = "HTTP Authentication Method";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEHTTPAUTHUSERNAME'] = "HTTP Authentication Username";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEHTTPAUTHPASSWORD'] = "HTTP Authentication Password";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEUSERID'] = "User ID of Website Owner";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_TERMSURL'] = "URL to T&Cs"; 
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_TERMSURL_TOOLTIP'] = "The absolute URL (incl. protocol) to the webpage listing the terms & conditions";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CREATEFTPACCOUNTONSETUP'] = "Create FTP account during website setup";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PASSWORDSETUPMETHOD'] = "Password set method";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTWEBSITETEMPLATE'] = 'Default Website Template for Sign-Up';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_AUTOLOGIN'] = 'Auto Login after sign-up';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_AUTOLOGIN_TOOLTIP'] = 'Automatically log user in after successfull sign-up process and redirect user to backend of newly created website.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_FTPACCOUNTFIXPREFIX'] = 'FTP account name prefix';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_FTPACCOUNTFIXPREFIX_TOOLTIP'] = 'Define a prefix that shall be used for the FTP account name in case the Website name is not a valid FTP account name. I.e.: cx';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_FORCEFTPACCOUNTFIXPREFIX'] = 'Force FTP account name prefix';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_FORCEFTPACCOUNTFIXPREFIX_TOOLTIP'] = 'Prefix all FTP account user names with the prefix defined by option '.$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_FTPACCOUNTFIXPREFIX'];
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUPPORTFAQURL'] = 'URL to FAQ webpage';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SUPPORTRECIPIENTMAILADDRESS'] = 'E-Mail recipient address for support requests';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAXLENGTHFTPACCOUNTNAME'] = 'Max. length of FTP account name';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PAYREXXACCOUNT'] = 'Payrexx Account';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PAYREXXFORMID'] = 'Payrexx Form Id';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_PAYREXXAPISECRET'] = 'Payrexx API Secret';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITEBACKUPLOCATION'] = 'Website Backup Location';

// settings status messages
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTCODEBASE_SUCCESSFUL_CREATION']      = "Default Code Base has been set successfully";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_CHANGED_SUCCESSFUL']      = "Website state has been set successfully";


// Here come the ACTs:
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_DEFAULT'] = 'Websites';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_STATISTICS'] = 'Statistics';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_NOTIFICATIONS'] = 'Notifications';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS'] = 'Settings';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_DEFAULT'] = 'Default';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_EMAIL'] = 'E-mails';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_WEBSITE_SERVICE_SERVERS'] = 'Website Service Servers';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_CODEBASES'] = 'Code Bases';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_WEBSITE_TEMPLATES'] = 'Website Templates';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_SETTINGS_MAIL_SERVICE_SERVERS'] = 'Mail Service Servers ';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ACT_DOMAINS']= 'Domains';

// Sign up interface
$_ARRAYLANG['TXT_MULTISITE_TITLE']='Get started with your own website now';
$_ARRAYLANG['TXT_MULTISITE_CLOSE']='Close';
$_ARRAYLANG['TXT_MULTISITE_EMAIL_ADDRESS']='Email Address';
$_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS']='Site Address';
$_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS_SCHEME']='The name must contain only lowercase letters (a-z) and numbers and must be at least %1$s characters, but no longer than %2$s characters.';
$_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON']='Create Site';
$_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS']='I confirm that I have read, understand and agree to the %s.';
$_ARRAYLANG['TXT_MULTISITE_ACCEPT_TERMS_URL_NAME']='Terms & Conditions';
$_ARRAYLANG['TXT_MULTISITE_EMAIL_INFO']='We\'ll send you an email to this address to activate your account.';
$_ARRAYLANG['TXT_MULTISITE_ORDER_BUTTON']='Order now';

// Sign up status messages
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_ALREADY_EXISTS'] = 'The name %s has been taken already. Please choose an other name for your own site.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_SHORT'] = 'Site name must be at least %s characters.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_LONG'] = 'Site name can be no longer than %s characters.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_WRONG_CHARS'] = 'The name must contain only lowercase letters (a-z) and numbers.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EMAIL_IN_USE'] = 'That email is already registered - %s.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL'] = 'The entered email address is invalid.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LOGIN'] = 'Log in';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATED'] = 'Congratulations! Your website %s is ready! Please check your inbox (ended up in spam folder?). We sent an email to you with your credentials and first hints. Good luck.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATION_ERROR'] = 'We were unable to setup your website. A technician will immediately address the issue and will inform you on the email address %s as soon as your website is ready for use.';
$_ARRAYLANG['TXT_MULTISITE_BUILD_WEBSITE_TITLE'] = 'Building your Website..';
$_ARRAYLANG['TXT_MULTISITE_BUILD_SUCCESSFUL_TITLE'] = 'Congratulations!';
$_ARRAYLANG['TXT_MULTISITE_BUILD_ERROR_TITLE'] = 'Oops..';
$_ARRAYLANG['TXT_MULTISITE_BUILD_WEBSITE_MSG'] = 'This might take a few minutes. You will be informed on the supplied email address once your website is online and ready to use.';
$_ARRAYLANG['TXT_MULTISITE_REDIRECT_MSG'] = 'Your website is ready. You are being redirected to the administration interface now...';
// TODO add english translation
$_ARRAYLANG['TXT_MULTISITE_BUILD_WEBSITE_MSG_AUTO_LOGIN'] = 'Vielen Dank für Ihre Anmeldung bei Cloudrexx! Bitte haben Sie etwas Geduld. Es kann bis zu 2 Minuten dauern, bis Ihre Website einsatzbereit ist. Sobald Ihre Website einsatzbereit ist, werden Sie in den Administrationsbereich weitergeleitet. Sie sind automatisch eingeloggt. Das Kennwort wird an %1$s gesendet. Ihre Cloudrexx-Domain lautet %2$s.';
$_ARRAYLANG['TXT_MULTISITE_BUILD_ERROR_MSG'] = 'Unfortunately, the build process of your website failed. If this did happen before, please contact our customer service under %s.';

//Reset FTP password status message
$_ARRAYLANG['TXT_MULTISITE_RESET_FTP_PASS_ERROR_MSG'] = 'Resetting your FTP password failed! Try again.';
$_ARRAYLANG['TXT_MULTISITE_RESET_FTP_PASS_MSG'] = 'Your FTP password has been reset successfully.';

//To execute sql query
$_ARRAYLANG['TXT_MULTISITE_EXECUTE_QUERY_ON_WEBSITE'] = 'Execute SQL query on Website ';
$_ARRAYLANG['TXT_MULTISITE_EXECUTE_QUERY_ON_ALL_WEBSITES_OF_SERVICE_SERVER'] = 'Execute SQL query on all websites running on service server ';
$_ARRAYLANG['TXT_MULTISITE_EXECUTED_QUERY_COMPLETED'] = 'SqlQuery Execution Completed!';
$_ARRAYLANG['TXT_MULTISITE_EXECUTED_QUERY_FAILED'] = 'SqlQuery Executed failed!';
$_ARRAYLANG['TXT_MULTISITE_QUERY_EXECUTED_ON_WEBSITE'] = 'SqlQuery executed on website : ';
$_ARRAYLANG['TXT_MULTISITE_SQL_QUERY'] = 'SqlQuery';
$_ARRAYLANG['TXT_MULTISITE_SQL_STATUS'] = 'SqlStatus';
$_ARRAYLANG['TXT_MULTISITE_PLEASE_INSERT_QUERY'] = 'Please insert a query!';
$_ARRAYLANG['TXT_MULTISITE_SQL_QUERY_EXECUTED_SUCCESSFULLY'] = 'Query executed successfully.';
$_ARRAYLANG['TXT_MULTISITE_SQL_QUERY_EXECUTED_FAILED'] = 'Query execution failed.';
$_ARRAYLANG['TXT_MULTISITE_FETCH_LICENSE_INFO'] = 'Fetch license information of website:';
$_ARRAYLANG['TXT_MULTISITE_LICENSE_DATA_TITLE'] = 'License data of website ';
$_ARRAYLANG['TXT_MULTISITE_SHOW_LICENSE'] = 'Show License';
$_ARRAYLANG['TXT_MULTISITE_LICENSE_INFO'] = 'License information for the selected website';
$_ARRAYLANG['TXT_MULTISITE_QUERY_IS_EMPTY'] = 'JsonMultiSite : sql query is empty..';
$_ARRAYLANG['TXT_MULTISITE_NO_RECORD_FOUND'] = 'No record found.';

//filter
$_ARRAYLANG['TXT_MULTISITE_SEARCH'] = 'Search';
$_ARRAYLANG['TXT_MULTISITE_FILTER'] = 'Filter';
$_ARRAYLANG['TXT_MULTISITE_ENTER_SEARCH_TERM'] = 'Enter your search term';
$_ARRAYLANG['TXT_MULTISITE_NO_ROWS_AFFECTED'] = " row(s) %s successfully.";

//Multisite website status message
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_TITLE']             = "Multisite configuration data of a website ";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_FETCH_SUCCESSFUL']  = "Multisite configuration Of the website:%s !";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_FETCH_FAILED']      = "Failed to %s the multisite configuration of the website: %s !";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_UPDATE_SUCCESSFUL'] = "Successfully updated the multisite configuration option:";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_UPDATE_FAILED']     = "Failed to update the multisite configuration option:";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_TITLE'] = 'Add new multisite configuration option: ';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG'] = 'Add new configuration';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_SUCCESSFUL'] = 'Successfully added the multisite configuration option: ';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_FAILED'] = 'Failed to add configuration Option due to empty option values!';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_OPTION_TOOLTIP'] = 'Option Values must be comma seperated values. <br /> Each option mustbe optionValue:optionName.<br/> For Example Activated => on:Activated';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DELETE_CONFIG_OPTION'] = 'Do you really want to delete this configuration?';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_DELETE_SUCCESSFUL'] = 'Successfully deleted the multisite configuration option: ';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_DELETE_FAILED'] = 'Failed to delete the multisite configuration option: ';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CONFIG_ALERT_MESSAGE'] = 'Making a change on the website configuration might break this selected website! You must not alter the configuration unless you know exactly what you are doing!';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_CONFIG_EXISTS'] = 'Failed to add configuration : %s .Option already exists!';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_FETCH_LICENSE_FAILED'] = 'Failed to get license information of website: %s !';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LICENSE_UPDATE_SUCCESS'] = 'The license option  "%s"  was successfully updated!';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LICENSE_UPDATE_FAILED'] = 'Failed to update The license Option "%s"  !';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_REMOTE_LOGIN_SUCCESS'] = 'Successfully login to website: %s !';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_WEBSITE_SUCCESS'] = "The Website has been successfully added.";
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD_WEBSITE_FAILED'] = "Failed to adding a new website.";

//cron mail error message
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CRON_MAIL_CRITERIA_EMPTY'] = "Please provide some conditions to setup the cron mail.";

//website template message
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SETTINGS_WEBSITE_TEMPLATE_HEADER_MSG'] = ' - This section is managed by the Website Manager';

$_ARRAYLANG['TXT_MULTISITE_WEBSITE_BUTTON_VIEW'] = 'View';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_BUTTON_ADMIN_CONSOLE'] = 'admin console';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_BUTTON_DEACTIVATE'] = 'Deactivate';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_BUTTON_DELETE'] = 'Delete';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_TITLE_ACCOUNT'] = 'Account';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_TITLE_NAME'] = 'Name';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_TITLE_EMAIL'] = 'E-Mail';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_TITLE_FUNCTIONS'] = 'Functions';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_ADD_ACCOUNT'] = 'Add Account';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_ADD_DOMAIN'] = 'Add domain';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_TITLE_USED_LIMIT'] = 'Used / Limit';
$_ARRAYLANG['TXT_MULTISITE_UNLIMITED'] = 'Unlimited';

//website subscription detail template message
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_NOACCESS'] = 'Access denied';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTIONID_EMPTY'] = 'Unknown subscription requested.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION_NOT_EXISTS'] = 'Unknown subscription requested.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCT_NOT_EXISTS'] = 'Unknown product requested.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_ORDER_NOT_EXISTS'] = 'Unknown order requested.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_EXISTS'] = 'Unknown website requested.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_NOT_MULTISITE_USER'] = 'Access denied';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_WEBSITES'] = 'Websites';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_SUBSCRIPTION_PAYMENTS'] = 'Payments';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_PAYREXX_LOGIN_FAILED'] = 'The Payrexx management console is currently not available.';

$_ARRAYLANG['TXT_MULTISITE_NO_WEBSITE_FOUND'] = 'Websites not available';
$_ARRAYLANG['TXT_MULTISITE_NOT_VALID_USER'] = 'Not a multisite user';
$_ARRAYLANG['TXT_MULTISITE_UNKOWN_WEBSITE'] = 'Unknown website requested.';

$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAXIMUM_QUOTA_REACHED'] = 'You have reached the maximum of %s entities that are included in your current plan.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_TEMPLATE_FAILED'] = 'Failed to load a website template.';

$_ARRAYLANG['TXT_MULTISITE_SUBSCRIPTION_PAYMENT_FAILED']  = 'The upgrade of the subscription is getting canceled.';
$_ARRAYLANG['TXT_MULTISITE_SUBSCRIPTION_INVALIDPARAMETERS']  = 'Invalid parameters.please try after sometime.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ORDER_FAILED']  = 'Unable to create the order.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UPGRADE_SUCCESS']  = 'The upgrade of your subscription was successful.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UPGRADE_FAILED']  = 'The upgrade of the subscription failed.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_PRODUCTS_NOT_FOUND']  = 'There are no upgrades available for the selected subscription.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_INITIALIZING'] = 'Initializing...';

//website email service
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_ACTIVATE_MAIL_SERVICE'] = 'Activate mail service';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_DEACTIVATE_MAIL_SERVICE'] = 'Deactivate mail service';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_ENABLED_SUCCESSFULLY'] = 'Mail service successfully activated.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_DISABLED_SUCCESSFULLY'] = 'Mail service successfully deactivated.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_ENABLED_FAILED'] = 'Mail service activation failed.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_MAIL_DISABLED_FAILED'] = 'Mail service deactivation failed.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_OPEN_ADMINISTRATION'] = 'Open administration';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_LOGIN_PLESK_FAILED'] = 'The email management console is currently not available.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_CREATE_MAIL_ACCOUNT_FAILED'] = 'Failed to create an email account.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_DELETE_MAIL_ACCOUNT_FAILED'] = 'Failed to delete the email account.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_CREATED_MAIL_ACCOUNT_SUCCESSFULLY'] = 'Email account successfully created.';
$_ARRAYLANG['TXT_MULTISITE_WEBSITE_DELETED_MAIL_ACCOUNT_SUCCESSFULLY'] = 'Email account successfully removed.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CANCEL'] = 'Cancel';

//website domain
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADD'] = 'Add';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_SAVE'] = 'Save';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DELETE'] = 'Delete';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_ADD_SUCCESS_MSG'] = 'The domain has been added successfully.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_EDIT_SUCCESS_MSG'] = 'The domain has been updated successfully.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_DELETE_SUCCESS_MSG'] = 'The domain has been deleted successfully.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_ADD_FAILED'] = 'Failed to add the domain.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_EDIT_FAILED'] = 'Failed to edit the domain.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_DELETE_FAILED'] = 'Failed to delete the domain.';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_DOMAIN_UNKNOWN'] = 'Unknown domain requested.';

$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_DELETE_SUCCESS']  = 'Successfully deleted the selected admin user';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_DELETE_FAIL']     = 'Failed to delete the selected admin user';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_ADD_SUCCESS']     = 'Successfully Added the admin user';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_ADD_FAIL']        = 'Failed to add a admin user';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_EMAIL_EMPTY']     = 'Email id is empty';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_ADMIN_USER_NOT_VALID_EMAIL'] = 'Email id is not a valid email';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_UNKOWN_USER_REQUEST']        = 'Unknown user requested';

$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EXTERNALPAYMENTCUSTOMERIDPROFILEATTRIBUTEID'] = 'Payment user profile attribute ID';
$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EXTERNALPAYMENTCUSTOMERIDPROFILEATTRIBUTEID_TOOLTIP']='The user profile attribute Id used to store the customer ID of the external payment provider.';