<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * This is the english language file for backend mode.
 * This file is included by Cloudrexx and all entries are set as placeholder
 * values for backend ACT template by SystemComponentBackendController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_chdirtravellog
 */

global $_ARRAYLANG;

// Let's start with module info:
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG'] = 'CHDIRTravelLog';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_DESCRIPTION'] = 'This is a new module with some sample content to show how to start.';

// Here come the ACTs:
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ACT_DEFAULT'] = 'Overview';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ACT_SETTINGS'] = 'Settings';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ACT_SETTINGS_DEFAULT'] = 'General';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ACT_SETTINGS_HELP'] = 'Mailing';

// Now our content specific values:
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_CONGRATULATIONS'] = 'Overview';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_SUCCESSFUL_CREATION'] = 'This is the Overview/Dashboard of your new Component. More tabs will be generated if you add entities to this component.';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXAMPLE_TEMPLATE'] = 'This is the default template for this component, located in View/Template/Backend/Default.html. In order to add entities, place your YAML files in Model/Yaml folder and execute ./cx workbench database update. Then add a language file entry for your entity.';

$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_PROJECT_NAME'] = 'Projekt Name';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_LAST_SYNC_TIME'] = 'Zeitstempel der letzten Synchronisation';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_DATA_FOLDER'] = 'Verzeichnis der Datenablage';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_PDF_FOLDER'] = 'Verzeichnis für generierte PDF Dokumente';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_CSV_DELIMITER'] = 'CSV Import: Trennzeichen';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_CSV_ENCLOSURE'] = 'CSV Import: Umschliessung';
$_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_CSV_ESCAPE'] = 'CSV Import: Escape';
