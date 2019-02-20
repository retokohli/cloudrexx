<?php declare(strict_types=1);
/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.0 - 7.2
 *
 * @category  CloudrexxApp
 * @package   CHDIRMega4DV
 * @author    Comvation AG <info@comvation.com>
 * @copyright 2018 ch-direct
 * @link      https://www.comvation.com/
 *
 * Unauthorized copying, changing or deleting
 * of any file from this app is strictly prohibited
 *
 * Authorized copying, changing or deleting
 * can only be allowed by a separate contract
 */

namespace Cx\Modules\CHDIRMega4DV\Controller;

/**
 * FrontendController
 * @author      Reto Kohli <reto.kohli@comvation.com>
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
        $template->setTemplate($this->getContent());
// Enable along with using cx.jQuery in rewriteScripts() (or remove)
//        \JS::activate('cx');
    }

    /**
     * Return the effective content
     * @return  string
     */
    protected function getContent(): string
    {
        $this->sendDownload();
        $folder = $this->getCurrentFilePath();
        $path = $this->getBasePath() . $folder;
        if (!file_exists($path)) {
            return 'Sorry, could not find the file ' . $folder;
        }
        // Suppress warnings (you may disable this for testing)
        libxml_use_internal_errors(true);
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
                    $urlContent = $this->getBaseFolder() . $urlContent;
                    $urlContent = $this->matchPath($urlContent);
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
            $path = realpath($basePath . $this->getBaseFolder() . $href);
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
                $url->setParam('dv', $baseFolder . $param[0]);
                $urlString = $url->toString(false)
                    . (isset($param[1]) ? '#' . $param[1] : '');
                $link->setAttribute('href', $urlString);
            }
        }
    }

    /**
     * Rewrite links in area tags
     *
     * Works exactly as {@see rewriteUrls()}, except that it overrides
     * the tag name with "area".
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
// Should/could cx.jQuery() be used instead?
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
                $path = $basePath . $baseFolder . $img->getAttribute('src');
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
     * Return the content of the body tag from the current DOM
     *
     * Appends a div tag for separating it from following content.
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
     * Return the content of all children of the given element
     * @param   \DOMElement $element
     * @return  string
     */
    protected function domInnerHtml(\DOMElement $element): string
    {
        $innerHTML = '';
        $children = $element->childNodes;
        foreach ($children as $child) {
            $cloned = $child->cloneNode(true);
            $dom = new \DOMDocument();
            $dom->appendChild($dom->importNode($cloned, true));
            $innerHTML .= $dom->saveHTML();
        }
        return $innerHTML;
    }

    /**
     * Return the path of the current start page
     *
     * The path returned is relative to the one returned by
     * {@see getBasePath()}.
     * Finds the path of the latest data folder by date, and appends
     * the current language folder and index file name.
     * Does not test whether the resulting path exists.
     * @return  string
     */
    protected function guessStartPage(): string
    {
        $basePath = $this->getBasePath();
        $dirs = glob($basePath . '*', GLOB_ONLYDIR);
        $max = 0;
        foreach ($dirs as $dir) {
            $key = preg_replace('/[^0-9]/', '', $dir);
            if ($key > $max) {
                $goto = $dir;
                $max = $key;
            }
        }
        $folder = strtr($goto, [$basePath => '']);
        $lang = $this->cx->getRequest()->getUrl()->getLangDir();
        return $folder . '/' . $lang . '/index.htm';
    }

    /**
     * Return a fixed version of the given path, if possible
     *
     * The path must be relative to the one returned by {@see getBasePath()}.
     * Tries the original, lower, and upper case versions of the file name
     * trailing the given path, and returns the first that matches an
     * existing file.
     * If none matches, the original path is returned unchanged.
     * Many pages in the content are referenced using all uppercase names,
     * whereas the respective file names are written in lower case.
     * @param   string  $path
     * @return  string
     */
    protected function matchPath($path): string
    {
        $basePath = $this->getBasePath();
        $fullPath = $basePath . $path;
        $folderPath = dirname($fullPath);
        $fileName = basename($path);
        switch (true) {
            case file_exists($folderPath . '/' . $fileName):
                break;
            case file_exists($folderPath . '/' . strtolower($fileName)):
                $path = strtr($path, [$fileName => strtolower($fileName)]);
                break;
            case file_exists($folderPath . '/' . strtoupper($fileName)):
                // Note that this case does probably never match
                $path = strtr($path, [$fileName => strtoupper($fileName)]);
                break;
        }
        return $path;
    }

    /**
     * Return the current file path
     *
     * This path contains the folder path returned by {@see getBaseFolder()}.
     * If the "dv" URL parameter value is set, replaces its language part
     * with the current language directory, and tries finding a match
     * by calling {@see matchPath()}
     * Otherwise, returns the path returned by {@see guessStartPage()}.
     * @staticvar   string      $currentFilePath
     * @return      string
     */
    protected function getCurrentFilePath(): string
    {
        static $currentFilePath = null;
        if (!$currentFilePath) {
            $get = $this->cx->getRequest()->getParams();
            if (empty($get['dv'])) {
                $currentFilePath = $this->guessStartPage();
            } else {
                $lang = $this->cx->getRequest()->getUrl()->getLangDir();
                $currentFilePath = urldecode($get['dv']);
                $currentFilePath = strtr($currentFilePath,
                    [
                        '/de/' => '/' . $lang . '/',
                        '/fr/' => '/' . $lang . '/'
                    ]
                );
                $currentFilePath = $this->matchPath($currentFilePath);
            }
        }
        return $currentFilePath;
    }

    /**
     * Return the folder of the current page
     *
     * This folder path is relative to the one returned by {@see getBasePath()}.
     * Contains a trailing slash.
     * On empty "dv" parameter value, before redirecting to the start page,
     * this is something like, "ADV_2017-01-30/de/".
     * On any of the content pages, "ADV_2017-01-30/de/pages/".
     * @staticvar   string  $baseFolder
     * @return      string
     */
    protected function getBaseFolder(): string
    {
        static $baseFolder = null;
        if (!$baseFolder) {
            $baseFolder = dirname($this->getCurrentFilePath()) . '/';
        }
        return $baseFolder;
    }

    /**
     * Return the absolute filesystem path of the contents folder
     *
     * This folder is the parent of the one returned by {@see getBaseFolder()}.
     * This includes a trailing slash, and is typically something like:
     *  /var/www/html/media/CHDIRMega4DV/
     * @return  string
     */
    protected function getBasePath(): string
    {
        $basePath = $this->cx->getWebsiteDocumentRootPath()
            . '/media/' . $this->getName() . '/';
        return $basePath;
    }

    /**
     * Return the base URL
     *
     * Includes the module path, but no parameters.
     * @return  \Cx\Core\Routing\Url
     */
    protected function getBaseUrl(): \Cx\Core\Routing\Url
    {
        $baseurl = clone \Env::get('Resolver')->getUrl();
        $baseurl->removeAllParams();
// TODO: Is this a proper fix?  I don't want the (default) port to appear:
        $baseurl->setPort(null);
        return $baseurl;
    }

}
