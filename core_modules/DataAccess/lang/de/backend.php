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
 * @copyright   Cloudrexx AG
 * @author Sam Hawkes <info@cloudrexx.com>
 * @package cloudrexx
 * @subpackage core_modules_dataaccess
 */
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS'] = 'RESTful API';
$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS'] = 'RESTful API';
$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_DESCRIPTION'] = 'Die Cloudrexx RESTful API ermöglich lesenden und schreibenden Zugriff auf Ihre Website-Daten durch Drittsysteme.';
$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_INTRODUCTION'] = 'Auf die zur Verfügung stehenden Daten kann (abhängig vom Funktionsumfang Ihrer Website) über API-Endpunkte zugegriffen werden. Der Zugriff darauf wird über API-Schlüssel geregelt. Ein API-Schlüssel kann lesenden und/oder schreibenden Zugriff auf einen oder mehrere API-Endpunkte erlauben. Es können mehrere API-Schlüssel mit unterschiedlichen Zugriffskonfigurationen angelegt werden. Fügen Sie nun Ihren ersten API-Schlüssel hinzu, um Ihre Website-Daten für ein Drittsystem zugänglich zu machen.<br /><br /><a href="/cadmin/DataAccess/ApiKey?add=1" class="button">API-Schlüssel hinzufügen</a>';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR'] = 'Exception of type "%s" with message "%s"';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR_NO_DATA_ACCESS'] = 'Der Enpdunkt konnte nicht gefunden werden.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR_NO_DATA_SOURCE'] = 'Die Daten-Ressource konnte nicht gefunden werden.';

$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_ACT_APIKEY'] = 'API-Schlüssel';
$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_ACT_DATAACCESS'] = 'Endpunkte';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_PLEASE_CHOOSE'] = 'Bitte wählen';

$_ARRAYLANG['id'] = 'ID';
$_ARRAYLANG['apiKey'] = 'API-Schlüssel';
$_ARRAYLANG['dataAccessApiKeys'] = 'Endpunkte';
$_ARRAYLANG['dataAccessReadOnly'] = 'Endpunkte nur mit Leseberechtigung';

$_ARRAYLANG['name'] = 'Name';
$_ARRAYLANG['fieldList'] = 'Erlaubte Attribute';
$_ARRAYLANG['accessCondition'] = 'Bedingungen';
$_ARRAYLANG['allowedOutputMethods'] = 'Erlaubte Ausgabemodule';

$_ARRAYLANG['protocols'] = 'Erlaubte Protokolle';
$_ARRAYLANG['methods'] = 'Erlaubte Methoden';
$_ARRAYLANG['userGroups'] = 'Benutzergruppen';
$_ARRAYLANG['accessIds'] = 'Access IDs';
$_ARRAYLANG['callbacks'] = 'Callback Methoden';
$_ARRAYLANG['readPermission'] = 'Leseberechtigung';
$_ARRAYLANG['writePermission'] = 'Schreibberechtigung';
$_ARRAYLANG['requiresLogin'] = 'Benötigt Login';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_REQUIRES_LOGIN'] = 'Ist ein Benutzer im Adminbereich oder in einem geschützen Webseitenbereich eingeloggt, ist diese Bedingung erfüllt.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_ACCESS_IDS'] = 'Sind keine Access IDs ausgewählt, sind alle zugelassen.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_OUTPUT_METHODS'] = 'Wie die Daten ausgegeben werden sollen, Cli bietet eine tabellarische Ausgabe.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_ALLOWED_FIELDS'] = 'Sind keine Attribute ausgewählt, sind alle zugelassen.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_USER_GROUPS'] = 'Sind keine Benutzergruppen ausgewählt, sind alle zugelassen.';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_SELECT'] = 'Der API-Schlüssel gewährt Lese- und Schreibberechtigungen auf diese Endpunkte.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_SELECT_READ_ONLY'] = 'Der API-Schlüssel gewährt ausschliesslich Leseberechtigungen auf diese Endpunkte.';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_GENERATE_BTN'] = 'Generiere API-Schlüssel';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_YES'] = 'Ja';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_NO'] = 'Nein';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_API_KEY_ALREADY_EXISTS'] = 'Ein Eintrag mit diesem API-Schlüssel exisitert bereits';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_COULD_NOT_STORE_APIKEY'] = 'Der API-Schlüssel konnte nicht gespeichert werden';
