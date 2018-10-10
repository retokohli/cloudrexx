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

namespace Cx\Modules\EasyTemp\Controller;

/**
 * Main controller for EasyTemp
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     cloudrexx
 * @subpackage  module_easytemp
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController
{
    /**
     * Access permission IDs
     *
     * Note that these values have been picked arbitrarily.
     * Make sure that they remain unique!
     */
    const PERMISSION_BACKEND = 906;
    const PERMISSION_IMPORT = 907;

    /**
     * Returns all Controller class names for this component (except this)
     *
     * Be sure to return all your controller classes if you add your own
     * @return  array   List of Controller class names (without namespace)
     */
    public function getControllerClasses()
    {
        return array('Frontend', 'Backend', 'Import', 'EsiWidget');
    }

    /**
     * Returns a list of JsonAdapter class names
     *
     * The array values might be a class name without namespace. In that case
     * the namespace \Cx\{component_type}\{component_name}\Controller is used.
     * If the array value starts with a backslash, no namespace is added.
     *
     * Avoid calculation of anything, just return an array!
     * @return array List of ComponentController classes
     */
    public function getControllersAccessableByJson()
    {
        return array('EsiWidgetController');
    }

    /**
     * Return a list of command mode commands provided by this component
     * @return  array   List of command names
     */
    public function getCommandsForCommandMode()
    {
        $permission = new \Cx\Core_Modules\Access\Model\Entity\Permission(
            array(), array(), true, array(),
            array(self::PERMISSION_IMPORT)
        );
        // TODO: Implement Permission checking.
        // FTTB, only the keys (command strings) are returned.
        return array_keys(array(
            'importApplicationCodes' => $permission,
            'importJobCodes' => $permission,
            'importJobs' => $permission,
        ));
    }

    /**
     * Returns the description for a command provided by this component
     * @param   string  $command    The name of the command
     * @param   boolean $short      Ignored
     * @return  string              The command description
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getCommandDescription($command, $short = false)
    {
        global $_ARRAYLANG;
        return $_ARRAYLANG[
            'TXT_MODULE_EASYTEMP_COMMAND_DESCRIPTION_' . strtoupper($command)];
    }

    /**
     * Execute one of the commands listed in getCommandsForCommandMode()
     * @see getCommandsForCommandMode()
     * @param   string  $command    Name of command to execute
     * @param   array   $arguments  Ignored
     * @param   array   $dataArguments (optional) List of data arguments for the command
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function executeCommand($command, $arguments, $dataArguments = array())
    {
        $import = $this->getController('Import');
        if (method_exists($import, $command)) {
            call_user_func([$import, $command], $arguments);
            $this->cx->getEvents()->triggerEvent('clearEsiCache',
                array(
                    'Widget',
                    array(
                        'EASYTEMP_HEADLINES_FILE',
                        'EASYTEMP_SEARCH_FILE',
                    ),
                )
            );
        }
    }

    /**
     * Register the Widgets for the headlines
     *
     * This event must be registered in the postInit-Hook definition
     * file config/postInitHooks.yml.
     * @param   \Cx\Core\Core\Controller\Cx $cx
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function postInit(\Cx\Core\Core\Controller\Cx $cx)
    {
        $widgetController = $this->getComponent('Widget');
        $widgetNames = array(
            'EASYTEMP_HEADLINES_FILE',
            'EASYTEMP_SEARCH_FILE',
        );
        foreach ($widgetNames as $widgetName) {
            $widget = new \Cx\Core_Modules\Widget\Model\Entity\EsiWidget(
                $this, strtolower($widgetName),
                \Cx\Core_Modules\Widget\Model\Entity\Widget::TYPE_CALLBACK);
            $widget->setEsiVariable(
                \Cx\Core_Modules\Widget\Model\Entity\EsiWidget::ESI_VAR_ID_THEME);
            $widgetController->registerWidget($widget);
        }
    }

}
