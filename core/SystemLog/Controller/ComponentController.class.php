<?php

/**
 * Contrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Comvation AG 2007-2015
 * @version   Contrexx 4.0
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
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Main controller for SystemLog
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_systemlog
 */

namespace Cx\Core\SystemLog\Controller;

/**
 * Main controller for SystemLog
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_systemlog
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    public function getControllerClasses() {
        return array();
    }

     /**
     * Load the component SystemLog.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_ARRAYLANG, $subMenuTitle;
        $subMenuTitle = $_ARRAYLANG['TXT_SYSTEM_LOGS'];
        
        \Permission::checkAccess(55, 'static');
        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();
        $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');
        
        $objSystemLog = new \Cx\Core\SystemLog\Controller\SystemLog();
        $objSystemLog->getLogPage();  
        
        $this->cx->getTemplate()->setRoot($cachedRoot); 
    }

}