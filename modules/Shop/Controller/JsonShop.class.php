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
 * JsonShop
 * Json controller for shop module
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_shop
 */

namespace Cx\Modules\Shop\Controller;
use \Cx\Core\Json\JsonAdapter;

class JsonShopException extends \Exception {};

/**
 * JsonShop
 * Json controller for shop module
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_shop
 */
class JsonShop implements JsonAdapter {
    /**
     * List of messages
     * @var Array
     */
    private $messages = array();

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'Shop';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array('parseProductsBlock', 'getNavbar');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        return new \Cx\Core_Modules\Access\Model\Entity\Permission(
            null,
            null,
            false
        );
    }

    /**
     * Parse products block
     *
     * @param array $params User input parameters
     *
     * @return array
     * @throws JsonShopException
     */
    public function parseProductsBlock($params)
    {
        if (empty($params)) {
            return array('content' => '');
        }

        try {
            $block  = isset($params['get']['block'])
                ? contrexx_input2raw($params['get']['block']) : 0;
            $catId  = isset($params['get']['catId'])
                ? contrexx_input2raw($params['get']['catId']) : 0;
            $langId = isset($params['get']['langId'])
                ? contrexx_input2raw($params['get']['langId']) : 0;
            $file   = !empty($params['get']['file'])
                    ? contrexx_input2raw($params['get']['file']) : '';

            $theme    = $this->getThemeFromInput($params);
            $content  = $theme->getContentBlockFromTpl($file, $block);
            if (!$content) {
                throw new JsonShopException(
                    'The block '. $block .' not exists'
                );
            }

            $template = new \Cx\Core\Html\Sigma();
            $template->setTemplate($content);

            if (!defined('FRONTEND_LANG_ID')) {
                define('FRONTEND_LANG_ID', $langId);
            }

            return array(
                'content' => Shop::parse_products_blocks($template, $catId)
            );
        } catch (JsonShopException $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }
    }

    /**
     * Get theme from the user input
     *
     * @param array $params User input array
     *
     * @return \Cx\Core\View\Model\Entity\Theme Theme instance
     * @throws JsonDirectoryException When theme id empty or
     * theme does not exits in the system
     */
    protected function getThemeFromInput($params)
    {
        $themeId  = !empty($params['get']['template'])
            ? contrexx_input2int($params['get']['template']) : 0;
        if (empty($themeId)) {
            throw new JsonDirectoryException(
                'The theme id is empty in the request'
            );
        }
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $theme           = $themeRepository->findById($themeId);
        if (!$theme) {
            throw new JsonDirectoryException(
                'The theme id '. $themeId .' does not exists.'
            );
        }
        return $theme;
    }

    /**
     * Get Navbar
     *
     * @param array $params User input array
     *
     * @return array
     */
    public function getNavbar($params)
    {
        global $_ARRAYLANG;

        if (empty($params) || empty($params['get']['file'])) {
            return array('content' => '');
        }

        $file    = isset($params['get']['file'])
            ? contrexx_input2raw($params['get']['file']) : '';
        $lang    = isset($params['get']['langId'])
            ? contrexx_input2raw($params['get']['langId']) : '';
        $theme   = $this->getThemeFromInput($params);
        $content = $theme->getContentFromFile($file . '.html');
        if (empty($content)) {
            $content = $theme->getContentFromFile('shopnavbar.html');
        }
        
        $_ARRAYLANG = \Env::get('init')->loadLanguageData('Shop');
        if (!defined('FRONTEND_LANG_ID')) {
            define('FRONTEND_LANG_ID', $lang);
        }

        return array('content' => Shop::getNavbar($content));
    }
}
