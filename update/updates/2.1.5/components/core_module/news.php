<?php

////////////////////////////////////////////////////
//Those are 1207 lines of code copied and pasted in here due to update packaging restrictions.
//I suggest searching for the END OF-tag to skip this
////////////////////////////////////////////////////
//BEGIN OF NEWS CONVERTING STUFF

//from validator.inc.php

/**
 * Validator
 *
 * Global request validator
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 * @todo        Isn't this supposed to be a class?
 */

//Security-Check
if (eregi("validator.inc.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}


/**
 * strip_tags wrapper
 *
 * @param     string     $string
 * @return    string     $string (cleaned)
 */
function contrexx_strip_tags($string)
{
    if (CONTREXX_ESCAPE_GPC) {
    return strip_tags($string);
    }
    return addslashes(strip_tags($string));
}


/**
 * addslashes wrapper to check for gpc_magic_quotes - gz
 * @param     string     $string
 * @return    string              cleaned
 */
function contrexx_addslashes($string)
{
    // if magic quotes is on, the string is already quoted
    if (CONTREXX_ESCAPE_GPC) {
        return $string;
    }
    return addslashes($string);
}


/**
 * stripslashes wrapper to check for gpc_magic_quotes
 * @param   string    $string
 * @return  string
 */
function contrexx_stripslashes($string)
{
    if (CONTREXX_ESCAPE_GPC) {
        return stripslashes($string);
    }
    return $string;
}

/**
 * Convenient match-and-replace-in-one function
 *
 * Parameters are those of preg_match() and preg_replace() combined.
 * @param   string  $pattern      The regex pattern to match
 * @param   string  $replace      The replacement string for matches
 * @param   string  $subject      The string to be matched/replaced on
 * @param   array   $subpatterns  The optional array for the matches found
 * @param   integer $limit        The optional limit for replacements
 * @param   integer $count        The optional counter for the replacements done
 * @return  string                The resulting string
 */
function preg_match_replace(
    $pattern, $replace, $subject, &$subpatterns=null, $limit=-1, &$count=null
) {
    if (preg_match($pattern, $subject, $subpatterns)) {
        $subject = preg_replace($pattern, $replace, $subject, $limit, $count);
//echo("preg_match_replace(<br />pattern $pattern,<br />replace $replace,<br />subject $subject,<br />subpatterns ".var_export($subpatterns, true).",<br />limit $limit,<br />count $count<br />): Match, made ".htmlentities($subject)."<br />");
        return $subject;
    }
//echo("NO match<br />");
    return $subject;
}


/**
 * Checks if the request comes from a spider
 *
 * @return  boolean
 */
function checkForSpider()
{
    $arrRobots = array();
    require_once ASCMS_CORE_MODULE_PATH.'/stats/lib/spiders.inc.php';
    $useragent =  htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, CONTREXX_CHARSET);
    foreach ($arrRobots as $spider) {
        $spiderName = trim($spider);
        if (preg_match("=".$spiderName."=",$useragent)) {
            return true;
            break;
        }
    }
    return false;
}


/////////////////////////////////////////////////////////////
//convenience escaping function layer - use these rather than
//contrexx_addslashes() and so on please.

/**
 * Escapes a raw string, e.g. from the db. The resulting string can be safely
 * written to the XHTML response.
 * @param string $raw
 * @return the escaped string
 */
function contrexx_raw2xhtml($raw) {
    return htmlentities($raw, ENT_QUOTES, CONTREXX_CHARSET);
}

/**
 * Unescapes an input string (from Get/Post/Cookie) as necessary so you get a raw string.
 * @param string $input
 * @return the raw string
 */
function contrexx_input2raw($input) {
  return  contrexx_stripslashes($input);
}

/**
 * Adds slashes so you can insert a string into the database
 * @param string $raw
 * @return the escaped string
 */
function contrexx_raw2db($raw) {
    return addslashes($raw);
}

/**
 * Escapes a raw string, e.g. from the db. The resulting string can be safely
 * written to an XML target.
 * @param string $raw
 * @return the escaped string
 */
function contrexx_raw2xml($raw) {
    return htmlspecialchars($raw, ENT_QUOTES, CONTREXX_CHARSET);
}

/**
 * Encode the given url so that it can be used as a a.href or img.src attribute value.
 * @param string $raw
 * @param boolean $encodeDash whether to encode dashes ('/') too - defaults to false
 * @return string
 */
function contrexx_raw2encodedUrl($source, $encodeDash = false)
{
    $cutHttp = false;
    if(!$encodeDash && substr($source,0,7) == "http://") {
        $source = substr($source, 7);
        $cutHttp = true;
    } 

    $source = array_map('rawurlencode', explode('/', $source));

    if ($encodeDash) {
        $source = str_replace('-', '%2D', $source);
    }

    $result = implode('/', $source);

    if($cutHttp)
        $result = "http://".$result;

    return $result;
}

/**
 * Remove script tags and their content from the string given
 * @param string $raw
 * @return string scriptless string.
 * @todo check for eventhandlers (onclick and the like)
 */
function contrexx_remove_script_tags($raw) {
    //remove closed script tags and content
    $result = preg_replace('#<\s*script[^>]*>.*?<\s*/script\s*>#is','',$raw);
    //remove unclosed script tags
    $result = preg_replace('#<\s*script[^>]*>#is', '',$result);
    return $result;
}
//from language.class.php

/**
 * Framework language
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Framework language
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class FWLanguage
{
    private static $arrLanguages = false;

    /**
     * ID of the default language
     *
     * @var integer
     * @access private
     */
    private static $defaultLangId;

    /**
     * Loads the language config from database.
     *
     * This used to be in __construct but is also
     * called from core/language.class.php to reload
     * the config, so core/settings.class.php can
     * rewrite .htaccess (virtual lang dirs).
     */
    static function init()
    {
        global $objDatabase;

         $objResult = $objDatabase->Execute("
            SELECT id, lang, name, charset, themesid,
                   frontend, backend, is_default
              FROM ".DBPREFIX."languages
             ORDER BY id
         ");
         if ($objResult) {
             while (!$objResult->EOF) {
                self::$arrLanguages[$objResult->fields['id']] = array(
                    'id'         => $objResult->fields['id'],
                    'lang'       => $objResult->fields['lang'],
                    'name'       => $objResult->fields['name'],
                    'charset'    => $objResult->fields['charset'],
                    'themesid'   => $objResult->fields['themesid'],
                    'frontend'   => $objResult->fields['frontend'],
                    'backend'    => $objResult->fields['backend'],
                    'is_default' => $objResult->fields['is_default'],
                );

                if ($objResult->fields['is_default'] == 'true') {
                    self::$defaultLangId = $objResult->fields['id'];
                }

                $objResult->MoveNext();
            }
        }
    }


    /**
     * Returns the ID of the default language
     *
     * @return integer Language ID
     */
    static function getDefaultLangId()
    {
        if (empty(self::$defaultLangId)) {
            self::init();
        }

        return self::$defaultLangId;
    }


    /**
     * Returns the complete language data
     * @see     FWLanguage()
     * @return  array           The language data
     * @access  public
     */
    static function getLanguageArray()
    {
        if (empty(self::$arrLanguages)) self::init();
        return self::$arrLanguages;
    }


    /**
     * Returns single language related fields
     *
     * Access language data by specifying the language ID and the index
     * as initialized by {@link FWLanguage()}.
     * @return  mixed           Language data field content
     * @access  public
     */
    static function getLanguageParameter($id, $index)
    {
        if (empty(self::$arrLanguages)) self::init();
        return
            (isset(self::$arrLanguages[$id][$index])
                ? self::$arrLanguages[$id][$index] : false
            );
    }


    /**
     * Returns HTML code to display a language selection dropdown menu.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * @param   integer $selectedId The optional preselected language ID
     * @param   string  $menuName   The optional menu name
     * @param   string  $onchange   The optional onchange code
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getMenu($selectedId=0, $menuName='', $onchange='')
    {
        $menu = self::getMenuoptions($selectedId, true);
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'".
                    ($onchange ? ' onchange="'.$onchange.'"' : '').
                    ">\n$menu</select>\n";
        }
//echo("getMenu(select=$selectedId, name=$menuName, onchange=$onchange): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Returns HTML code to display a language selection dropdown menu
     * for the active frontend languages only.
     *
     * Does only contain the <select> tag pair if the optional $menuName
     * is specified and evaluates to a true value.
     * Frontend use only.
     * @param   integer $selectedId The optional preselected language ID
     * @param   string  $menuName   The optional menu name
     * @param   string  $onchange   The optional onchange code
     * @return  string              The dropdown menu HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getMenuActiveOnly($selectedId=0, $menuName='', $onchange='')
    {
        $menu = self::getMenuoptions($selectedId, false);
        if ($menuName) {
            $menu = "<select id='$menuName' name='$menuName'".
                    ($onchange ? ' onchange="'.$onchange.'"' : '').
                    ">\n$menu</select>\n";
        }
//echo("getMenu(select=$selectedId, name=$menuName, onchange=$onchange): made menu: ".htmlentities($menu)."<br />");
        return $menu;
    }


    /**
     * Returns HTML code for the language menu options
     * @param   integer $selectedId   The optional preselected language ID
     * @param   boolean $flagInactive If true, all languages are added,
     *                                only the active ones otherwise
     * @return  string                The menu options HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getMenuoptions($selectedId=0, $flagInactive=false)
    {
        if (empty(self::$arrLanguages)) self::init();
        $menuoptions = '';
        foreach (self::$arrLanguages as $id => $arrLanguage) {
            // Skip inactive ones if desired
            if (!$flagInactive && empty($arrLanguage['frontend']))
                continue;
            $menuoptions .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').
                ">{$arrLanguage['name']}</option>\n";
        }
        return $menuoptions;
    }


    /**
     * Return the language ID for the ISO 639-1 code specified.
     *
     * If the code cannot be found, returns the default language.
     * If that isn't set either, returns the first language encountered.
     * If none can be found, returns boolean false.
     * Note that you can supply the complete string from the Accept-Language
     * HTTP header.  This method will take care of chopping it into pieces
     * and trying to pick a suitable language.
     * However, it will not pick the most suitable one according to RFC2616,
     * but only returns the first language that fits.
     * @static
     * @param   string    $langCode         The ISO 639-1 language code
     * @return  mixed                       The language ID on success,
     *                                      false otherwise
     * @global  ADONewConnection
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getLangIdByIso639_1($langCode)
    {
        global $objDatabase;

        // Something like "fr; q=1.0, en-gb; q=0.5"
        $arrLangCode = preg_split('/,\s*/', $langCode);
        $strLangCode = "'".join("', '", preg_replace('/(?:-\w+)?(?:;\s*q(?:\=\d?\.?\d*)?)?/i', '', $arrLangCode))."'";

        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE lang IN ($strLangCode)
               AND frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // The code was not found.  Pick the default.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE is_default='true'
               AND frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Still nothing.  Pick the first frontend language available.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Pick the first language.
        $objResult = $objDatabase->Execute("
            SELECT id
              FROM ".DBPREFIX."languages
             WHERE frontend=1
        ");
        if ($objResult && $objResult->RecordCount() > 0) {
            return $objResult->fields['id'];
        }
        // Give up.
        return false;
    }


    /**
     * Return the language code from the database for the given ID
     *
     * Returns false on failure, or false if the code could not be found.
     * @global  ADONewConnection
     * @param   integer $langId         The language ID
     * @return  mixed                   The two letter code, or false
     * @static
     */
    static function getLanguageCodeById($langId)
    {
        if (empty(self::$arrLanguages)) self::init();
        return self::getLanguageParameter($langId, 'lang');
    }

}

//from RSSWriter.class.php
/**
 * RSSWriter
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author Astalavista Development Team <thun@astalavista.ch>
 * @version 2.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * RSSWriter
 *
 * Creates RSS files
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author Astalavista Development Team <thun@astalavista.ch>
 * @version 2.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class RSSWriter {
    var $xmlDocumentPath;
    var $characterEncoding;
    var $xmlDocument;
    var $feedType = 'xml'; // allowed types: xml, js
    var $_rssVersion = '2.0';
    var $_xmlVersion = '1.0';
    var $_xmlElementLevel = 0;

    var $arrErrorMsg = array();
    var $arrWarningMsg = array();

    var $channelTitle = ''; //'Contrexx.com Neuste Videos';
    var $channelLink = ''; //'http://www.contrexx.com/podcast';
    var $channelDescription = ''; //'Neuste Videos';

    var $channelLanguage = '';
    var $channelCopyright = '';
    var $channelManagingEditor = '';
    var $channelWebMaster = '';
    var $channelPubDate = '';
    var $channelLastBuildDate = '';
    var $channelCategory = '';
    var $channelGenerator = '';
    var $channelDocs = '';
    var $channelCloud = '';
    var $channelTtl = '';

    var $channelImageUrl = '';
    var $channelImageTitle = '';
    var $channelImageLink = '';

    var $channelImageWidth = '';
    var $channelImageHeight = '';
    var $channelImageDescription = '';

    var $channelRating = '';

    var $channelTextInputTitle = '';
    var $channelTextInputDescription = '';
    var $channelTextInputName = '';
    var $channelTextInputLink = '';

    var $channelSkipHours = '';
    var $channelSkipDays = '';

    var $_arrItems = array();
    var $xmlItems = '';
    var $_currentItem = 0;

    /**
     * PHP4 Contructor
     *
     */
    function RSSWriter()
    {
        $this->__construct();
    }

    /**
     * PHP5 contructor
     *
     */
    function __construct()
    {
        global $_CONFIG;

        $this->channelGenerator = $_CONFIG['coreCmsName'];
        $this->channelDocs = 'http://blogs.law.harvard.edu/tech/rss';
    }

    /**
     * Add item
     *
     * Add an item to the RSS feed
     *
     * @param stirng $title
     * @param string $link
     * @param stirng $description
     * @param string $author
     * @param array $arrCategory
     * @param string $comments
     * @param array $arrEnclosure
     * @param array $arrGuid
     * @param string $pubDate
     * @param array $arrSource
     * @return boolean
     */
    function addItem($title = '', $link = '', $description = '', $author = '', $arrCategory = array(), $comments = '', $arrEnclosure = array(), $arrGuid = array(), $pubDate = '', $arrSource = array())
    {
        global $_CORELANG;

        if (!empty($title) || !empty($description)) {
            array_push($this->_arrItems, array(
                'title'         => $title,
                'link'          => $link,
                'description'   => $description,
                'author'        => $author,
                'arrCategory'   => $arrCategory,
                'comments'      => $comments,
                'arrEnclosure'  => $arrEnclosure,
                'arrGuid'       => $arrGuid,
                'pubDate'       => $pubDate,
                'arrSource'     => $arrSource
            ));

            return true;
        } else {
            array_push($this->arrErrorMsg, $_CORELANG['TXT_MUST_DEFINE_RSS_TITLE_OR_DESCRIPTION']);
            return false;
        }
    }

    /**
     * Write feed
     *
     * Writes the rss feed.
     *
     * @return boolean
     */
    function write()
    {
        global $_CORELANG;

        if ($this->_create()) {
            if (($xmlDocument = @fopen($this->xmlDocumentPath, "w+")) !== false) {
                $writeStatus = @fwrite($xmlDocument, $this->xmlDocument);
                @fclose($xmlDocument);

                if ($writeStatus) {
                    return true;
                } else {
                    array_push($this->arrErrorMsg, sprintf($_CORELANG['TXT_UNABLE_TO_WRITE_TO_FILE'], $this->xmlDocumentPath));
                    return false;
                }
            } else {
                array_push($this->arrErrorMsg, sprintf($_CORELANG['TXT_UNABLE_TO_CREATE_FILE'], $this->xmlDocumentPath));
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove items
     *
     * Removes the items from the rss writer object.
     */
    function removeItems()
    {
        $this->_arrItems = array();
    }

    /**
     * Create feed
     *
     * Create the content of the RSS feed.
     *
     * @return boolean
     */
    function _create()
    {
        switch ($this->feedType) {
            case 'js':
                return $this->_createJS();
                break;

            case 'xml':
            default:
                return $this->_createXML();
                break;
        }
    }

    function _createXML()
    {
        global $_CORELANG;

        if (!empty($this->characterEncoding)) {
            $this->xmlDocument = $this->_visualElementLevel()."<?xml version=\"".$this->_xmlVersion."\" encoding=\"".$this->characterEncoding."\"?>\n";
        } else {
            array_push($this->arrErrorMsg, $_CORELANG['TXT_NO_CHARACTER_ENCODING_DEFINED']);
            return false;
        }
        $this->xmlDocument .= $this->_visualElementLevel()."<rss version=\"".$this->_rssVersion."\">\n";
        $this->_xmlElementLevel++;

        $this->xmlDocument .= $this->_visualElementLevel()."<channel>\n";
        $this->_xmlElementLevel++;
        if ($this->_addChannelTitle()) {
            if ($this->_addChannelLink()) {
                if ($this->_addChannelDescription()) {
                    $this->_addOptionalChannelElements();

                    $this->_parseItems();
                } else {
                array_push($this->arrErrorMsg, $_CORELANG['TXT_FEED_NO_CHANNEL_DESCRIPTION']);
                return false;
            }
            } else {
                array_push($this->arrErrorMsg, $_CORELANG['TXT_FEED_NO_CHANNEL_LINK']);
                return false;
            }
        } else {
            array_push($this->arrErrorMsg, $_CORELANG['TXT_FEED_NO_CHANNEL_TITLE']);
            return false;
        }
        $this->_xmlElementLevel--;
        $this->xmlDocument .= $this->_visualElementLevel()."</channel>\n";
        $this->_xmlElementLevel--;
        $this->xmlDocument .= $this->_visualElementLevel()."</rss>\n";

        return true;
    }

    function _createJS()
    {
        $this->xmlDocument = <<<XMLJSOUTPUT
if (document.body) {
	document.write('<div id="news_js_rss_feed"></div>');
}
fnWinOnload = window.onload;
window.onload = function() {
    if (typeof(fnWinOnload) != 'undefined' && fnWinOnload != null) {
        fnWinOnload();
    }

    var rssFeedNews = new Array();
XMLJSOUTPUT;

        $nr = 0;

        foreach ($this->_arrItems as $arrItem) {
            $this->xmlDocument .= "rssFeedNews[".$nr."] = new Array();\n";
            $this->xmlDocument .= "rssFeedNews[".$nr."]['title'] = '".addslashes(($arrItem['title']))."';\n";
            $this->xmlDocument .= "rssFeedNews[".$nr."]['link'] = '".$arrItem['link']."';\n";
            $this->xmlDocument .= "rssFeedNews[".$nr."]['date'] = '".date(ASCMS_DATE_SHORT_FORMAT, $arrItem['pubDate'])."';\n";
            $nr++;
        }
		$utf8_fixed = $this->_js_umlauts($this->xmlDocument);
		if ($utf8_fixed) $this->xmlDocument = $utf8_fixed;


        $this->xmlDocument .= <<<XMLJSOUTPUT
if (typeof rssFeedFontColor != "string") {
    rssFeedFontColor = "";
} else {
    rssFeedFontColor = "color:"+rssFeedFontColor+";";
}
if (typeof rssFeedFontSize != "number") {
    rssFeedFontSize = "";
} else {
    rssFeedFontSize = "font-size:"+rssFeedFontSize+";";
}
if (typeof rssFeedTarget != "string") {
    rssFeedTarget = "target=\"_blank\"";;
} else {
    rssFeedTarget = "target=\""+rssFeedTarget+"\"";
}
if (typeof rssFeedFont != "string") {
    rssFeedFont = "";
} else {
    rssFeedFont = "font-family:"+rssFeedFont+";";
}
if (typeof rssFeedShowDate != "boolean") {
    rssFeedShowDate = false;
}

if (typeof rssFeedFontColor == "string" || typeof rssFeedFontSize != "number" || typeof rssFeedFont != "string") {
    style = 'style="'+rssFeedFontColor+rssFeedFontSize+rssFeedFont+'"';
}

if (typeof rssFeedLimit != 'number') {
    rssFeedLimit = 10;
}
if (rssFeedNews.length < rssFeedLimit) {
    rssFeedLimit = rssFeedNews.length;
}

    rssFeedContainer = document.getElementById('news_js_rss_feed');
    rssFeedContainer.innerHTML = '';

var rssFeedNewsDate = "";
for (nr = 0; nr < rssFeedLimit; nr++) {
    if (rssFeedShowDate) {
        rssFeedNewsDate = rssFeedNews[nr]['date'];
    }
        rssCode = '<a href="'+rssFeedNews[nr]['link']+'" '+rssFeedTarget+' '+style+'>'+rssFeedNewsDate+' '+rssFeedNews[nr]['title']+'</a><br />';
        rssFeedContainer.innerHTML += rssCode;
    }
}
XMLJSOUTPUT;

        return true;
    }

    /**
     * Add channel title
     *
     * Adds the channel title to the feed.
     *
     * @return boolean
     */
    function _addChannelTitle()
    {
        if (!empty($this->channelTitle)) {
            $this->xmlDocument .= $this->_visualElementLevel()."<title>".$this->channelTitle."</title>\n";
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add channel link
     *
     * Adds the link title to the feed.
     * @return boolean
     */
    function _addChannelLink()
    {
        if (!empty($this->channelLink)) {
            $this->xmlDocument .= $this->_visualElementLevel()."<link>".$this->channelLink."</link>\n";
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add channel description
     *
     * Adds the channel description to the feed.
     * @return boolean
     */
    function _addChannelDescription()
    {
        if (!empty($this->channelDescription)) {
            $this->xmlDocument .= $this->_visualElementLevel()."<description>".$this->channelDescription."</description>\n";
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add optional channel elements
     *
     * Adds all the optional channel elements to the feed.
     * @return boolean
     */
    function _addOptionalChannelElements()
    {
        if (!empty($this->channelLanguage)) $this->xmlDocument .= $this->_visualElementLevel()."<language>".$this->channelLanguage."</language>\n";
        if (!empty($this->channelCopyright)) $this->xmlDocument .= $this->_visualElementLevel()."<copyright>".$this->channelCopyright."</copyright>\n";
        if (!empty($this->channelManagingEditor)) $this->xmlDocument .= $this->_visualElementLevel()."<managingEditor>".$this->channelManagingEditor."</managingEditor>\n";
        if (!empty($this->channelWebMaster)) $this->xmlDocument .= $this->_visualElementLevel()."<webMaster>".$this->channelWebMaster."</webMaster>\n";
        if (!empty($this->channelPubDate)) $this->xmlDocument .= $this->_visualElementLevel()."<pubDate>".$this->channelPubDate."</pubDate>\n";
        if (!empty($this->channelLastBuildDate)) $this->xmlDocument .= $this->_visualElementLevel()."<lastBuildDate>".$this->channelLastBuildDate."</lastBuildDate>\n";
        if (!empty($this->channelCategory)) $this->xmlDocument .= $this->_visualElementLevel()."<category>".$this->channelCategory."</category>\n";
        if (!empty($this->channelGenerator)) $this->xmlDocument .= $this->_visualElementLevel()."<generator>".$this->channelGenerator."</generator>\n";
        if (!empty($this->channelDocs)) $this->xmlDocument .= $this->_visualElementLevel()."<docs>".$this->channelDocs."</docs>\n";
        if (!empty($this->channelCloud)) $this->xmlDocument .= $this->_visualElementLevel()."<cloud>".$this->channelCloud."</cloud>\n";
        if (!empty($this->channelTtl)) $this->xmlDocument .= $this->_visualElementLevel()."<ttl>".$this->channelTtl."</ttl>\n";
        if (!empty($this->channelImageUrl) && !empty($this->channelImageTitle) && !empty($this->channelImageLink)) {
            $this->xmlDocument .= $this->_visualElementLevel()."<image>\n";

            $this->_xmlElementLevel++;
            $this->xmlDocument .= $this->_visualElementLevel()."<url>".$this->channelImageUrl."</url>\n";
            $this->xmlDocument .= $this->_visualElementLevel()."<title>".$this->channelImageTitle."</title>\n";
            $this->xmlDocument .= $this->_visualElementLevel()."<link>".$this->channelImageLink."</link>\n";
            if (!empty($this->channelImageWidth)) $this->xmlDocument .= $this->_visualElementLevel()."<width>".$this->channelImageWidth."</width>\n";
            if (!empty($this->channelImageHeight)) $this->xmlDocument .= $this->_visualElementLevel()."<height>".$this->channelImageHeight."</height>\n";
            if (!empty($this->channelImageDescription)) $this->xmlDocument .= $this->_visualElementLevel()."<description>".$this->channelImageDescription."</description>\n";
            $this->_xmlElementLevel--;
            $this->xmlDocument .= $this->_visualElementLevel()."</image>\n";
        }

        if (!empty($this->channelRating)) $this->xmlDocument .= $this->_visualElementLevel()."<rating>".$this->channelRating."</rating>\n";
        if (!empty($this->channelTextInputTitle) && !empty($this->channelTextInputDescription) && !empty($this->channelTextInputName) && !empty($this->channelTextInputLink)) {
            $this->$this->xmlDocument .= $this->_visualElementLevel()."<textInput>\n";

            $this->_xmlElementLevel++;
            $this->xmlDocument .= $this->_visualElementLevel()."<title>".$this->channelTextInputTitle."</title>\n";
            $this->xmlDocument .= $this->_visualElementLevel()."<description>".$this->channelTextInputDescription."</description>\n";
            $this->xmlDocument .= $this->_visualElementLevel()."<name>".$this->channelTextInputName."</name>\n";
            $this->xmlDocument .= $this->_visualElementLevel()."<link>".$this->channelTextInputLink."</link>\n";

            $this->_xmlElementLevel--;
            $this->$this->xmlDocument .= $this->_visualElementLevel()."</textInput>\n";
        }
        if (!empty($this->channelSkipHours)) $this->xmlDocument .= $this->_visualElementLevel()."<skipHours>".$this->channelSkipHours."</skipHours>\n";
        if (!empty($this->channelSkipDays)) $this->xmlDocument .= $this->_visualElementLevel()."<skipDays>".$this->channelSkipDays."</skipDays>\n";



        return true;
    }

    /**
     * Add custom channel elements
     *
     * Adds all the custom channel elements to the feed.
     * @return boolean
     */
    function _addCustomChannelElements($array = array())
    {
        foreach ($array as $name => $value) {
            if(is_array($value)) {
                $element .= $this->_visualElementLevel()."<".$name.">\n";
                $this->_xmlElementLevel++;
                $element .= $this->_addCustomChannelElements($value);
                $this->_xmlElementLevel--;
                $element .= $this->_visualElementLevel()."</".$name.">\n";
            } else {
                $element .= $this->_visualElementLevel()."<".$name.">".$value."</".$name.">\n";
            }
        }

        return $element;
    }

    /**
     * Parse items
     *
     * Parse the items of the feed and adds them to it.
     */
    function _parseItems()
    {
        foreach ($this->_arrItems as $arrItem) {
            $this->xmlDocument .= $this->_visualElementLevel()."<item>\n";
                $this->_xmlElementLevel++;

                if (!empty($arrItem['title'])) $this->xmlDocument .= $this->_visualElementLevel()."<title>".$arrItem['title']."</title>\n";
                if (!empty($arrItem['link'])) $this->xmlDocument .= $this->_visualElementLevel()."<link>".$arrItem['link']."</link>\n";
                if (!empty($arrItem['description'])) $this->xmlDocument .= $this->_visualElementLevel()."<description>".$arrItem['description']."</description>\n";
                if (!empty($arrItem['author'])) $this->xmlDocument .= $this->_visualElementLevel()."<author>".$arrItem['author']."</author>\n";

                if (!empty($arrItem['arrCategory']['title'])) {
                    $this->xmlDocument .= $this->_visualElementLevel()."<category".(!empty($arrItem['arrCategory']['domain']) ? " domain=\"".$arrItem['arrCategory']['domain']."\"" : "").">".$arrItem['arrCategory']['title']."</category>\n";
                } elseif (is_array($arrItem['arrCategory'])) {
                    foreach ($arrItem['arrCategory'] as $arrCategory) {
                        if (!empty($arrCategory['title'])) {
                            $this->xmlDocument .= $this->_visualElementLevel()."<category".(!empty($arrCategory['domain']) ? " domain=\"".$arrCategory['domain']."\"" : "").">".$arrCategory['title']."</category>\n";
                        }
                    }
                }

                if (!empty($arrItem['comments'])) $this->xmlDocument .= $this->_visualElementLevel()."<comments>".$arrItem['comments']."</comments>\n";

                if (!empty($arrItem['arrEnclosure']['url']) && !empty($arrItem['arrEnclosure']['length']) && !empty($arrItem['arrEnclosure']['type'])) {
                    $this->xmlDocument .= $this->_visualElementLevel()."<enclosure url=\"".$arrItem['arrEnclosure']['url']."\" length=\"".$arrItem['arrEnclosure']['length']."\" type=\"".$arrItem['arrEnclosure']['type']."\" />\n";
                }

                if (!empty($arrItem['arrGuid']['guid'])) $this->xmlDocument .= $this->_visualElementLevel()."<guid".(!empty($arrItem['arrGuid']['isPermaLink']) ? " isPermaLink=\"".(bool)$arrItem['arrGuid']['isPermaLink']."\"" : "").">".$arrItem['arrGuid']['guid']."</guid>\n";

                if (!empty($arrItem['pubDate'])) $this->xmlDocument .= $this->_visualElementLevel()."<pubDate>".date('r', $arrItem['pubDate'])."</pubDate>\n";

                if (!empty($arrItem['source']['url']) && !empty($arrItem['source']['title'])) {
                    $this->xmlDocument .= $this->_visualElementLevel()."<source url=\"".$arrItem['source']['url']."\">".$arrItem['source']['title']."</source>\n";
                }

                if (!empty($arrItem['arrCustom'])) {
                    $this->xmlDocument .= $this->_addCustomChannelElements($arrItem['arrCustom']);
                }

                $this->_xmlElementLevel--;
                $this->xmlDocument .= $this->_visualElementLevel()."</item>\n";
        }
    }

    /**
     * Visual element level
     *
     * Return a number of tabs to visual the locial structure of the RSS feed.
     * @return string
     */
    function _visualElementLevel()
    {
        return sprintf("%'\t".$this->_xmlElementLevel."s", "");
    }

	/** returns the ord() of an UTF8 character.
	 * @param c - the character you want to have converted
	 * @param index (optional) index into the string
	 * @param bytes (optional, out) returns number of bytes of character.
	 * Copyright: "kerry at shetline dot com", copied from php.net/ord.
	 * Dave Vogt, 25.07.2008
	 */
	private function _ordUTF8($c, $index = 0, &$bytes = null) {
        $len   = strlen($c);
        $bytes = 0;

        if ($index >= $len) return false;

        $h = ord($c{$index});

        if ($h <= 0x7F) {
			$bytes = 1;
			return $h;
        }
        else if ($h < 0xC2) return false;
        else if ($h <= 0xDF && $index < $len - 1) {
			$bytes = 2;
			return ($h & 0x1F) <<    6 | (ord($c{$index + 1}) & 0x3F);
        }
        else if ($h <= 0xEF && $index < $len - 2) {
			$bytes = 3;
			return ($h & 0x0F) << 12 | (ord($c{$index + 1}) & 0x3F) << 6
									 | (ord($c{$index + 2}) & 0x3F);
        }                    
        else if ($h <= 0xF4 && $index < $len - 3) {
			$bytes = 4;
			return ($h & 0x0F) << 18 | (ord($c{$index + 1}) & 0x3F) << 12
									 | (ord($c{$index + 2}) & 0x3F) << 6
									 | (ord($c{$index + 3}) & 0x3F);
        }

        else return false;
	}

	/**
	 * Helper function for converting an special character into
	 * it's proper \uXXXX notation.
	 */
	private function _uni_escape($chr) {
		$chr = $chr[0];
		$bytecount = 1;
		$codepoint = $this->_ordUTF8($chr, 0, $bytecount);

		// 1-byte UTF8 character means ASCII aequivalent. no need
		// to escape!
		if ($bytecount == 1) return $chr;

		$hex       = strtoupper(dechex($codepoint));

		$len = strlen($hex);
		// output needs to be zero-padded (four positions)
		$zeroes    = 4 - $len;
		for (; $zeroes > 0; $zeroes--) {
			$hex = "0$hex";
		}
		return "\\u$hex";
	}

	/**
	 * Takes a string and replaces all umlauts and special chars with
	 * their unicode escape sequence. This is needed so UTF8 Javascript
	 * news gets displayed correctly in Latin1 pages.
	 */
	private function _js_umlauts($str) {
		return preg_replace_callback('/(.)/u', array($this, '_uni_escape'), $str); 
	}
}

/*
    this was c&ped together from news/admin.class.php and news/lib/newsLib.class.php 
*/
class HackyFeedRepublisher {

    protected $arrSettings = array();

    public function runRepublishing() {
        initRepublishing();
    
        require_once 'Language.class.php';
        FWLanguage::init();

        $langIds = array_keys(FWLanguage::getLanguageArray());
        
        foreach($langIds as $id) {
            $this->createRSS($id);
        }
    }

    protected function initRepublishing()
    {
        global  $_ARRAYLANG, $objInit, $objTemplate, $_CONFIG;

        //getSettings
        global $objDatabase;
        $query = "SELECT name, value FROM ".DBPREFIX."module_news_settings";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $this->arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
            $objResult->MoveNext();
        }
    }

    protected function createRSS($langId){
        global $_CONFIG, $objDatabase; 
        $_FRONTEND_LANGID = $langId;

        require_once 'validator.inc.php';
        require_once 'RSSWriter.class.php';

        if (intval($this->arrSettings['news_feed_status']) == 1) {
            $arrNews = array();
            $objRSSWriter = new RSSWriter();

            $objRSSWriter->characterEncoding = CONTREXX_CHARSET;
            $objRSSWriter->channelTitle = $this->arrSettings['news_feed_title'];
            $objRSSWriter->channelLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news';
            $objRSSWriter->channelDescription = $this->arrSettings['news_feed_description'];
            $objRSSWriter->channelLanguage = FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang');
            $objRSSWriter->channelCopyright = 'Copyright '.date('Y').', http://'.$_CONFIG['domainUrl'];

            if (!empty($this->arrSettings['news_feed_image'])) {
                $objRSSWriter->channelImageUrl = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).$this->arrSettings['news_feed_image'];
                $objRSSWriter->channelImageTitle = $objRSSWriter->channelTitle;
                $objRSSWriter->channelImageLink = $objRSSWriter->channelLink;
            }
            $objRSSWriter->channelWebMaster = $_CONFIG['coreAdminEmail'];

            $itemLink = "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;cmd=details&amp;newsid=';

            $query = "
                SELECT      tblNews.id,
                            tblNews.date,
                            tblNews.title,
                            tblNews.text,
                            tblNews.redirect,
                            tblNews.source,
                            tblNews.catid AS categoryId,
                            tblNews.teaser_frames AS teaser_frames,
                            tblNews.teaser_text,
                            tblCategory.name AS category
                FROM        ".DBPREFIX."module_news AS tblNews
                INNER JOIN  ".DBPREFIX."module_news_categories AS tblCategory
                USING       (catid)
                WHERE       tblNews.status=1
                    AND     tblNews.lang = ".$_FRONTEND_LANGID."
                    AND     (tblNews.startdate <= CURDATE() OR tblNews.startdate = '0000-00-00 00:00:00')
                    AND     (tblNews.enddate >= CURDATE() OR tblNews.enddate = '0000-00-00 00:00:00')"
                    .($this->arrSettings['news_message_protection'] == '1' ? " AND tblNews.frontend_access_id=0 " : '')
                            ."ORDER BY tblNews.date DESC";

            if (($objResult = $objDatabase->SelectLimit($query, 20)) !== false && $objResult->RecordCount() > 0) {
                while (!$objResult->EOF) {
                    if (empty($objRSSWriter->channelLastBuildDate)) {
                        $objRSSWriter->channelLastBuildDate = date('r', $objResult->fields['date']);
                    }
                    $arrNews[$objResult->fields['id']] = array(
                        'date'          => $objResult->fields['date'],
                        'title'         => $objResult->fields['title'],
                        'text'          => empty($objResult->fields['redirect']) ? (!empty($objResult->fields['teaser_text']) ? nl2br($objResult->fields['teaser_text']).'<br /><br />' : '').$objResult->fields['text'] : (!empty($objResult->fields['teaser_text']) ? nl2br($objResult->fields['teaser_text']) : ''),
                        'redirect'      => $objResult->fields['redirect'],
                        'source'        => $objResult->fields['source'],
                        'category'      => $objResult->fields['category'],
                        'teaser_frames' => explode(';', $objResult->fields['teaser_frames']),
                        'categoryId'    => $objResult->fields['categoryId']
                    );
                    $objResult->MoveNext();
                }
            }

            // create rss feed
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml';
            foreach ($arrNews as $newsId => $arrNewsItem) {
                $objRSSWriter->addItem(
                    contrexx_raw2xml($arrNewsItem['title']),
                    (empty($arrNewsItem['redirect'])) ? ($itemLink.$newsId.(isset($arrNewsItem['teaser_frames'][0]) ? '&amp;teaserId='.$arrNewsItem['teaser_frames'][0] : '')) : htmlspecialchars($arrNewsItem['redirect'], ENT_QUOTES, CONTREXX_CHARSET),
                    contrexx_raw2xml($arrNewsItem['text']),
                    '',
                    array('domain' => "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;category='.$arrNewsItem['categoryId'], 'title' => $arrNewsItem['category']),
                    '',
                    '',
                    '',
                    $arrNewsItem['date'],
                    array('url' => htmlspecialchars($arrNewsItem['source'], ENT_QUOTES, CONTREXX_CHARSET), 'title' => contrexx_raw2xml($arrNewsItem['title']))
               );
            }
            $status = $objRSSWriter->write();

            // create headlines rss feed
            $objRSSWriter->removeItems();
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_headlines_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml';
            foreach ($arrNews as $newsId => $arrNewsItem) {
                $objRSSWriter->addItem(
                    contrexx_raw2xml($arrNewsItem['title']),
                    $itemLink.$newsId.(isset($arrNewsItem['teaser_frames'][0]) ? "&amp;teaserId=".$arrNewsItem['teaser_frames'][0] : ""),
                    '',
                    '',
                    array('domain' => 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;category='.$arrNewsItem['categoryId'], 'title' => $arrNewsItem['category']),
                    '',
                    '',
                    '',
                    $arrNewsItem['date']
                );
            }
            $statusHeadlines = $objRSSWriter->write();

            $objRSSWriter->feedType = 'js';
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.js';
            $objRSSWriter->write();

            /*
            if (count($objRSSWriter->arrErrorMsg) > 0) {
                $this->strErrMessage .= implode('<br />', $objRSSWriter->arrErrorMsg);
            }
            if (count($objRSSWriter->arrWarningMsg) > 0) {
                $this->strErrMessage .= implode('<br />', $objRSSWriter->arrWarningMsg);
            }
            */
        } else {
            @unlink(ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml');
            @unlink(ASCMS_FEED_PATH.'/news_headlines_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml');
            @unlink(ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.js');
        }
    }
}
//END OF NEWS CONVERTING STUFF


function _newsUpdate() {
	global $objDatabase, $_CONFIG, $objUpdate, $_ARRAYLANG;


	/************************************************
	* EXTENSION:	Placeholder NEWS_LINK replaced	*
	*				by NEWS_LINK_TITLE				*
	* ADDED:		Contrexx v2.1.0					*
	************************************************/
	if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.1.0')) {
		$query = "
    		SELECT
	    		c.`id`,
	    		c.`content`,
	    		c.`title`,
	    		c.`metatitle`,
	    		c.`metadesc`,
	    		c.`metakeys`,
	    		c.`metarobots`,
	    		c.`css_name`,
	    		c.`redirect`,
	    		c.`expertmode`,
	    		n.`catid`,
                n.`is_validated`,
	    		n.`parcat`,
	    		n.`catname`,
	    		n.`target`,
	    		n.`displayorder`,
	    		n.`displaystatus`,
                n.`activestatus`,
	    		n.`cachingstatus`,
                n.`username`,
	    		n.`cmd`,
	    		n.`lang`,
	    		n.`startdate`,
	    		n.`enddate`,
	    		n.`protected`,
	    		n.`frontend_access_id`,
	    		n.`backend_access_id`,
	    		n.`themes_id`,
                n.`css_name`
    		FROM `".DBPREFIX."content` AS c
    		INNER JOIN `".DBPREFIX."content_navigation` AS n ON n.`catid` = c.`id`
    		WHERE n.`module` = 8 AND c.`content` LIKE '%\{NEWS_LINK\}%' AND n.`username` != 'contrexx_update_2_1_0'";
    	$objContent = $objDatabase->Execute($query);
    	if ($objContent !== false) {
    		$arrFailedPages = array();
    		while (!$objContent->EOF) {
    			$newContent = str_replace(
    				'{NEWS_LINK}',
    				'{NEWS_LINK_TITLE}',
    				$objContent->fields['content']
    			);
    			$query = "UPDATE `".DBPREFIX."content` AS c INNER JOIN `".DBPREFIX."content_navigation` AS n on n.`catid` = c.`id` SET `content` = '".addslashes($newContent)."', `username` = 'contrexx_update_2_1_0' WHERE c.`id` = ".$objContent->fields['id'];
    			if ($objDatabase->Execute($query) === false) {
					$link = CONTREXX_SCRIPT_PATH."?section=news".(empty($objContent->fields['cmd']) ? '' : "&amp;cmd=".$objContent->fields['cmd'])."&amp;langId=".$objContent->fields['lang'];
    				$arrFailedPages[$objContent->fields['id']] = array('title' => $objContent->fields['catname'], 'link' => $link);
    			} else {
	    			$objDatabase->Execute("UPDATE `".DBPREFIX."content_navigation_history` SET `is_active` = '0' WHERE `catid` = ".$objContent->fields['id']);
	    			$objDatabase->Execute("
	    				INSERT INTO `".DBPREFIX."content_navigation_history`
						SET
							`is_active` = '1',
							`catid` = ".$objContent->fields['id'].",
							`parcat` = ".$objContent->fields['parcat'].",
							`catname` = '".addslashes($objContent->fields['catname'])."',
							`target` = '".$objContent->fields['target']."',
							`displayorder` = ".$objContent->fields['displayorder'].",
							`displaystatus` = '".$objContent->fields['displaystatus']."',
							`activestatus` = '".$objContent->fields['activestatus']."',
							`cachingstatus` = '".$objContent->fields['cachingstatus']."',
							`username` = 'contrexx_update_2_1_0',
							`changelog` = ".time().",
							`cmd` = '".$objContent->fields['cmd']."',
							`lang` = ".$objContent->fields['lang'].",
							`module` = 8,
							`startdate` = '".$objContent->fields['startdate']."',
							`enddate` = '".$objContent->fields['enddate']."',
							`protected` = ".$objContent->fields['protected'].",
							`frontend_access_id` = ".$objContent->fields['frontend_access_id'].",
							`backend_access_id` = ".$objContent->fields['backend_access_id'].",
							`themes_id` = ".$objContent->fields['themes_id'].",
                            `css_name` = '".$objContent->fields['css_name']."'"
					);

					$historyId = $objDatabase->Insert_ID();

					$objDatabase->Execute("
						INSERT INTO `".DBPREFIX."content_history`
						SET
							`id` = ".$historyId.",
							`page_id` = ".$objContent->fields['id'].",
							`content` = '".addslashes($newContent)."',
							`title` = '".addslashes($objContent->fields['title'])."',
							`metatitle` = '".addslashes($objContent->fields['metatitle'])."',
							`metadesc` = '".addslashes($objContent->fields['metadesc'])."',
							`metakeys` = '".addslashes($objContent->fields['metakeys'])."',
							`metarobots` = '".addslashes($objContent->fields['metarobots'])."',
							`css_name` = '".addslashes($objContent->fields['css_name'])."',
							`redirect` = '".addslashes($objContent->fields['redirect'])."',
							`expertmode` = '".$objContent->fields['expertmode']."'
					");

					$objDatabase->Execute("
						INSERT INTO	`".DBPREFIX."content_logfile`
						SET
							`action` = 'update',
							`history_id` = ".$historyId.",
							`is_validated` = '1'
					");
    			}

    			$objContent->MoveNext();
    		}

    		if (count($arrFailedPages)) {
    			setUpdateMsg($_ARRAYLANG['TXT_UNABLE_APPLY_NEW_NEWS_LAYOUT'], 'msg');

                $pages = '<ul>';
                foreach ($arrFailedPages as $arrPage) {
                    $pages .= "<li><a href='".$arrPage['link']."' target='_blank'>".htmlentities($arrPage['title'], ENT_QUOTES, CONTREXX_CHARSET)." (".$arrPage['link'].")</a></li>";
                }
                $pages .= '</ul>';
                setUpdateMsg($pages, 'msg');
    		}
    	} else {
    		return _databaseError($query, $objDatabase->ErrorMsg());
    	}
	}



	/************************************************
	* EXTENSION:	Front- and backend permissions  *
	* ADDED:		Contrexx v2.1.0					*
	************************************************/
	$query = "SELECT 1 FROM `".DBPREFIX."module_news_settings` WHERE `name` = 'news_message_protection'";
    $objResult = $objDatabase->SelectLimit($query, 1);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "INSERT INTO `".DBPREFIX."module_news_settings` (`name`, `value`) VALUES ('news_message_protection', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "SELECT 1 FROM `".DBPREFIX."module_news_settings` WHERE `name` = 'news_message_protection_restricted'";
    $objResult = $objDatabase->SelectLimit($query, 1);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "INSERT INTO `".DBPREFIX."module_news_settings` (`name`, `value`) VALUES ('news_message_protection_restricted', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'module_news');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_news'));
        return false;
    }

    if (!in_array('frontend_access_id', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_news` ADD `frontend_access_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `validated`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    if (!in_array('backend_access_id', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_news` ADD `backend_access_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `frontend_access_id`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



	/************************************************
	* EXTENSION:	Thunbmail Image                 *
	* ADDED:		Contrexx v2.1.0					*
	************************************************/
    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'module_news');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_news'));
        return false;
    }

    if (!in_array('teaser_image_thumbnail_path', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_news` ADD `teaser_image_thumbnail_path` TEXT NOT NULL AFTER `teaser_image_path`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



    try{
        // delete obsolete table  contrexx_module_news_access
        UpdateUtil::drop_table(DBPREFIX.'module_news_access');
        # fix some ugly NOT NULL without defaults
        UpdateUtil::table(
            DBPREFIX . 'module_news',
            array(
                'id'                         => array('type'=>'INT(6) UNSIGNED','notnull'=>true,  'primary'     =>true,   'auto_increment' => true),
                'date'                       => array('type'=>'INT(14)',            'notnull'=>false, 'default_expr'=>'NULL'),
                'title'                      => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'text'                       => array('type'=>'MEDIUMTEXT',         'notnull'=>true),
                'redirect'                   => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'source'                     => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'url1'                       => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'url2'                       => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'catid'                      => array('type'=>'INT(2) UNSIGNED',    'notnull'=>true,  'default'     =>0),
                'lang'                       => array('type'=>'INT(2) UNSIGNED',    'notnull'=>true,  'default'     =>0),
                'userid'                     => array('type'=>'INT(6) UNSIGNED',    'notnull'=>true,  'default'     =>0),
                'startdate'                  => array('type'=>'DATETIME',           'notnull'=>true,  'default'     =>'0000-00-00 00:00:00'),
                'enddate'                    => array('type'=>'DATETIME',           'notnull'=>true,  'default'     =>'0000-00-00 00:00:00'),
                'status'                     => array('type'=>'TINYINT(4)',         'notnull'=>true,  'default'     =>1),
                'validated'                  => array('type'=>"ENUM('0','1')",      'notnull'=>true,  'default'     =>0),
                'frontend_access_id'         => array('type'=>'INT(10) UNSIGNED',   'notnull'=>true,  'default'     =>0),
                'backend_access_id'          => array('type'=>'INT(10) UNSIGNED',   'notnull'=>true,  'default'     =>0),
                'teaser_only'                => array('type'=>"ENUM('0','1')",      'notnull'=>true,  'default'     =>0),
                'teaser_frames'              => array('type'=>'TEXT',               'notnull'=>true),
                'teaser_text'                => array('type'=>'TEXT',               'notnull'=>true),
                'teaser_show_link'           => array('type'=>'TINYINT(1) UNSIGNED','notnull'=>true,  'default'     =>1),
                'teaser_image_path'          => array('type'=>'TEXT',               'notnull'=>true),
                'teaser_image_thumbnail_path'=> array('type'=>'TEXT',               'notnull'=>true),
                'changelog'                  => array('type'=>'INT(14)',            'notnull'=>true,  'default'     =>0),
            ),
            array(#indexes
                'newsindex' =>array ('type' => 'FULLTEXT', 'fields' => array('text','title','teaser_text'))
            )
        );

    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

    //encoding was a little messy in 2.1.4. convert titles and teasers to their raw representation
    if($_CONFIG['coreCmsVersion'] == "2.1.4") {
        try{
            $res = UpdateUtil::sql('SELECT `id`, `title`, `teaser_text` FROM `'.DBPREFIX.'module_news` WHERE `changelog` > '.mktime(0,0,0,12,15,2010));
            while($res->MoveNext()) {
                $title = $res->fields['title'];
                $teaserText = $res->fields['teaser_text'];
                $id = $res->fields['id'];

                //title is html entity style
                $title = html_entity_decode($title, ENT_QUOTES, CONTREXX_CHARSET);
                //teaserText is html entity style, but no contrexx was specified on encoding
                $teaserText = html_entity_decode($teaserText);

                UpdateUtil::sql('UPDATE `'.DBPREFIX.'module_news` SET `title`="'.addslashes($title).'", `teaser_text`="'.addslashes($teaserText).'" where `id`='.$id);
            }

            require_once('news_dependencies/hackyFeedRepublisher.class.php');
            $hfr = new HackyFeedRepublisher();
            $hfr->runRepublishing();
        }
        catch (UpdateException $e) {
            DBG::trace();
            return UpdateUtil::DefaultActionHandler($e);
        }
    }

	return true;
}
?>
