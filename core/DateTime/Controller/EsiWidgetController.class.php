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
 * Class EsiWidgetController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datetime
 * @version     1.0.0
 */

namespace Cx\Core\DateTime\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datetime
 * @version     1.0.0
 */

class EsiWidgetController extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController {

    /**
     * Parses a widget
     *
     * @param string                                 $name     Widget name
     * @param \Cx\Core\Html\Sigma                    $template WidgetTemplate
     * @param \Cx\Core\Routing\Model\Entity\Response $response Response object
     * @param array                                  $params   Get parameters
     */
    public function parseWidget($name, $template, $response, $params)
    {
        if ($name === 'DATE') {
            global $_CORELANG;

            //The global $_CORELANG is required by the method showFormattedDate()
            $_CORELANG = \Env::get('init')->getComponentSpecificLanguageData(
                'Core',
                true,
                $params['lang']
            );
            $template->setVariable($name, showFormattedDate());
            $setTimeout = new \DateTime();
            $setTimeout->setTime(23, 59, 59);
            $response->setExpirationDate($setTimeout);
            return;
        }

        $dateTime = $this->getComponent('DateTime');
        $date     = $dateTime->createDateTimeForUser('now');
        if ($name === 'TIME' || $name === 'DATE_TIME') {
            $template->setVariable($name, $date->format('H:i'));
            $date->setTime($date->format('H'), $date->format('i'), 59);
            $response->setExpirationDate($date);
            return;
        }

        if ($name === 'DATE_YEAR') {
            $template->setVariable($name, $date->format('Y'));
            $date->modify($date->format('Y') . '-12-31');
            $date->setTime(23, 59, 59);
            $response->setExpirationDate($date);
            return;
        }

        if ($name === 'DATE_MONTH') {
            $template->setVariable($name, $date->format('m'));
            $date->modify('last day of this month');
            $date->setTime(23, 59, 59);
            $response->setExpirationDate($date);
            return;
        }

        if ($name === 'DATE_DAY') {
            $template->setVariable($name, $date->format('d'));
            $date->setTime(23, 59, 59);
            $response->setExpirationDate($date);
        }

    }
}
