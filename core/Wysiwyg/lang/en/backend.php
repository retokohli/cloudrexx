<?php
/**
 * This is the english language file for backend mode.
 * This file is included by Contrexx and all entries are set as placeholder
 * values for backend ACT template by SystemComponentBackendController
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_wysiwyg
 */

global $_ARRAYLANG;

// Let's start with module info:
$_ARRAYLANG['TXT_CORE_WYSIWYG'] = 'WYSIWYG';
$_ARRAYLANG['TXT_CORE_WYSIWYG_DESCRIPTION'] = 'With this module you can manage the wysiwyg templates';

// Here come the ACTs:
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_DEFAULT'] = 'Overview';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYG_TEMPLATE'] = 'WYSIWYG Templates';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYG_TEMPLATE_TOOLTIP'] = 'Click here to create a content template , which you can select in the Editor under Templates later.';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_SETTINGS_DEFAULT'] = 'Mailing';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_SETTINGS_HELP'] = 'Help';
$_ARRAYLANG['TXT_CORE_WYSIWYG_TEMPLATE_ENTITY'] = 'WysiwygTemplate';

// Now our content specific values:
$_ARRAYLANG['TXT_CORE_WYSIWYG_CONGRATULATIONS'] = 'Overview';
$_ARRAYLANG['TXT_CORE_WYSIWYG_SUCCESSFUL_CREATION'] = 'This is the Overview/Dashboard of your new Component. More tabs will be generated if you add entities to this component.';
$_ARRAYLANG['TXT_CORE_WYSIWYG_EXAMPLE_TEMPLATE'] = 'This is the default template for this component, located in View/Template/Backend/Default.html. In order to add entities, place your YAML files in Model/Yaml folder and execute ./workbench.bat db update. Then add a language file entry for your entity.';

$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYGTEMPLATE'] = 'WYSIWYG Templates';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYGTEMPLATE_TITLE'] = 'Title';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYGTEMPLATE_DESCRIPTION'] = 'Description';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYGTEMPLATE_ACTIVE'] = 'Active';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYGTEMPLATE_IMAGE_PATH'] = 'Preview image';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYGTEMPLATE_HTML_CONTENT'] = 'HTML';
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_WYSIWYGTEMPLATE_STATE'] = 'State';

//Settings
$_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_SETTINGS'] = 'Settings';
$_ARRAYLANG['TXT_CORE_WYSIWYG_SPECIFICSTYLESHEET'] = 'Consideration of specific CSS';
$_ARRAYLANG['TXT_CORE_WYSIWYG_REPLACEACTUALCONTENTS'] = 'Replace actual content in the WYSIWYG';