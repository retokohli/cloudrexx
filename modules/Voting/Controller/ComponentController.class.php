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
 * Main controller for Voting
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_voting
 */

namespace Cx\Modules\Voting\Controller;

class JsonVotingException extends \Exception {}

/**
 * Main controller for Voting
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_voting
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController implements \Cx\Core\Json\JsonAdapter {
    /**
     * getControllerClasses
     *
     * @return type
     */
    public function getControllerClasses() {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getControllersAccessableByJson()
    {
        return array('ComponentController');
    }

    /**
     * Load the component Voting.
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $objTemplate, $_CORELANG;

        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                \Env::get('cx')->getPage()->setContent(votingShowCurrent(\Env::get('cx')->getPage()->getContent()));
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(14, 'static');
                $subMenuTitle = $_CORELANG['TXT_CONTENT_MANAGER'];
                $objvoting = new VotingManager();
                $objvoting->getVotingPage();
                break;

            default:
                break;
        }
    }

    /**
     * Do something before content is loaded from DB
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $themesPages, $page_template, $section;

        $themesPages['sidebar'] = $this->replaceEsiContent(
            $themesPages['sidebar'],
            'sidebar.html'
        );
        $themesPages['index'] = $this->replaceEsiContent(
            $themesPages['index'],
            'index.html'
        );
        $page_template = $this->replaceEsiContent(
            $page_template,
            $section == 'Home' ? 'home.html' : 'content.html'
        );
        $page->setContent($this->replaceEsiContent(
            $page->getContent(),
            '',
            $page
        ));
    }

    /**
     * Get current theme instance
     *
     * @return \Cx\Core\View\Model\Entity\Theme
     */
    protected function getCurrentTheme()
    {
        global $objInit;

        static $theme = null;

        if (!isset($theme)) {
            $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
            $theme           = $themeRepository->findById($objInit->getCurrentThemeId());
        }

        return $theme;
    }

    /**
     * Replace esi content in given content
     *
     * @param string                                        $content    Content
     * @param string                                        $file       Theme file name
     * @param \Cx\Core\ContentManager\Model\Entity\Page     $page       Page instance
     * @param string                                        $block      Template Block name
     *
     * @return string Replaced content
     */
    protected function replaceEsiContent(
        $content,
        $file  = 'index.html',
        $page = null,
        $block = 'voting_result',
        $apiMethod = 'showVotingResult'
    ) {
        $arrMatches = null;
        if (!preg_match(
           '@<!--\s+BEGIN\s+('. $block .')\s+-->(.*)<!--\s+END\s+\1\s+-->@m',
            $content,
            $arrMatches
        )) {
            return $content;
        }
        $params = array();
        if (   $page != null
            && ($page instanceof \Cx\Core\ContentManager\Model\Entity\Page)
        ) {
            $params = array('page' => $page->getId());
        } else {
            $theme   = $this->getCurrentTheme();
            if (!$theme) {
                return $content;
            }
            $params = array(
                'template' => $theme->getId(),
                'file'     => $file
            );
        }
        $esiContent = $this->getComponent('Cache')->getEsiContent(
            'Voting',
            $apiMethod,
            $params
        );
        $replacedContent = preg_replace(
            '@(<!--\s+BEGIN\s+('. $block .')\s+-->.*<!--\s+END\s+\2\s+-->)@m',
            $esiContent,
            $content
        );

        return $replacedContent;
    }

    /**
     * Json data for getting voting result
     *
     * @param array $params Request parameters
     *
     * @return array
     */
    public function showVotingResult($params)
    {
        $pageId =  !empty($params['get']['page'])
                 ? contrexx_input2int($params['get']['page']) : 0;
        if (!empty($pageId)) {
            $pageRepo = $this->cx
                             ->getDb()
                             ->getEntityManager()
                             ->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
            $result = $pageRepo->findOneById($pageId);
            if (!$result) {
                return array('content' => '');
            }
            $page    = $result[0];
            $matches = null;
            if (preg_match(
                '/<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->/s',
                $page->getContent(),
                $matches
            )) {
                $content = $matches[2];
            }
        } else {
            $content = $this->getVotingContentBlock(
                $params,
                'voting_result'
            );
        }
        if ($this->cx->getClassLoader()->loadFile($this->cx->getCodeBaseModulePath().'/Voting/Controller/Voting.class.php')) {
            return array('content' => setVotingResult($content));;
        }
        return array('content' => '');
    }

    /**
     * Get the template block to parse the access placeholders
     *
     * @param array     $params     Input params
     * @param string    $block      Template block
     *
     * @return string
     * @throws JsonVotingException
     */
    protected function getVotingContentBlock(
        $params = array(),
        $block = ''
    ) {
        try {
            $theme = $this->getThemeFromInput($params);
            $file  =  !empty($params['get']['file'])
                    ? contrexx_input2raw($params['get']['file']) : '';
            if (empty($file)) {
                throw new JsonVotingException(__METHOD__ .': the input file cannot be empty');
            }
            $content = $theme->getContentFromFile($file);
            $matches = null;
            if (   $content
                && preg_match(
                    '/<!--\s+BEGIN\s+('. $block .')\s+-->(.*)<!--\s+END\s+\1\s+-->/s',
                    $content,
                    $matches
                )
            ) {
                return $matches[2];
            }
        } catch (\Exception $ex) {
            \DBG::log($ex->getMessage());
        }
        throw new JsonVotingException('The block '. $block .' not exists');
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array('showVotingResult');
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return new \Cx\Core_Modules\Access\Model\Entity\Permission(
            null,
            null,
            false
        );
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }

    /**
     * Wrapper to __call()
     * @return string ComponentName
     */
    public function getName() {
        return parent::getName();
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
}
