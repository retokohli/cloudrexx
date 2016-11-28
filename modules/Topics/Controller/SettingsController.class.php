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

namespace Cx\Modules\Topics\Controller;

/**
 * SettingsController
 * @copyright   Cloudrexx AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_topics
 */
class SettingsController extends \Cx\Core\Core\Model\Entity\Controller
{ /**
 * Settings view
 * @param   \Cx\Core\Html\Sigma $template   Template containing content
 *                                          of resolved page
 * @param   string  $cmd                    cmd request parameter value
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        global $_ARRAYLANG;
        \Cx\Core\Setting\Controller\Setting::init('Topics', 'config');
        if (\Cx\Core\Setting\Controller\Setting::getValue(
            'frontend_fulltext_enable') === null) {
            self::errorHandler();
        }
        \Cx\Core\Setting\Controller\Setting::storeFromPost();
// TODO: Settings::show() should accept the empty string as $tab_name
// and show *no tab*.
// TODO: There should be a more elegant way to use Settings::show()
// $uriBase, $section, $tab_name, and $prefix should all default to null,
// and substitute sensible default values when unset.
// They should assume arguments roughly corresponding to the following:
        \Cx\Core\Setting\Controller\Setting::show(
            $template, 'index.php?cmd=Topics&amp;act=Settings', '',
            $_ARRAYLANG['TXT_MODULE_TOPICS_SETTINGS_CONFIG'],
            'TXT_MODULE_TOPICS_SETTINGS_CONFIG_');
        $cmd = null; // Unused
    }

    /**
     * Call this to set up or repair the (default) setting for this module
     *
     * Note that you MUST call Setting::init() beforehand.
     * After storing the settings, this method will reinit them.
     * @return  boolean                 False. Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function errorHandler()
    {
        $i = 0;
// TODO: For type checkbox, $values should default to 1 if unset
        \Cx\Core\Setting\Controller\Setting::add(
            'frontend_fulltext_enable', 1, ++$i,
            \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, 1);
        \Cx\Core\Setting\Controller\Setting::updateAll();
        \Cx\Core\Setting\Controller\Setting::init('Topics', 'config');
        return false;
    }

}
