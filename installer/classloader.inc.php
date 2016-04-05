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
 * This file is used so index.php works under PHP 5.2 (namespaces!)
 * @author <michael.ritter@comvation.com>
 */
require_once($basePath.'/../core/Core/Controller/Cx.class.php');
require_once($basePath.'/../core/ClassLoader/ClassLoader.class.php');
require_once($basePath.'/InstallerCx.class.php');

$installerCx = new \InstallerCx($basePath);

$cl = new \Cx\Core\ClassLoader\ClassLoader($installerCx, false);
$cl->loadFile($basePath.'/../core/Env.class.php');
$cl->loadFile($basePath.'/../lib/FRAMEWORK/DBG/DBG.php');
\Env::set('cx', $installerCx);
