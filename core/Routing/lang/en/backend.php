<?php
/**
 * @copyright   CONTREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@comvation.com>
 * @access      public
 * @package     contrexx
 * @subpackage  core_routing
 */
global $_ARRAYLANG;

// Act Variables
$_ARRAYLANG['TXT_CORE_ROUTING'] = 'Routing';
$_ARRAYLANG['TXT_CORE_ROUTING_ACT_DEFAULT'] = 'Overview';
$_ARRAYLANG['TXT_CORE_ROUTING_ACT_REDIRECT'] = 'Redirections';

$_ARRAYLANG['TXT_CORE_ROUTING_ACT_REWRITERULE'] = 'Rewrite rules';
$_ARRAYLANG['id'] = 'ID';
$_ARRAYLANG['regularExpression'] = 'Regular expression';
$_ARRAYLANG['orderNo'] = 'Order number';
$_ARRAYLANG['rewriteStatusCode'] = 'Redirection HTTP Statuscode';
$_ARRAYLANG['continueOnMatch'] = 'Continue after match';

$_ARRAYLANG['TXT_CORE_ROUTING_TITLE'] = 'Rewrite rules';
$_ARRAYLANG['TXT_CORE_ROUTING_INTRODUCTION'] = 'This functionality allows you to globally redirect requests to your website. This for example allows you to redirect request to another domain to a specific page. Here\'s an explanation of all the options:';
$_ARRAYLANG['TXT_CORE_ROUTING_EXPLANATION'] = 'The rules consist of a <a href="http://en.wikipedia.org/wiki/Regular_expression">regular expression</a>, which is used for simple search and replace on the request URL. All the rules are processed in the order of the order number. If a rule matches and "Continue after match" is set to "No" no more rules are processed. As soon as all rules are processed and at least one matched the redirection is made. The HTTP status code of the last matched rule is used for redirection.';
