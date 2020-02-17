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
$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_DESCRIPTION'] = 'Cloudrexx RESTful API allows read and write access to data from third-party systems.';
$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_INTRODUCTION'] = 'The different types of data can be accessed using endpoints. Access is granted using API keys. Each API key can grant read and/or write access to one or more endpoints. In order to begin using the API create an API key.<br /><br /><a href="/cadmin/DataAccess/ApiKey?add=1" class="button">Create API key</a>';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR'] = 'Exception of type "%s" with message "%s"';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR_NO_DATA_ACCESS'] = 'The endpoint could not be found.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR_NO_DATA_ACCESS'] = 'The data-ressource could not be found.';

$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_ACT_APIKEY'] = 'API Key';
$_ARRAYLANG['TXT_CORE_MODULE_DATAACCESS_ACT_DATAACCESS'] = 'Endpoints';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_PLEASE_CHOOSE'] = 'Please choose';

$_ARRAYLANG['id'] = 'ID';
$_ARRAYLANG['apiKey'] = 'API Key';
$_ARRAYLANG['dataAccessApiKeys'] = 'Endpoints';
$_ARRAYLANG['dataAccessReadOnly'] = 'Endpoints with read-only permission only';

$_ARRAYLANG['name'] = 'Name';
$_ARRAYLANG['fieldList'] = 'Allowed attributes';
$_ARRAYLANG['accessCondition'] = 'Conditions';
$_ARRAYLANG['allowedOutputMethods'] = 'Allowed output methods';

$_ARRAYLANG['protocols'] = 'Allowed Protocols';
$_ARRAYLANG['methods'] = 'Allowed methods';
$_ARRAYLANG['userGroups'] = 'User groups';
$_ARRAYLANG['accessIds'] = 'Access IDs';
$_ARRAYLANG['callbacks'] = 'Callback methods';
$_ARRAYLANG['readPermission'] = 'Read permission';
$_ARRAYLANG['writePermission'] = 'Write permission';
$_ARRAYLANG['requiresLogin'] = 'Requires login';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_REQUIRES_LOGIN'] = 'If a user is logged in in the admin area or in a protected web page area, this condition is fulfilled.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_ACCESS_IDS'] = 'If no Access IDs are selected, all are allowed.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_OUTPUT_METHODS'] = 'How the data should be output, Cli offers a tabular output. ';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_ALLOWED_FIELDS'] = 'If no attributes are selected, all are allowed. ';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_USER_GROUPS'] = 'If no user groups are selected, all are allowed.';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_SELECT'] = 'The API key grants read and write permissions to these endpoints.';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_INFO_SELECT_READ_ONLY'] = 'The API key grants readonly permissions to these endpoints.';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_GENERATE_BTN'] = 'Generate API-Key';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_YES'] = 'Yes';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_NO'] = 'No';

$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_API_KEY_ALREADY_EXISTS'] = 'An entry with this API key already exists';
$_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_COULD_NOT_STORE_APIKEY'] = 'The API key could not be saved';
