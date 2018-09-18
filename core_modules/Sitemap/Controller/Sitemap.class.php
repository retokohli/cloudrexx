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
 * Sitemap
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @version 1.0.1
 * @package     cloudrexx
 * @subpackage  coremodule_sitemap
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Core_Modules\Sitemap\Controller;
/**
 * Sitemap
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @version 1.0.1
 * @package     cloudrexx
 * @subpackage  coremodule_sitemap
 */
class Sitemap
{
    protected $_objTpl;

    /**
    * Constructor
    *
    * @param  string
    */
    public function __construct($pageContent, $license)
    {
        $this->_objTpl = new \Cx\Core\Html\Sigma('.');
        \Cx\Core\Csrf\Controller\Csrf::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_objTpl->setTemplate($pageContent);

        if (isset($this->_objTpl->_blocks['sitemap'])) {
            $sm = new \Cx\Core\PageTree\SitemapPageTree(\Env::get('em'), $license, 0, null, FRONTEND_LANG_ID, null, true, true);
            $sm->setVirtualLanguageDirectory(\Env::get('virtualLanguageDirectory'));
            $sm->setTemplate($this->_objTpl);
            $sm->render();
        }
    }

    public function getSitemapContent() {
        return $this->_objTpl->get();
    }
}

