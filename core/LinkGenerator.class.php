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
 * LinkGenerator
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core
 */

/**
 * LinkGeneratorException
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core
 */
class LinkGeneratorException extends \Exception {}

/**
 * Handles the node-Url placeholders: [[ NODE_(<node_id>|<module>[_<cmd>])[_<lang_id>] ]]
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core
 */
class LinkGenerator {
    /**
     * array ( placeholder_name => placeholder_link
     *
     * @var array stores the placeholders found by scan()
     */
    protected $placeholders = array();
    /**
     * @var boolean whether fetch() ran.
     */
    protected $fetchingDone = false;

    /**
     * Replace all occurrences of node-placeholders (NODE_...) by their
     * URL-representation.
     * @param   mixed $content  Either a string or an array of strings in which the node-placeholders shall be replaced by their URL-representation.
     * @param   boolean $absoluteUris   Set to TRUE to replace the node-placeholders by absolute URLs.
     * @param   Cx\Core\Net\Model\Entity\Domain $domain Set the domain that shall be used when absolute URLs shall be generated.
     */
    public static function parseTemplate(&$content, $absoluteUris = false, \Cx\Core\Net\Model\Entity\Domain $domain = null)
    {
        $lg = new LinkGenerator();

        if (!is_array($content)) {
            $arrTemplates = array(&$content);
        } else {
            $arrTemplates = &$content;
        }

        foreach ($arrTemplates as &$template) {
            $lg->scanAndReplace($template);
        }
    }

    /**
     * Scans the given string for placeholders and remembers them
     * @param string $content
     */
    protected function scanAndReplace(&$content) {
        $this->fetchingDone = false;

        $regex = '/\{'.\Cx\Core\ContentManager\Model\Entity\Page::NODE_URL_PCRE.'\}/xi';

        $matches = array();
        if (!preg_match_all($regex, $content, $matches)) {
            return;
        }

        for($i = 0; $i < count($matches[0]); $i++) {
            $placeholder = $matches[\Cx\Core\ContentManager\Model\Entity\Page::NODE_URL_PLACEHOLDER][$i];
            $nodePlaceholder = \Cx\Core\Routing\NodePlaceholder::fromPlaceholder('[[' . $placeholder . ']]');
            $content = str_replace('{'.$placeholder.'}', $nodePlaceholder->getUrl(), $content);
        }
    }

    public function getPlaceholders() {
        return $this->placeholders;
    }
}

