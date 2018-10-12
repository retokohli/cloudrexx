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
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController {

    /**
     * Use this to parse your frontend page
     *
     * You will get a template based on the content of the resolved page
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd) {
        // this class inherits from Controller, therefore you can get access to
        // Cx like this:
        $this->cx;

        // Controller routes all calls to undeclared methods to your
        // ComponentController. So you can do things like
        $this->getName();
    }
}


// TODO from index.class.php

// TODO: This is apparently obsolete
//require 'lib/QueryPath/qp.php';

/**
 * MEGA4DV module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author	Comvation Development Team <info@comvation.com>
 * @author      Red Ochsenbein <red.ochsenbein@comvation.com>
 * @version	1.0.0
 * @package     contrexx
 * @subpackage  module_mega4dv
 */
class Mega4Dv
{
    /**
     * The basepath of the current parsing location
     *
     * @access private
     * @var string
     */
    var $_basepath;

    /**
     * The current parsing location
     *
     * @access private
     * @var string
     */
    var $_currentPath;

    /**
     * Get content page
     *
     * @access public
     */
    function getPage()
    {
        $path = $this->getCurrentPath();

        $file = ASCMS_DOCUMENT_ROOT . '/mega4dv/' . $path;
        if (!file_exists($file)) {
            return 'Sorry, could not find the file ' . $path;
        }

        $content = file_get_contents($file);

        $dom = new \DOMDocument();
        $dom->loadHTML($content);

        libxml_use_internal_errors(TRUE);

        $redirect = $this->checkForRedirect($dom);
        if ($redirect) {
            return $redirect;
        }

        $xpath = new DOMXPath($dom);
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
        return '<div><div><div><div><div><div><div><div><div><div><div><div>' . $content . '';
    }

    function checkForRedirect($dom)
    {
        $metas = $dom->getElementsByTagName('meta');
        $basepath = $this->getBasePath();
        if ($metas) {
            foreach ($metas as $meta) {
                $http_equiv = $meta->getAttribute('http-equiv');
                $url = $meta->getAttribute('content');
                if ($http_equiv && $http_equiv == 'REFRESH' && $url) {
                    $parts = explode('=', $url);
                    $url = array_pop($parts);

                    if (strpos($url, 'http') !== 0) {
                        $url = $basepath . '/' . $url;
                        $url = $this->checkPathAvailability($url,
                                $basepath . '/');
                    }
                    $myUrl = clone \Env::get('Resolver')->getUrl();
                    $myUrl->setParam('dv', $url);

                    $redirect = '<script>document.location="' . $myUrl . '";</script>';
                    return $redirect;
                }
            }
        }
        return false;
    }

    function rewriteUrls($dom, $tagname = 'a')
    {
        $links = $dom->getElementsByTagName($tagname);
        $basepath = $this->getBasePath();
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (
                strpos($href, '/imageproxy.php') !== 0 &&
                strpos($href, 'mailto') !== 0 &&
                strpos($href, 'http') !== 0 &&
                strpos($href, '#') !== 0
            ) {
                $param = explode('#', $href);
                $host = isset($_SERVER['HTTPS_HOST']) ? $_SERVER['HTTPS_HOST'] : $_SERVER['HTTP_HOST'];
                $url = new Cx\Lib\Net\Model\Entity\Url(ASCMS_PROTOCOL . '://' . $host . $_GET['__cap']);
                $url->setParam('dv', $basepath . '/'. $param[0]);
                if (isset($param[1])) {
                    $url->setFragment($param[1]);
                }
                $link->setAttribute('href', $url);
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
                $script->nodeValue = '$J(document).ready(function($J){' . strtr($script->nodeValue,
                                array('$(' => '$J(')) . '});';
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

    function _rewriteImagesSimple($dom)
    {
        $basepath = $this->getBasePath();
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if (strpos($img->getAttribute('src'), 'http') !== 0) {
                $url = '/mega4dv/' . $basepath . '/' . $img->getAttribute('src');
                $img->setAttribute('src', $url);
            }
        }
    }

    function rewriteDocumentsProxy($dom)
    {

        $links = $dom->getElementsByTagName('a');

        foreach ($links as $link) {
            if (strpos($link->getAttribute('href'), 'http') !== 0 && strpos(strtolower($link->getAttribute('href')),
                            'xls') !== false || strpos(strtolower($link->getAttribute('href')),
                            'pdf') !== false) {
                $path = $this->getBasePath() .'/' . $link->getAttribute('href');
                $url = '/imageproxy.php?c=' . md5($path . '38364DC9-FAEF-406E-B6A7-DFB0C83F1CBE') . '&i=' . urlencode($path);
                $link->setAttribute('href', $url);
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

    function _rewriteImagesInline($dom)
    {
        $basepath = $this->getBasePath();
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if (strpos($img->getAttribute('src'), 'http') !== 0) {
                $path = ASCMS_DOCUMENT_ROOT . '/mega4dv/' . $basepath . '/' . $img->getAttribute('src');

                if (file_exists($path)) {
                    $mime = $this->getMimeType($path);
                    $data = base64_encode(file_get_contents($path));
                    $img->setAttribute('src', 'data:' . $mime . ';base64,' . $data);
                } else {
                	$img->setAttribute('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs=');
                }
            }
        }
    }

    function extractBody($dom)
    {
        $body = $dom->getElementsByTagName('body');

        $content = '<style>b {font-weight: bold;}#content a:hover {text-decoration:underline;}article#content table tr {background-color:white !important;}#content table td {padding: 1px 0 1px 1px;}ul{margin:0;padding:0;list-style-type:none;}li{margin:0;padding:0;}#naviDiv{display:none;}img{/*max-width:100%;*/}.klapp>div:first-child{margin-top:-32px!important;}#content table {width:100%;}#subnavigation,aside#sidebar{display:none;}#main article#content{width:calc(100% - 30px)}.bg4{background-color: #666;color:white;}.bg4 a {color: white;}article#content img {margin:0;}article#content ul {list-style-type: square;margin-left:5px;}article#content li {padding-left:0 !important;}footer#content-meta{display:none;}</style>';
        $content .= '<script>
    if (!evaluateURLAndOpenKlappi) {
        function evaluateURLAndOpenKlappi() {
		var klappiId = getParam("open")
		if (klappiId) {
			document.location = "#Link"+klappiId;
			$J("#klappi"+klappiId).slideToggle("fast");
		}
	}

	/*  This function gets the value of the URL parameter provided */
	function getParam(variable){
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
			var pair = vars[i].split("=");
			if(pair[0] == variable){
				return pair[1];
			}
		}
		return(false);
	}
	 $J(document).ready(function(){evaluateURLAndOpenKlappi()});
	}
</script>';

        foreach ($body as $element) {
            $content .= $this->domInnerHtml($element);
        }
        return $content . "<div style='clear:both;'></div>";
    }

    function domInnerHtml($element)
    {
        $innerHTML = "";
        $children = $element->childNodes;

        foreach ($children as $child) {
            $dom2 = new DOMDocument();
            $cloned = $child->cloneNode(true);
            $dom2->appendChild($dom2->importNode($cloned, true));
            $innerHTML .= $dom2->saveHTML();
        }

        return $innerHTML;
    }

    function parseUri()
    {
        list($request_url, $request_query) = explode('?',
                $_SERVER['REQUEST_URI']);
        parse_str($request_query, $get);
        return $get;
    }

    function getCurrentPath()
    {
    	global $objInit;
    	$lang = 'de';

        $langId = $objInit->getFrontendLangId();
        $lang = FWLanguage::getLanguageCodeById($langId);

        $basepath = ASCMS_DOCUMENT_ROOT . '/mega4dv/';
        if ($this->_currentPath === null) {
            $get = $this->parseUri();
            if (empty($get['dv'])) {
                $path = 'index.htm';
                $path = $this->checkPathAvailability($path);
                if (!file_exists($path)) {
                    $path = $this->guessStartPath();
                }
            } else {
                $path = strtr($get['dv'], '.', '');
                $path = strtr($path, array('/de/'=>"/$lang/", '/fr/'=>"/$lang/"));
                $path = $this->checkPathAvailability($path);
            }
            $this->_currentPath = $path;
        }
        return $this->_currentPath;
    }

    function guessStartPath()
    {
    	global $objInit;
    	$lang = 'de';

        $langId = $objInit->getFrontendLangId();
        $lang = FWLanguage::getLanguageCodeById($langId);

        $base = ASCMS_DOCUMENT_ROOT . '/mega4dv/';
        $dirs = glob($base . "/*", GLOB_ONLYDIR);
        $max = 0;
        foreach ($dirs as $dir) {
        	$key = preg_replace("/[^0-9]/","",$dir);
        	if ($key > $max) {
        		$goto = $dir;
        		$max = $key;
        	}
        }
        if(file_exists($goto . '/de/index.htm') || file_exists($goto . '/fr/index.htm')) {
        	return strtr($goto, array($base => '')) . '/' . $lang . '/index.htm';
        }
        return strtr($goto, array($base => '')) . '/index.htm';
    }

    function checkPathAvailability($path)
    {
        $base = ASCMS_DOCUMENT_ROOT . '/mega4dv';
        $full = $base . $path;
        $dir = dirname($full);
        $file = basename($full);
        switch (true) {
            case file_exists($dir . '/' . $file):
                break;
            case file_exists($dir . '/' . strtolower($file)):
                $path = strtr($path, array($file => strtolower($file)));
                break;
            case file_exists($dir . '/' . strtoupper($file)):
                $path = strtr($path, array($file => strtoupper($file)));
                break;
            default:
                break;
        }
        return $path;
    }

    function getBasePath()
    {
        static $basepath = null;
        if (!$basepath) {
            $path = $this->getCurrentPath();
            $basepath = explode('/', $path);
            array_pop($basepath);
            $basepath = implode('/', $basepath);
        }
        return $basepath;
    }

    function getMimeType($filename)
    {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

        switch (strtolower($fileSuffix[1])) {
            case "jpg" :
            case "jpeg" :
            case "jpe" :
                return "image/jpg";

            case "png" :
            case "gif" :
            case "bmp" :
            case "tiff" :
                return "image/" . strtolower($fileSuffix[1]);

            default :
                if (function_exists("mime_content_type")) {
                    $fileSuffix = mime_content_type($filename);
                }

                return "unknown/" . trim($fileSuffix[0], ".");
        }
    }

}
