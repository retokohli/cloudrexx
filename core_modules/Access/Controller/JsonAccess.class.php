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
 * JsonAccess
 * Json controller for Access component
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_access
 */

namespace Cx\Core_Modules\Access\Controller;

class JsonAccessException extends \Exception {}

/**
 * JsonAccess
 * Json controller for Access component
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_access
 */
class JsonAccess implements \Cx\Core\Json\JsonAdapter
{
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
        return 'Access';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array('showCurrentlyOnlineUsers', 'showLastActiveUsers', 'showLatestRegisteredUsers', 'showBirthdayUsers');
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
        return new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false);
    }

    /**
     * Parse the currently online users
     *
     * @param array $params Users input params
     *
     * @return array
     */
    public function showCurrentlyOnlineUsers($params)
    {
        try {
            $content = $this->getAccessContentBlock(
                $this->getPageFromInput($params),
                $this->getThemeFromInput($params),
                'access_currently_online_member_list'
            );
            return array(
                'content' => $this->parseAccessContentBlock(
                    $content,
                    'currently_online',
                    'setCurrentlyOnlineUsers'
                )
            );
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }
    }

    /**
     * Parse the last active users
     *
     * @param array $params Users input params
     *
     * @return array
     */
    public function showLastActiveUsers($params)
    {
        try {
            $content = $this->getAccessContentBlock(
                $this->getPageFromInput($params),
                $this->getThemeFromInput($params),
                'access_last_active_member_list'
            );
            return array(
                'content' => $this->parseAccessContentBlock(
                    $content,
                    'last_active',
                    'setLastActiveUsers'
                )
            );
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }
    }

    /**
     * Parse the last registered users
     *
     * @param array $params Users input params
     *
     * @return array
     */
    public function showLatestRegisteredUsers($params)
    {
        try {
            $content = $this->getAccessContentBlock(
                $this->getPageFromInput($params),
                $this->getThemeFromInput($params),
                'access_latest_registered_member_list'
            );
            return array(
                'content' => $this->parseAccessContentBlock(
                    $content,
                    'latest_registered',
                    'setLatestRegisteredUsers'
                )
            );
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }
    }

    /**
     * Parse the birth day users
     *
     * @param array $params Users input params
     *
     * @return array
     */
    public function showBirthdayUsers($params)
    {
        try {
            $content = $this->getAccessContentBlock(
                $this->getPageFromInput($params),
                $this->getThemeFromInput($params),
                'access_birthday_member_list'
            );
            return array(
                'content' => $this->parseAccessContentBlock(
                    $content,
                    'birthday',
                    'setBirthdayUsers'
                )
            );
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }
    }

    /**
     * Parse the block by given content and block name and method
     *
     * @param array $params Users input params
     *
     * @return array
     */
    protected function parseAccessContentBlock($content, $block, $method)
    {
        if (empty($content) || empty($block) || empty($method)) {
            return '';
        }
        $template = new \Cx\Core\Html\Sigma();
        $template->setTemplate($content);
        $accessBlocks = new AccessBlocks($template);
        if ($template->blockExists('access_'. $block .'_female_members')) {
            $accessBlocks->{$method}('female');
        }
        if ($template->blockExists('access_'. $block .'_male_members')) {
            $accessBlocks->{$method}('male');
        }
        if ($template->blockExists('access_'. $block .'_members')) {
            $accessBlocks->{$method}();
        }
        return $template->get();
    }

    /**
     * Get the template block to parse the access placeholders
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page   Page instance
     * @param \Cx\Core\View\Model\Entity\Theme          $theme  Theme instance
     * @param string                                    $block  Access block
     *
     * @return string
     * @throws JsonAccessException
     */
    protected function getAccessContentBlock(
        \Cx\Core\ContentManager\Model\Entity\Page $page,
        \Cx\Core\View\Model\Entity\Theme $theme,
        $block = ''
    ) {
        $content = $this->getContentFromThemeFile($theme, 'index.html');
        if (!preg_match(
            '/<!--\s+BEGIN\s+'. $block .'\s+-->(.*)<!--\s+END\s+'. $block .'\s+-->/s',
             $content
        )) {
            $content = \Cx\Core\Core\Controller\Cx::getContentTemplateOfPage($page);
        }
        $matches = null;
        if (preg_match(
            '/<!--\s+BEGIN\s+'. $block .'\s+-->(.*)<!--\s+END\s+'. $block .'\s+-->/s',
            $content,
            $matches
        )) {
            return $matches[1];
        }
        throw new JsonAccessException('The block '. $block .' not exists');
    }

    /**
     * Get theme from the user input
     *
     * @param array $params User input array
     * @return \Cx\Core\View\Model\Entity\Theme Theme instance
     * @throws JsonNewsException When theme id empty or theme does not exits in the system
     */
    protected function getThemeFromInput($params)
    {
        $themeId  = !empty($params['get']['template']) ? contrexx_input2int($params['get']['template']) : 0;
        if (empty($themeId)) {
            throw new JsonAccessException('The theme id is empty in the request');
        }
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $theme           = $themeRepository->findById($themeId);
        if (!$theme) {
            throw new JsonAccessException('The theme id '. $themeId .' does not exists.');
        }
        return $theme;
    }

    /**
     * Get page from the input parameters
     *
     * @param array $params User input array
     *
     * @return \Cx\Core\ContentManager\Model\Entity\Page instance of the page
     * @throws JsonAccessException
     */
    protected function getPageFromInput($params)
    {
        $pageId  = !empty($params['get']['pageId']) ? contrexx_input2int($params['get']['pageId']) : 0;
        if (empty($pageId)) {
            throw new JsonAccessException('The page id is empty in the request');
        }
        $pageRepo = \Cx\Core\Core\Controller\Cx::instanciate()
                    ->getDb()
                    ->getEntityManager()
                    ->getRepository('\Cx\Core\ContentManager\Model\Entity\Page');
        $page     = $pageRepo->findById($pageId);
        if (!$page) {
            throw new JsonAccessException('The page id '. $pageId .' does not exists.');
        }
        return $page[0];
    }

    /**
     * Get the contents from the given theme file path
     *
     * @param \Cx\Core\View\Model\Entity\Theme $theme   Theme instance
     * @param string                           $file    Relative file path
     * @return string File content
     * @throws JsonNewsException When file not exists in the theme
     */
    protected function getContentFromThemeFile(\Cx\Core\View\Model\Entity\Theme $theme, $file)
    {
        $filePath = $theme->getFilePath($file);
        if (empty($filePath)) {
            throw new JsonNewsException('The file => '. $file .' not exists in Theme => ' . $theme->getThemesname());
        }

        $content = file_get_contents($filePath);
        return $content;
    }

    /**
     * Check whether the file exists in the template
     *
     * @param \Cx\Core\View\Model\Entity\Theme  $theme  Theme instance
     * @param string                            $file   Filename
     *
     * @return boolean True when file exists in the theme, false otherwise
     */
    protected function isThemeFileExists(\Cx\Core\View\Model\Entity\Theme $theme, $file)
    {
        return !\FWValidator::isEmpty($theme->getFilePath($file));
    }
}
