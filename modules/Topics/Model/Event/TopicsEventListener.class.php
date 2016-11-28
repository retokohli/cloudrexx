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

namespace Cx\Modules\Topics\Model\Event;

/**
 * Listen to MediaBrowser's load event
 * @copyright   Cloudrexx AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class TopicsEventListener extends \Cx\Core\Event\Model\Entity\DefaultEventListener
{
    /**
     * Enable the Topics extension
     * @global  array   $_ARRAYLANG
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function mediabrowserLoad()
    {
        global $_ARRAYLANG;
        \JS::registerCSS('modules/Topics/View/Style/TopicsBrowser.css');
        \JS::registerJS('modules/Topics/View/Script/TopicsBrowser.js');
        \Env::get('init')->loadLanguageData('Topics');
        $cxjs = \ContrexxJavascript::getInstance();
        foreach ($_ARRAYLANG as $key => $value) {
            if (!preg_match('/^TXT_MODULE_TOPICS_FILEBROWSER/', $key)) {
                continue;
            }
            // The scope used by the translate filter
            $cxjs->setVariable($key, $value, 'mediabrowser');
        }
    }

}
