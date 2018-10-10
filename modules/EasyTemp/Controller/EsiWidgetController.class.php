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
 * JsonAdapter Controller to handle EsiWidgets
 * @author Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     cloudrexx
 * @subpackage  module_easytemp
 */

namespace Cx\Modules\EasyTemp\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * @author Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     cloudrexx
 * @subpackage  module_easytemp
 */
class EsiWidgetController extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController
{
    /**
     * In frontend mode, parse the job headlines
     * @param string                                 $name     Widget name
     * @param \Cx\Core\Html\Sigma                    $template Widget template
     * @param \Cx\Core\Routing\Model\Entity\Response $response Response object
     * @param array                                  $params   Get parameters
     */
    public function parseWidget($name, $template, $response, $params)
    {
        global $_ARRAYLANG;
        $langId = $params['locale']->getId();
        $theme = $params['theme'];
        if (!($theme instanceof \Cx\Core\View\Model\Entity\Theme)) {
            return;
        }
        $_ARRAYLANG = array_merge(
            $_ARRAYLANG,
            \Env::get('init')->getComponentSpecificLanguageData(
                'EasyTemp', true, $langId));

        $criteria = [];
        $index = 0;
        while (isset($params[$index]) && isset($params[$index + 1])) {
            $criteria[$params[$index]] = $params[$index + 1];
            $index += 2;
        }
        $frontend = $this->getController('Frontend');
        $frontend->parseContainer($name, $template, $theme, $criteria);
        // Optionally set the cache timeout (untested)
        //$dateTime = new \DateTime('@' . (time() + 86400));
        //$response->setExpirationDate($dateTime);
    }

}
