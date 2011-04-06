<?php

/**
 * Javascript
 * @author      Stefan Heinemann <sh@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  framework
 * @todo        Edit PHP DocBlocks!
 */

// TODO: This is awkward here!  Find a way to include it just when it's needed.
// Required by ContrexxJS (activated with 'cx')
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/ContrexxJavascript.php';

/**
 * Javascript
 * @author      Stefan Heinemann <sh@comvation.com>
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  framework
 * @todo        Edit PHP DocBlocks!
 */
class JS
{
    /**
     * An offset that shall be used before all paths
     *
     * When the JS files are used e.g. in the cadmin
     * section, all paths need a '../' before the path.
     * This variable holds that offset.
     * @see setOffset($offset)
     * @access private
     * @static
     * @var string
     */
    private static $offset = "";

    /**
     * The array containing all the registered stuff
     *
     * @access private
     * @static
     * @var array
     */
    private static $active = array();

    /**
     * Holding the last error
     * @access private
     * @static
     * @var string
     */
    private static $error;

    /**
     * Available JS libs
     * These JS files are per default available
     * in every Contrexx CMS.
     * The format is the following:
     * array(
     *      scriptname : array (
     *          jsfiles :   array of strings containing
     *                      all needed javascript files
     *          cssfiles :  array of strings containing
     *                      all needed css files
     *          dependencies :  array of strings containing
     *                          all dependencies in the right
     *                          order
     *          specialcode :   special js code to be executed
     *          loadcallback:   function that will be executed with
     *                          the options as parameter when chosen
     *                          to activate that JS library, so the
     *                          options can be parsed
     *          makecallback:   function that will be executed when
     *                          the code is generated
     *      )
     * )
     * @access private
     * @static
     * @var array
     */
    private static $available = array(
        'prototype'     => array(
            'jsfiles'       => array(
                'lib/javascript/prototype.js'
            ),
        ),
        'scriptaculous' => array(
            'jsfiles'       => array(
                'lib/javascript/scriptaculous/scriptaculous.js'
            ),
            'dependencies'  => array(
                'prototype'
            ),
        ),
        'datepicker'    => array(
            'jsfiles'       => array(
                'lib/javascript/datepickercontrol/datepickercontrol.js'
            ),
            'cssfiles'      => array(
                'lib/javascript/datepickercontrol/datepickercontrol.css'
            )
        ),
        'shadowbox'     => array(
            'jsfiles'       => array(
                'lib/javascript/shadowbox/shadowbox-prototype.js',
                'lib/javascript/shadowbox/shadowbox.js'
            ),
            'dependencies'  => array(
                'prototype'
            ),
            'specialcode'  => 'var tmpOnLoad = window.onload; window.onload = function() { if(tmpOnLoad){tmpOnLoad();} Shadowbox.init(); }',
            'loadcallback' => 'parseShadowBoxOptions',
            'makecallback' => 'makeShadowBoxOptions'
        ),
        'jquery'     => array(
            'jsfiles'       => array(
                'lib/javascript/jquery/jquery-1.4.4.min.js',
            ),
            'specialcode'  => 'var $J = jQuery.noConflict();',
        ),
        'jquery-tools' => array(
            'jsfiles' => array(
                'lib/javascript/jquery/tools/jquery.tools.min.js',
            ),
            'dependencies' => array('jquery')
        ),
/*
Coming soon
        'jqueryui'     => array(
            'jsfiles'       => array(
                'lib/javascript/jqueryUI/js/jquery-ui-1.8.2.custom.min.js'
            ),
            'cssfiles'      => array(
                'lib/javascript/jqueryUI/css/blitzer/jquery-ui-1.8.2.custom.css',
            ),
            'dependencies'  => array(
                'jquery',
            ),
        ),
*/
/*
Coming soon
        'jcrop' => array(
            'jsfiles'       => array(
                'lib/javascript/jcrop/js/jquery.Jcrop.min.js'
            ),
            'cssfiles'      => array(
                'lib/javascript/jcrop/css/jquery.Jcrop.css',
            ),
            'dependencies'  => array(
                'jquery',
            ),
            // When invoking jcrop, add code like this to create the widget:
            // jQuery(window).load(function(){
            //   jQuery("#my_image").Jcrop({ [option: value, ...] });
            // });
            // where option may be any of
            // aspectRatio   decimal
            //    Aspect ratio of w/h (e.g. 1 for square)
            // minSize       array [ w, h ]
            //    Minimum width/height, use 0 for unbounded dimension
            // maxSize       array [ w, h ]
            //    Maximum width/height, use 0 for unbounded dimension
            // setSelect     array [ x, y, x2, y2 ]
            //    Set an initial selection area
            // bgColor       color value
            //    Set color of background container
            // bgOpacity     decimal 0 - 1
            //    Opacity of outer image when cropping
        ),
*/
        'cx' => array(
            'jsfiles' => array(
                'lib/javascript/cx/contrexxJs.js',
                'lib/javascript/cx/contrexxJs-tools.js',
                'lib/javascript/jquery/jquery.includeMany-1.2.2.min.js' //to dynamically include javascript files
            ),
            'dependencies' => array('jquery')
            //we insert the specialCode for the Contrexx-API later in getCode()
        ),
    );

    /**
     * Holds the custom JS files
     * @static
     * @access private
     * @var array
     */
    private static $customJS = array();

    /**
     * The custom CSS files
     * @static
     * @access private
     * @var array
     */
    private static $customCSS = array();

    /**
     * The custom Code
     * @static
     * @access private
     * @var array
     */
    private static $customCode = array();

    /**
     * The players of the shadowbox
     * @access private
     * @static
     * @var array
     */
    private static $shadowBoxPlayers = array('img', 'swf', 'flv', 'qt', 'wmp', 'iframe','html');

    /**
     * The language of the shadowbox to be used
     * @access private
     * @static
     * @var string
     */
    private static $shadowBoxLanguage = "en";

    /**
     * Remembers all js files already added in some way.
     *
     * @access private
     * @static
     * @var array
     */
    private static $registeredJsFiles = array();

    private static $re_name_postfix = 1;
    private static $comment_dict = array();


    /**
     * Set the offset parameter
     * @param string
     * @static
     * @access public
     */
    public static function setOffset($offset)
    {
        if (!preg_match('/\/$/', $offset)) {
            $offset .= '/';
        }
        self::$offset = $offset;
    }


    /**
     * Activate an available js file
     *
     * The options parameter is specific for the chosen
     * library. The library must define callback methods for
     * the options to be used.
     * @access public
     * @static
     * @param  string  $name
     * @param  array   $options
     * @param  bool    $dependencies
     * @return bool
     */
    public static function activate($name, $options = null, $dependencies = true)
    {
        $name = strtolower($name);
        if (array_key_exists($name, self::$available) === false) {
            self::$error = $name.' is not a valid name for
                an available javascript type';
            return false;
        }
        $data = self::$available[$name];
        if (!empty($data['ref'])) {
            $name = $data['ref'];
            if (array_key_exists($name, self::$available)) {
                $data = self::$available[$name];
            } else {
                self::$error = $name.' unknown reference';
                return false;
            }
        }
        if (!empty($data['dependencies']) && $dependencies) {
            foreach ($data['dependencies'] as $dep) {
                self::activate($dep);
            }
        }
        if (isset($data['loadcallback']) && isset($options)) {
            self::$data['loadcallback']($options);
        }
        if (array_search($name, self::$active) === false) {
            self::$active[] = $name;
        }
        return true;
    }


    /**
     * Deactivate a previously activated js file
     * @param string $name
     * @access public
     * @static
     * @return bool
     */
    public static function deactivate($name)
    {
        $name = strtolower($name);
        $searchResult = array_search($name, self::$active);
        if ($searchResult === false)
        {
            self::$error = $name.' is not a valid name for
                an available javascript type';
            return false;
        }
        unset(self::$active[$searchResult]);
        return true;
    }


    /**
     * Register a custom js file
     *
     * Adds a new, individual JS file to the list.
     * The filename has to be relative to the document root.
     * If a file is registered that already exists as a available
     * JS lib, then this one will be activated instead of
     * added.
     * @param mixed $file
     * @access public
     * @return bool Return true if successful
     * @static
     */
    public static function registerJS($file)
    {
        // $basename = strtolower(preg_replace("/\.[^\.]+$/", "", basename($file)));
        // we assume, every javascript files ends with .js
        $basename = strtolower(str_replace(".js", "", basename($file)));
        if (array_search($basename, array_keys(self::$available)) !== false) {
            self::activate($basename);
            return true;
        }
        if (!preg_match('#^https?://#', $file)) {
            if (!file_exists(($file[0] == '/' ? ASCMS_PATH : ASCMS_DOCUMENT_ROOT.'/').$file)) {
                self::$error .= "The file ".$file." doesn't exist\n";
                return false;
            }
        }
        if (array_search($file, self::$customJS) === false) {
            self::$customJS[] = $file;
        }
        return true;
    }

    /**
     * Register a custom css file
     *
     * Add a new, individual CSS file to the list.
     * The filename has to be relative to the document root.
     * @static
     * @access public
     * @return bool
     */
    public static function registerCSS($file)
    {
        if (!file_exists(ASCMS_DOCUMENT_ROOT.'/'.$file)) {
            self::$error = "The file ".$file." doesn't exist\n";
            return false;
        }

        if (array_search($file, self::$customCSS) === false) {
            self::$customCSS[] = $file;
        }
        return true;
    }


    /**
     * Register special code
     * Add special code to the List
     * @static
     * @access public
     * @return bool
     */
    public static function registerCode($code)
    {
        // try to see if this code already exists
        $code = trim($code);
        if (array_search($code, self::$customCode) === false) {
            self::$customCode[] = $code;
        }
        return true;
    }


    /**
     * Return the code for the placeholder
     * @access public
     * @static
     * @return string
     */
    public static function getCode()
    {
        $jsfiles = array();
        $cssfiles = array();
        $specialcode = array();
        $retstring  = '';
        if (count(self::$active) > 0) {
            foreach (self::$active as $name) {
                $data = self::$available[$name];
                if (!isset($data['jsfiles'])) {
                    self::$error = "A JS entry should at least contain one js file...";
                    return false;
                }
                $retstring .= self::makeJSFiles($data['jsfiles']);
                if (!empty($data['cssfiles'])) {
                    $cssfiles = array_merge($cssfiles, $data['cssfiles']);
                }
                if (isset($data['specialcode']) && strlen($data['specialcode']) > 0) {
                    $retstring .= self::makeSpecialCode(array($data['specialcode']));
                }
                if (isset($data['makecallback'])) {
                    self::$data['makecallback']();
                }
                // Special case contrexx-API: fetch specialcode if activated
                if ($name == 'cx') {
                    $retstring .= self::makeSpecialCode(
                        array(ContrexxJavascript::getInstance()->initJs()));
                }                  
            }
        }
        $retstring .= self::makeJSFiles(self::$customJS);
        $retstring .= self::makeCSSFiles($cssfiles);
        $retstring .= self::makeCSSFiles(self::$customCSS);
        $retstring .= self::makeSpecialCode(self::$customCode);
        return $retstring;
    }


    /**
     * Return the last error
     * @return string
     * @static
     * @access public
     */
    public static function getLastError()
    {
        return self::$error;
    }


    /**
     * Return the available libs
     * @access public
     * @static
     * @return array
     */
    public static function getAvailableLibs()
    {
        return self::$available;
    }


    /**
     * Make the code for the Javascript files
     * @param array $files
     * @return string
     * @static
     * @access private
     */
    private static function makeJSFiles($files)
    {
        $code = "";

        foreach ($files as $file) {
            // The file has already been added to the js list
            if (array_search($file, self::$registeredJsFiles) !== false)
                continue;
            self::$registeredJsFiles[] = $file;
            $code .= "<script type=\"text/javascript\" src=\"".self::$offset.$file."\"></script>\n\t";
        }
        return $code;
    }


    /**
     * Make the code for the CSS files
     * @param array $files
     * @return string
     * @static
     * @access private
     */
    private static function makeCSSFiles($files)
    {
        $code = "";
        foreach ($files as $file) {
            $code .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".self::$offset.$file."\" />\n\t";
        }
        return $code;
    }


    /**
     * Make the code section for
     * @access private
     * @param array $code
     * @return string
     * @static
     */
    private static function makeSpecialCode($code)
    {
        $retcode = "";
        if (!empty($code)) {
            $retcode .= "<script type=\"text/javascript\">\n/* <![CDATA[ */\n";
            foreach ($code as $segment) {
                $retcode .= $segment."\n";
            }
            $retcode .= "\n/* ]]> */\n</script>\n";
        }
        return $retcode;
    }


    /**
     * Callback function for the shadowbox library
     *
     * Called when the shadowbox is loaded and when parameters are given.
     * Add the the players to a list. Set the language.
     * Format of the options passed through JS::activate
     * (everything is optional):
     * array(
     *      players => array(img, swf, flv, qt, wmp, iframe, html),
     *      language => [ar, ca, cs, de-CH, de-DE, en, es
     *                          et, fi, fr, gl, he, id, is, it,
     *                          ko, my, nl, no, pl, pt-BR, pt-PT,
     *                          ro, ru, sk, svn, tr, zh-CN, zh-TW])
     * )
     * @static
     * @access private
     * @param array $options
     */
    private static function parseShadowBoxOptions($options = null)
    {
        $available_players = array('img', 'swf', 'flv', 'qt', 'wmp', 'iframe','html');
        $available_langs = array('ar', 'ca', 'cs', 'de-CH', 'de-DE', 'en',
            'es', 'et', 'fi', 'fr', 'gl', 'he', 'id', 'is', 'it', 'ko',
            'my', 'nl', 'no', 'pl', 'pt-BR', 'pt-PT', 'ro', 'ru', 'sk', 'sv',
            'tr', 'zh-CN', 'zh-TW');
        $options = (isset($options)) ? $options : array();
        if (!empty($options['players'])) {
            $renewed = false;
            foreach ($options['players']  as $player) {
                if (!$renewed) {
                    self::$shadowBoxPlayers = array();
                    $renewed = true;
                }
                if (array_search($player, $available_players) !== false) {
                    // valid player
                    if (array_search($player, self::$shadowBoxPlayers) === false) {
                        self::$shadowBoxPlayers[] = $player;
                    }
                }
            }
        } else {
            // set all players
            self::$shadowBoxPlayers = $available_players;
        }

        if (!empty($options['language'])) {
            if (array_search($options['language'], $available_langs) !== false) {
                self::$shadowBoxLanguage = $options['language'];
            }
        }
    }


    /**
     * Callback function for the shadowbox library
     *
     * Called when the shadowbox was loaded and the code is
     * generated. Makes the initial-lines to provide the chosen
     * players and to load the skin. If there is a directory
     * called 'shadowbox' in the current theme directory, this one
     * will be taken, otherwise the default skin under lib/javascript/shadowbox.
     * @static
     * @access private
     * @global object $objInit
     */
    private static function makeShadowBoxOptions()
    {
        global $objInit;

        // make the code for loading the players
        if (!empty(self::$shadowBoxPlayers)) {
            $players = "";
            foreach (self::$shadowBoxPlayers as $player) {
                $players .= " '".$player."',";
            }
            $players = substr($players, 1, -1);
            self::$customCode[] = "Shadowbox.loadPlayer([".$players."],"
                ."'".self::$offset."lib/javascript/shadowbox/player/');";
        }

        // make the code for loading the skins
        $skindir = self::$offset."lib/javascript/shadowbox/skin";
        $skin = 'standard';
        if ($objInit->mode == "frontend") {
            $themePath = $objInit->getCurrentThemesPath();
            if (file_exists(ASCMS_THEMES_PATH.'/'.$themePath.'/shadowbox/')) {
                $skindir = $themePath.'/shadowbox';
            }
        }
        self::$customCode[] = "Shadowbox.loadSkin('".$skin."', '".self::$offset.$skindir."');";
        // Make the code for loading the language
        self::$customCode[] = "Shadowbox.loadLanguage('".self::$shadowBoxLanguage."', '".self::$offset."lib/javascript/shadowbox/lang')";
    }


    public static function registerFromRegex($matchinfo)
    {
        $script = $matchinfo[1];
        self::registerJS($script);
    }


    /**
     * Finds all <script>-Tags in the passed HTML content, strips them out
     * and puts them in the internal JAVASCRIPT placeholder store.
     * You can then retreive them all-in-one with JS::getCode().
     * @param string $content - Reference to the HTML content. Note that it
     *                          WILL be modified in-place.
     */
    public function findJavascripts(&$content)
    {
        JS::grabComments($content);
        $content = preg_replace_callback('/<script .*?src=(?:"|\')([^"\']*)(?:"|\').*?\/?>(?:<\/script>)?/i', array('JS', 'registerFromRegex'), $content);
        JS::restoreComments($content);
    }


    /**
     * Grabs all comments in the given HTML and replaces them with a
     * temporary string. Modifies the given HTML in-place.
     * @param string $content
     */
    private static function grabComments(&$content)
    {
        $content = preg_replace_callback('#<!--.*?-->#ms', array('JS', '_storeComment'), $content);
    }


    /**
     * Restores all grabbed comments (@see JS::grabComments()) and
     * puts them back in the given content. Modifies the given HTML in-place.
     * @param string $content
     */
    private static function restoreComments(&$content)
    {
        krsort(self::$comment_dict);
        foreach (self::$comment_dict as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
    }


    /**
     * Internal helper for replacing comments. @see JS::grabComments()
     */
    private static function _storeComment($re)
    {
        $name = 'saved_comment_'.self::$re_name_postfix;
        self::$comment_dict[$name] = $re[0];
        self::$re_name_postfix++;
        return $name;
    }

}

?>
