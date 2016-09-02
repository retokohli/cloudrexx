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
 * An Cloudrexx internal URL
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */

namespace Cx\Core\Routing\Model\Entity;

/**
 * Exception from within this file
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */
class UrlException extends \Cx\Lib\Net\Model\Entity\UrlException {}

/**
 * An Cloudrexx internal URL
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */
abstract class Url extends \Cx\Lib\Net\Model\Entity\Url {
    
    /**
     * Creates an Url instance from a string
     * @param string $stringUrl String to create Url instance for
     * @param boolean $replacePorts (optional) Wheter to replace ports with default ones or not, defaults to false
     * @return \Cx\Lib\Net\Model\Entity\Url Url instance for given string
     * @see fromUrl()
     */
    public static function fromString($stringUrl, $replacePorts = false) {
        return static::fromUrl(new \Cx\Lib\Net\Model\Entity\Url($stringUrl), $replacePorts);
    }
    
    /**
     * Creates an Url instance from an existing Url instance
     *
     * This decides if an Url points to this installation of Cloudrexx and if yes to which mode.
     * Depending on the results there are different return types:
     * - Url does not point to this installation of Cloudrexx: \Cx\Lib\Net\Model\Entity\Url
     * - Url point to this installation's frontend mode: \Cx\Core\Routing\Model\Entity\FrontendUrl
     * - Url point to this installation's backend mode: \Cx\Core\Routing\Model\Entity\BackendUrl
     * - Url point to this installation's command mode: \Cx\Core\Routing\Model\Entity\CommandUrl
     * @param \Cx\Lib\Net\Model\Entity\Url $url Url instance to get Url instance for
     * @param boolean $replacePorts (optional) Wheter to replace ports with default ones or not, defaults to false
     * @return \Cx\Lib\Net\Model\Entity\Url Url instance for given string
     */
    public static function fromUrl($url, $replacePorts = false) {
        \Cx\Core\Setting\Controller\Setting::init(
            'Config',
            null,
            'Yaml',
            null,
            \Cx\Core\Setting\Controller\Setting::NOT_POPULATE
        );
        try {
            switch (static::calculateMode($url)) {
                case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                    $url = new FrontendUrl($url); // resolving (incl. aliases), virtual language dirs and can be generated from pages and so
                    $port = \Cx\Core\Setting\Controller\Setting::getValue(
                        'portFrontend' . $url->getScheme(),
                        'Config'
                    );
                    break;
                case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                    $url = new BackendUrl($url); // can be generated from component backend commands
                    $port = \Cx\Core\Setting\Controller\Setting::getValue(
                        'portBackend' . $url->getScheme(),
                        'Config'
                    );
                    break;
                case \Cx\Core\Core\Controller\Cx::MODE_COMMAND:
                    $url = new CommandUrl($url); // can be generated from datasource/-access and component commands
                    $port = $url->getPort();
                    break;
                default;
                    throw new UrlException('Unknown Url mode');
                    break;
            }
            if ($replacePorts) {
                $url->setPort($port);
            }
            
        // external url
        } catch (UrlException $e) {
            $url = new \Cx\Lib\Net\Model\Entity\Url((string) $url);
        }
        return $url;
    }
    
    /**
     * Returns an Url object for module, cmd and lang
     * @todo There could be more than one page using the same module and cmd per lang
     * @param string $module Module name
     * @param string $cmd (optional) Module command, default is empty string
     * @param int $lang (optional) Language to use, default is FRONTENT_LANG_ID
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $scheme (optional) The scheme to use
     * @param boolean $returnErrorPageOnError (optional) If set to TRUE, this method will return an URL object that point to the error page of Cloudrexx. Defaults to TRUE.
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the supplied module, cmd and lang
     */
    public static function fromModuleAndCmd($module, $cmd = '', $lang = '', $parameters = array(), $scheme = '', $returnErrorPageOnError = true) {
        return \Cx\Core\Routing\Model\Entity\FrontendUrl::fromModuleAndCmd(
            $module,
            $cmd,
            $lang,
            $parameters,
            $scheme,
            $returnErrorPageOnError
        );
    }
    
    /**
     * Returns an Url object pointing to the documentRoot of the website
     * @param int $lang (optional) Language to use, default is FRONTEND_LANG_ID
     * @param string $scheme (optional) The protocol to use
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the documentRoot of the website
     */
    public static function fromDocumentRoot($arrParameters = array(), $lang = '', $scheme = '') {
        return \Cx\Core\Routing\Model\Entity\FrontendUrl::fromDocumentRoot(
            $arrParameters,
            $lang,
            $scheme
        );
    }
    
    /**
     * Returns the URL object for a page
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page to get the URL to
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $protocol (optional) The protocol to use
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the supplied page
     */
    public static function fromPage($page, $parameters = array(), $scheme = '') {
        return \Cx\Core\Routing\Model\Entity\FrontendUrl::fromPage(
            $page,
            $parameters,
            $scheme
        );
    }
    
    /**
     * Tells the mode for an internal Url
     * @param \Cx\Lib\Net\Model\Entity\Url Internal Url
     * @throws UrlException if Url is not internal
     * @return string One of the modes defined in Cx class
     */
    protected static function calculateMode($internalUrl) {
        // commmand line is always command mode
        if (php_sapi_name() == 'cli') {
            return \Cx\Core\Core\Controller\Cx::MODE_COMMAND;
        }
        
        // sort out externals
        if (!static::isInternal($internalUrl)) {
            throw new UrlException('Not an internal Url');
        }
        
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $installationOffsetParts = explode('/', $cx->getWebsiteOffsetPath());
        $firstNonOffsetPartIndex = count($installationOffsetParts);
        $path = $internalUrl->getPathParts();
        $firstPathPart = '/';
        if (isset($path[$firstNonOffsetPartIndex])) {
            $firstPathPart .= $path[$firstNonOffsetPartIndex];
        }
        switch ($firstPathPart) {
            case \Cx\Core\Core\Controller\Cx::FOLDER_NAME_COMMAND_MODE:
                return \Cx\Core\Core\Controller\Cx::MODE_COMMAND;
                break;
            case 'admin':
            case \Cx\Core\Core\Controller\Cx::FOLDER_NAME_BACKEND:
                return \Cx\Core\Core\Controller\Cx::MODE_BACKEND;
                break;
            default:
                return \Cx\Core\Core\Controller\Cx::MODE_FRONTEND;
                break;
        }
    }
    
    /**
     * Returns the Cloudrexx mode of this Url
     * @return string One of the MODE_* constants defined in Cx class
     */
    public function getMode() {
        return static::calculateMode($this);
    }
    
    /**
     * Returns this URL's path without Cloudrexx offset
     *
     * Example:
     * URL: http://localhost/cloudrexx/git/master/de/Medien/Medien-Archiv
     * Return value: /de/Medien/Medien-Archiv
     * @return string URL path without Cloudrexx offset
     */
    public function getPathWithoutOffset() {
        $installationOffset = $this->cx->getWebsiteOffsetPath();
        $providedOffset = $this->getPath();
        return substr(
            $providedOffset,
            strlen($installationOffset)
        );
    }
    
    /**
     * Tells wheter the given Url points to this CLX installation
     * @param \Cx\Lib\Net\Model\Entity\Url Url
     * @return boolean True if Url is internal, false otherwise
     */
    public static function isInternal($url) {
        // check domain
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        if (!$domainRepo->findOneBy(array('name' => $url->getHost()))) {
            return false;
        }
        
        // check offset
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $installationOffset = $cx->getWebsiteOffsetPath();
        $providedOffset = $url->getPath();
        if (
            $installationOffset != substr(
                $providedOffset,
                0,
                strlen($installationOffset)
            )
        ) {
            return false;
        }
        return true;
    }
}
