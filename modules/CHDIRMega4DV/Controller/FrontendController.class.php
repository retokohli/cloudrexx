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

namespace Cx\Modules\CHDIRMega4DV\Controller;

/**
 * FrontendController
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_chdirmega4dv
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController
{
    /**
     * Salt added to md5 hashes
     */
    const SALT = '38364DC9-FAEF-406E-B6A7-DFB0C83F1CBE';

    /**
     * Set up the view
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string              $cmd
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
\DBG::activate(DBG_PHP|DBG_DB_ERROR);
        $template->setTemplate($this->getContent());
// TODO: Enable along with using cx.jQuery in rewriteScripts() (or remove)
//        \JS::activate('cx');
\DBG::deactivate();
    }

    /**
     * Return the effective content
     * @return  string
     */
    protected function getContent(): string
    {
        $this->sendDownload();
        $folder = $this->getCurrentFolder();
        $path = $this->getBasePath() . $folder;
        if (!file_exists($path)) {
            return 'Sorry, could not find the file ' . $folder;
        }
        // Suppress warnings
        //libxml_use_internal_errors(true);
        $content = file_get_contents($path);
        $dom = new \DOMDocument();
        $dom->loadHTML($content);
        $this->checkForRedirect($dom);
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//comment()') as $comment) {
            $comment->parentNode->removeChild($comment);
        }
        $this->rewriteDocumentsProxy($dom);
        $this->rewriteUrls($dom);
        $this->rewriteAreas($dom);
        $this->rewriteImagesInline($dom);
        $this->rewriteScripts($dom);
        libxml_clear_errors();
        $content = $this->extractBody($dom);
        return $content;
    }

    /**
     * Redirect to the page specified by a refresh meta tag
     *
     * Redirects to the first URL found in any meta tag of the form:
     *  <meta HTTP-EQUIV="REFRESH" content="0; url=pages/763F79EE5304A269.htm">
     * Mind that the "REFRESH" value is matched case sensitive.
     * Internal URLs are rewritten to match an existing path.
     * The target URL includes the module base URL, plus the "dv" parameter
     * with its value set to the target page path.
     * If no matching meta tag is found, this is a noop, and returns.
     * @param   \DOMDocument    $dom
     */
    protected function checkForRedirect(\DOMDocument $dom)
    {
        $metas = $dom->getElementsByTagName('meta');
        if ($metas) {
            foreach ($metas as $meta) {
                $http_equiv = $meta->getAttribute('http-equiv');
                $content = $meta->getAttribute('content');
                if ($http_equiv !== 'REFRESH' || !$content) {
                    continue;
                }
                $parts = explode('=', $content);
                $urlContent = array_pop($parts);
                if (strpos($urlContent, 'http') !== 0) {
                    $urlContent = $this->getBaseFolder() . '/' . $urlContent;
                    $urlContent = $this->checkFolderAvailability($urlContent,
                            $this->getBaseFolder() . '/');
                }
                $url = $this->getBaseUrl();
                $url->setParam('dv', $urlContent);
                \Cx\Core\Csrf\Controller\Csrf::redirect(
                    $url->toString(true, false)
                );
            }
        }
    }

    /**
     * Rewrite links referencing documents for downloading
     *
     * Note that the generated links are absolute (including protocol)
     * in order to avoid rewriting them again in rewriteUrls().
     * @param   \DOMDocument    $dom
     */
    protected function rewriteDocumentsProxy(\DOMDocument $dom)
    {
        $basePath = $this->getBasePath();
        $basePathLength = strlen($basePath);
        $url = $this->getBaseUrl();
        $url->setParam('proxy', '');
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (strpos($href, 'http') === 0) {
                continue;
            }
            $hrefLower = strtr($href, [
                '.PDF' => '.pdf',
                '.XLSX' => '.xlsx',
                '.XLS' => '.xls',
            ]);
            if (strpos($hrefLower, '.xls') === false
                && strpos($hrefLower, '.pdf') === false
            ) {
                continue;
            }
            // Mind that the file must exist for realpath() to work.
            $path = realpath($basePath . $this->getBaseFolder() . '/' . $href);
            if (!$path) {
                continue;
            }
            $path = substr($path, $basePathLength);
            $url->setParam('c', md5($path . static::SALT));
            $url->setParam('i', urlencode($path));
            $link->setAttribute('href', $url->toString());
        }
    }

    /**
     * Send the requested download, if any
     *
     * If any of the required URL parameters (proxy, c, i) is missing,
     * this is a noop.
     * Otherwise, it sends the download, and exits.
     */
    protected function sendDownload()
    {
        $request = $this->cx->getRequest();
        if (!(
            $request->hasParam('proxy')
            && $request->hasParam('c')
            && $request->hasParam('i')
        )) {
            return;
        }
        $check = $request->getParam('c');
        $url = urldecode($request->getParam('i'));
        $md5 = md5($url . static::SALT);
        if ($check !== $md5) {
            return;
        }
        $filePath = $this->getBasePath() . $url;
        $fileName = basename($url);
        $mediaType = \Mime::getMimeTypeForExtension($fileName);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-type: ' . $mediaType);
        readfile($filePath);
        exit();
    }

    /**
     * Rewrite links referencing local pages
     *
     * Restricts affected elements to the given tag name.
     * Ignores absolute links (starting with either "http", or "mailto"),
     * and anchor links (starting with "#").
     * Any other URL is transformed to the module base URL, plus the
     * corresponding path as the "dv" parameter value.
     * @param   \DOMDocument    $dom
     * @param   string          $tagname
     */
    protected function rewriteUrls(\DOMDocument $dom, $tagname = 'a')
    {
        $links = $dom->getElementsByTagName($tagname);
        $baseFolder = $this->getBaseFolder();
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            // Do not touch href values starting with any of:
            if (strpos($href, 'mailto') !== 0
                && strpos($href, 'http') !== 0
                && strpos($href, '#') !== 0
            ) {
                $param = explode('#', $href);
                $url = $this->getBaseUrl();
                $url->setParam('dv', $baseFolder . '/' . $param[0]);
                $urlString = $url->toString(false)
                    . (isset($param[1]) ? '#' . $param[1] : '');
                $link->setAttribute('href', $urlString);
            }
        }
    }

    /**
     *
     * @param   \DOMDocument    $dom
     */
    protected function rewriteAreas(\DOMDocument $dom)
    {
        $this->rewriteUrls($dom, 'area');
    }

    /**
     * Rewrite Javascript calls to jQuery
     * @param   \DOMDocument    $dom
     * @todo    Javascript calls are broken (e.g. "slideToogle")
     * @todo    "slideToggle" does not work either (at least those I've seen)
     */
    protected function rewriteScripts(\DOMDocument $dom)
    {
        $scripts = $dom->getElementsByTagName('script');
        foreach ($scripts as $script) {
            if ($script->nodeValue) {
                $script->nodeValue =
                    '$J(function($J){'
                    . strtr($script->nodeValue, ['$(' => '$J('])
                    . '});';
// TODO: Should/could cx.jQuery() be used instead?
//                $script->nodeValue =
//                    'cx.jQuery(function(){'
//                    . strtr($script->nodeValue, ['$(' => 'cx.jQuery('])
//                    . '});';
            }
        }
    }

    /**
     * Rewrite image tags with inline data
     * @param   \DOMDocument    $dom
     */
    protected function rewriteImagesInline(\DOMDocument $dom)
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
                    $img->setAttribute(
                        'src', 'data:' . $mime . ';base64,' . $data);
                } else {
                    $img->setAttribute(
                        'src',
                        'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs='
                    );
                }
            }
        }
    }

    /**
     *
     * @param   \DOMDocument    $dom
     * @return  string
     */
    protected function extractBody(\DOMDocument $dom): string
    {
        $body = $dom->getElementsByTagName('body');
        $content = '';
        foreach ($body as $element) {
            $content .= $this->domInnerHtml($element);
        }
        return $content . '<div style="clear:both;"></div>';
    }

    /**
     *
     * @param   \DOMElement $element
     * @return  string
     */
    protected function domInnerHtml(\DOMElement $element): string
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

    /**
     *
     * @global type $objInit
     * @return string
     */
    protected function guessStartFolder(): string
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

    /**
     *
     * @param   string  $path
     * @return  string
     */
    protected function checkFolderAvailability($path): string
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

    /**
     *
     * @global      \InitCMS    $objInit
     * @staticvar   string      $currentFolder
     * @return      string
     */
    protected function getCurrentFolder(): string
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

    /**
     *
     * @staticvar   string  $baseFolder
     * @return      string
     */
    protected function getBaseFolder(): string
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

    /**
     * Return the base URL
     *
     * Includes the module path, but no parameters.
     * @return  \Cx\Core\Routing\Url
     */
    protected function getBaseUrl(): \Cx\Core\Routing\Url
    {
        $url = clone \Env::get('Resolver')->getUrl();
        $url->removeAllParams();
// TODO: Is this a proper fix?  I don't want the (default) port to appear:
        $url->setPort(null);
        return $url;
    }

}
