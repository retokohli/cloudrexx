<?php
/**
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */

namespace Cx\Modules\Topics\Controller;

/**
 * Topics SettingsController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
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
            static::errorHandler();
        }
        \Cx\Core\Setting\Controller\Setting::storeFromPost();
// TODO: Settings::show() should accept the empty string as $tab_name
// and show *no tab*.
// TODO: There should be a more elegant way to use Settings::show()
// $uriBase, $section, $tab_name, and $prefix should all default to null,
// and substitute sensible default values when unset.
// They should assume arguments roughly corresponding to the following:
        \Cx\Core\Setting\Controller\Setting::show(
            $template, ASCMS_BACKEND_PATH.'/Topics/Settings', '',
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
