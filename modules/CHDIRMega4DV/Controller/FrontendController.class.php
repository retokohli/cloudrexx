<?php declare(strict_types=1);

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
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_chdirmega4dv
 */

namespace Cx\Modules\CHDIRMega4DV\Controller;

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_chdirmega4dv
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController
{
    /**
     * Set up the view
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string              $cmd
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
\DBG::activate(DBG_PHP|DBG_DB_ERROR);
        $template->setTemplate($this->getContent());
\DBG::deactivate();
    }

    /**
     * Return the effective content
     */
    protected function getContent()
    {
        $folder = $this->getCurrentFolder();
        $path = $this->getBasePath() . $folder;
        if (!file_exists($path)) {
            return 'Sorry, could not find the file ' . $folder;
        }
        $content = file_get_contents($path);
        $dom = new \DOMDocument();
        $dom->loadHTML($content);
        libxml_use_internal_errors(true);
        $redirect = $this->checkForRedirect($dom);
        if ($redirect) {
            return $redirect;
        }
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//comment()') as $comment) {
            $comment->parentNode->removeChild($comment);
        }
        $this->rewriteDocumentsProxy($dom);
        $this->rewriteUrls($dom);
        $this->rewriteAreas($dom);
        $this->rewriteImages($dom, 'inline');
        $this->rewriteScripts($dom);
        libxml_clear_errors();
        $content = $this->extractBody($dom);
        return $content;
    }

    function checkForRedirect($dom)
    {
        $metas = $dom->getElementsByTagName('meta');
        if ($metas) {
            foreach ($metas as $meta) {
                $http_equiv = $meta->getAttribute('http-equiv');
                $param = $meta->getAttribute('content');
                if ($http_equiv && $http_equiv == 'REFRESH' && $param) {
                    $parts = explode('=', $param);
                    $param = array_pop($parts);
                    if (strpos($param, 'http') !== 0) {
                        $param = $this->getBaseFolder() . '/' . $param;
                        $param = $this->checkFolderAvailability($param,
                                $this->getBaseFolder() . '/');
                    }
                    $url = clone \Env::get('Resolver')->getUrl();
                    $url->setParam('dv', $param);
                    \Cx\Core\Csrf\Controller\Csrf::redirect($url);
                }
            }
        }
        return false;
    }

    function rewriteDocumentsProxy($dom)
    {
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            if (strpos($link->getAttribute('href'), 'http') !== 0 && strpos(strtolower($link->getAttribute('href')),
                            'xls') !== false || strpos(strtolower($link->getAttribute('href')),
                            'pdf') !== false) {
                $path = $this->getBaseFolder() .'/' . $link->getAttribute('href');
                $url = '/imageproxy.php?c=' . md5($path . '38364DC9-FAEF-406E-B6A7-DFB0C83F1CBE') . '&i=' . urlencode($path);
                $link->setAttribute('href', $url);
            }
        }
    }

    function rewriteUrls($dom, $tagname = 'a')
    {
        $links = $dom->getElementsByTagName($tagname);
        $baseFolder = $this->getBaseFolder();
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            // Do not touch href values starting with any of:
            if (strpos($href, '/imageproxy.php') !== 0
                && strpos($href, 'mailto') !== 0
                && strpos($href, 'http') !== 0
                && strpos($href, '#') !== 0
            ) {
                $param = explode('#', $href);
                $url = clone \Env::get('Resolver')->getUrl();
                $url->setParam('dv', $baseFolder . '/' . $param[0]);
// TODO: Is this a proper fix?  I don't want the (default) port to appear:
                $url->setPort(null);
                $urlString = $url . (isset($param[1]) ? '#' . $param[1] : '');
                $link->setAttribute('href', $urlString);
            }
        }
    }

    function rewriteAreas($dom)
    {
        $this->rewriteUrls($dom, 'area');
    }

    function rewriteScripts($dom)
    {
        $scripts = $dom->getElementsByTagName('script');
        foreach ($scripts as $script) {
            if ($script->nodeValue) {
                $script->nodeValue = '$J(document).ready(function($J){'
                    . strtr($script->nodeValue, ['$(' => '$J('])
                    . '});';
            }
        }
    }

    function _rewriteImagesInline($dom)
    {
        $baseFolder = $this->getBaseFolder();
        $basePath = $this->getBasePath();
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if (strpos($img->getAttribute('src'), 'http') !== 0) {
                $path = $basePath . $baseFolder
                    . '/' . $img->getAttribute('src');
                if (file_exists($path)) {
                    $mime = \Mime::getMimeTypeForExtension($path);
                    $data = base64_encode(file_get_contents($path));
                    $img->setAttribute('src', 'data:' . $mime . ';base64,' . $data);
                } else {
                	$img->setAttribute('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs=');
                }
            }
        }
    }

    function _rewriteImagesProxy($dom)
    {
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if (strpos($img->getAttribute('src'), 'http') !== 0) {
                $url = '/imageproxy.php?c=' . md5($img->getAttribute('src') . '38364DC9-FAEF-406E-B6A7-DFB0C83F1CBE') . '&i=' . urlencode($img->getAttribute('src'));
                $img->setAttribute('src', $url);
            }
        }
    }

    function _rewriteImagesSimple($dom)
    {
        $baseFolder = $this->getBaseFolder();
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if (strpos($img->getAttribute('src'), 'http') !== 0) {
                $url = '/mega4dv/' . $baseFolder . '/' . $img->getAttribute('src');
                $img->setAttribute('src', $url);
            }
        }
    }

    function rewriteImages($dom, $type = 'simple')
    {
        switch ($type) {
            case 'inline':
                return $this->_rewriteImagesInline($dom);
            case 'proxy':
                return $this->_rewriteImagesProxy($dom);
            default:
                return $this->_rewriteImagesSimple($dom);
        }
    }

    function extractBody($dom)
    {
        $body = $dom->getElementsByTagName('body');
        $content = '';
        foreach ($body as $element) {
            $content .= $this->domInnerHtml($element);
        }
        return $content . '<div style="clear:both;"></div>';
    }

    function domInnerHtml($element)
    {
        $innerHTML = '';
        $children = $element->childNodes;
        foreach ($children as $child) {
            $dom = new \DOMDocument();
            $cloned = $child->cloneNode(true);
            $dom->appendChild($dom->importNode($cloned, true));
            $innerHTML .= $dom->saveHTML();
        }
        return $innerHTML;
    }

    function guessStartFolder()
    {
    	global $objInit;
    	$lang = 'de';
        $langId = $objInit->getFrontendLangId();
        $lang = \FWLanguage::getLanguageCodeById($langId);
        $base = $this->getBasePath();
        $dirs = glob($base . '/*', GLOB_ONLYDIR);
        $max = 0;
        foreach ($dirs as $dir) {
        	$key = preg_replace('/[^0-9]/','',$dir);
        	if ($key > $max) {
        		$goto = $dir;
        		$max = $key;
        	}
        }
        if (file_exists($goto . '/de/index.htm')
            || file_exists($goto . '/fr/index.htm')) {
            return strtr($goto, [$base => ''])
                . '/' . $lang . '/index.htm';
        }
        return strtr($goto, [$base => '']) . '/index.htm';
    }

    function checkFolderAvailability($path)
    {
        $base = $this->getBasePath();
        $full = $base . $path;
        $dir = dirname($full);
        $file = basename($full);
        switch (true) {
            case file_exists($dir . '/' . $file):
                break;
            case file_exists($dir . '/' . strtolower($file)):
                $path = strtr($path, [$file => strtolower($file)]);
                break;
            case file_exists($dir . '/' . strtoupper($file)):
                $path = strtr($path, [$file => strtoupper($file)]);
                break;
            default:
                break;
        }
        return $path;
    }

    protected function getCurrentFolder()
    {
    	global $objInit;
        static $currentFolder = null;
        if (!$currentFolder) {
            $langId = $objInit->getFrontendLangId();
            $lang = \FWLanguage::getLanguageCodeById($langId);
            $get = $this->cx->getRequest()->getParams();
            if (empty($get['dv'])) {
                $currentFolder = $this->checkFolderAvailability('index.htm');
                if (!file_exists($currentFolder)) {
                    $currentFolder = $this->guessStartFolder();
                }
            } else {
                $currentFolder = strtr(
                    urldecode($get['dv']), '.', '');
                $currentFolder = strtr($currentFolder,
                    [
                        '/de/' => '/' . $lang . '/',
                        '/fr/' => '/' . $lang . '/'
                    ]
                );
                $currentFolder = $this->checkFolderAvailability($currentFolder);
            }
        }
        return $currentFolder;
    }

    function getBaseFolder()
    {
        static $baseFolder = null;
        if (!$baseFolder) {
            $baseFolder = dirname($this->getCurrentFolder());
        }
        return $baseFolder;
    }

    /**
     * Return the absolute filesystem path of the contents folder
     *
     * This includes a trailing slash, and is typically something like:
     *  /var/www/html/media/CHDIRMega4DV/
     * @return  string
     */
    protected function getBasePath(): string
    {
        return $this->cx->getWebsiteDocumentRootPath()
            . '/media/' . $this->getName() . '/';
    }

}
