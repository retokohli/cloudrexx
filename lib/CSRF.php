<?php

/**
 * Protect against CSRF attacks
 * @version     2.1.3
 * @since       2.1.3
 * @package     contrexx
 * @subpackage  lib
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      David Vogt <david.vogt@comvation.com>
 */

/**
 * This class provides protection against CSRF attacks.
 *
 * call CSRF::add_code() if the page contains vulnerable
 * links and forms, and use CSRF::check_code() to kill the
 * request if there's an invalid code.
 *
 * This class expects that the session has been set up
 * correctly and can be used through $_SESSION.
 * @version     2.1.3
 * @since       2.1.3
 * @package     contrexx
 * @subpackage  lib
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      David Vogt <david.vogt@comvation.com>
 */
class CSRF {

    /**
     * This variable defines how many times a given code
     * is accepted as valid. We need this in case the user
     * opens a new tab in the admin panel.
     *
     * A high value increases usability, a low value
     * increases security. Tough call!
     */
    static $validity_count = 15;

    /**
     * This number defines how much any known code's validity
     * count is reduced at every check, even if another code
     * was given by the form/link. This way, we can expire
     * codes that are not in use anymore, and so keep the
     * session smaller. With a value of 0.5 and a validity_count
     * of 5, this means that after 10 requests, an unused
     * key will be invalid.
     */
    static $unused_decrease = 0.5;

    /**
     * This number defines how much to decrease a code's
     * validity each time it's checked. Example: if
     * validity_count is 5 and active_decrease is 1,
     * a code is valid four times, meaning a user can
     * open four tabs from the same page before the
     * request is denied.
     */
    static $active_decrease = 1;

    private static $already_added_code = false;
    private static $already_checked    = false;

    private static $sesskey = '__csrf_data__';
    private static $formkey = 'csrf';

    private static $current_code = NULL;

    private static $frontend_mode = false;


    private static function __get_code()
    {
        if (!empty(self::$current_code)) {
            return self::$current_code;
        }
        self::$current_code = base64_encode(rand(1000000000000, 9999999999999));
        self::$current_code = preg_replace('#[\'"=%]#', '_', self::$current_code);
        self::__setkey(self::$current_code, self::$validity_count);
        return self::$current_code;
    }


    /**
     * An utility function to patch URLs specifically in
     * redirect (and possibly other) headers. Expects a
     * string in the form "header-name: ...." and returns
     * it, modified to contain the CSRF protection parameter.
     *
     * Example: __enhance_header('Location: index.php')
     * --> "Location: index.php?__csrf__=xxxxx"
     */
    private static function __enhance_header($header)
    {
        if (!self::__is_logged_in()) return $header;
        if (self::$frontend_mode) return $header;
        $result = array();
        if (!preg_match('#^(\w+):\s*(.*)$#i', $header, $result)) {
            // don't know what to do with it
            return $header;
        }
        $hdr = $result[1];
        $url = self::enhanceURI($result[2]);
        return "$hdr: $url";
    }


    /**
     * Acts as a replacement for header() calls that handle URLs.
     * Only use it for headers in the form "Foo: an_url", for
     * instance "Location: index.php?foo=bar".
     */
    public static function header($header, $replace = true, $httpResponseCode = null)
    {
        header(self::__enhance_header($header), $replace, $httpResponseCode);
    }


    /**
     * Adds the CSRF protection code to the URI specified by $uri.
     *
     * Note: This adds a simple ampersand (&), not the HTML entity &amp;.
     * Thus, it is only suitable for modifying header() parameters and
     * URIs within javascript.  For URIs to be embedded into HTML,
     * you *SHOULD* htmlentities() it first!
     */
    public static function enhanceURI($uri)
    {
        if (self::$frontend_mode) return $uri;

        $key = self::$formkey;
        $val = self::__get_code();
        $uri .= (strstr($uri, '?') ? '&' : '?')."$key=$val";
        return $uri;
    }


    /**
     * Call this to add a CSRF protection code to all the
     * forms and links on the generated page. Note that
     * you don't need to pass any content, and nothing is
     * returned - this function uses PHP to change it's
     * output so as to insert the data.
     *
     * Note: output_add_rewrite_var() used in here does a really bad job
     * on your URIs within the HTML.  It adds parameters without considering
     * whether it should use '&' or '&amp;'.  This results in invalid HTML!
     */
    public static function add_code()
    {
        if (!self::__is_logged_in()) return;
        if (self::$already_added_code) return;
        self::$already_added_code = true;
        $code = self::__get_code();
        output_add_rewrite_var(self::$formkey, $code);
    }


    /**
     * Adds a placeholder for the CSRF code to the given template.
     * This is so you can easily patch javascript code that handles
     * URLs, as this cannot be done by add_code().
     * @param $tpl Template object
     */
    public static function add_placeholder($tpl)
    {
        if (!self::__is_logged_in()) return true;
        if (!is_object($tpl)) {
            DBG::msg("self::add_placeholder(): fix this call, that ain't a template object! (Stack follows)");
            DBG::stack();
        }
        $code = self::__get_code();
        $tpl->setGlobalVariable(array(
            "CSRF_PARAM" => self::param(),
            "CSRF_KEY"   => "$code",
        ));
        return true;
    }


    /**
     * Returns the anti-CSRF code's form key.
     * You can build your own URLs together
     * with CSRF::code()
     */
    public static function key()
    {
        return self::$formkey;
    }


    /**
     * Returns the anti-CSRF code for the current
     * request. You can build your own URLs together
     * with CSRF::key()
     */
    public static function code()
    {
        return self::__get_code();
    }


    /**
     * Returns a key/value pair ready to use in an URL.
     */
    public static function param()
    {
        if (!self::__is_logged_in()) return '';
        return self::key().'='.self::code();
    }


    /**
     * Call this if you need to protect critical work.
     * This function will stop the request if it cannot
     * find a valid anti-CSRF code in the request.
     */
    public static function check_code()
    {
        if (!self::__is_logged_in()) return;
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && self::$frontend_mode) return;
        if (self::$already_checked) return;
        self::$already_checked = true;
        // do not check if it's an AJAX request.  They're secure
        // by definition and also, they're much more delicate in
        // what can be returned - and they usually exceed the
        // request amount limit pretty quickly (see active_decrease etc)
        if (self::__is_ajax()) {
            return;
        }

        $code = ($_SERVER['REQUEST_METHOD'] == 'GET')
            ? (isset($_GET [self::$formkey]) ? $_GET[self::$formkey] : '')
            : (isset($_POST[self::$formkey]) ? $_POST[self::$formkey] : '');

        self::__cleanup();
        if(! self::__getkey($code)) {
            self::__kill();
        } else {
            self::__reduce($code);
            if (self::__getkey($code) < 0) {
                self::__kill();
            }
        }
    }


    private static function __kill()
    {
        global $_CORELANG;

        $data = ($_SERVER['REQUEST_METHOD'] == 'GET'
            ? $_GET
            : $_POST
        );
        self::add_code();
       	$tpl = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        $tpl->setErrorHandling(PEAR_ERROR_DIE);
        $tpl->loadTemplateFile('csrfprotection.html');
        $form = '';
        foreach ($data as $key => $value) {
            if ($key == self::$formkey or $key == 'amp;'.self::$formkey) {
                continue;
            }
            $form .= self::parseRequestParametersForForm($key, $value);
        }

        $tpl->setGlobalVariable(array(
	        'TXT_CSRF_TITLE' => $_CORELANG['TXT_CSRF_TITLE'],
    	    'TXT_CSRF_DESCR' => $_CORELANG['TXT_CSRF_DESCR'],
        	'TXT_CSRF_ABORT' => $_CORELANG['TXT_CSRF_ABORT'],
            'TXT_CSRF_CONTINUE' => $_CORELANG['TXT_CSRF_CONTINUE'],
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'SAFE_CMD' => 'index.php?cmd='.$_GET['cmd'],
    	    'FORM_ELEMENTS' => $form,
            'IMAGES_PATH' => ASCMS_ADMIN_WEB_PATH.'/images/csrfprotection',
        ));
        $tpl->parse();

        
        die($tpl->get());
    }


    private static function parseRequestParametersForForm($key, $value, $arrSubKeys=array())
    {
        $elem = '';
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $elem .= self::parseRequestParametersForForm($key, $subValue, array_merge($arrSubKeys, array($subKey)));
            }
        } else {
            $elem = '<input type="hidden" name="_N_" value="_V_" />';
            $elem = str_replace('_N_', self::__formval($key.(!empty($arrSubKeys) ? '['.implode('][', $arrSubKeys).']' : '')),  $elem);
            $elem = str_replace('_V_', self::__formval($value), $elem);
        }
        return $elem;
    }


    private static function __formval($str)
    {
        return htmlspecialchars(contrexx_stripslashes($str), ENT_QUOTES, CONTREXX_CHARSET);
    }


    private static function __reduce($code)
    {
        foreach (array_keys($_SESSION[self::$sesskey]) as $key) {
            $_SESSION[self::$sesskey][$key] -=
                ($code == $key
                    ? self::$active_decrease : self::$unused_decrease);
        }
    }


    private static function __is_ajax()
    {
        return
            (   isset($_SERVER['HTTP_X_REQUESTED_WITH'])
             && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }


    private static function __cleanup()
    {
        foreach ($_SESSION[self::$sesskey] as $key => $count) {
            if ($count < 0) {
                unset($_SESSION[self::$sesskey][$key]);
            }
        }
    }


    private static function __getkey($key)
    {
        return !empty($_SESSION[self::$sesskey][$key]);
    }


    private static function __setkey($key, $value)
    {
        if (!isset($_SESSION[self::$sesskey])) {
            $_SESSION[self::$sesskey] = array();
        }
        $csrfdata                 = $_SESSION[self::$sesskey];
        $csrfdata[$key]           = $value;
        $_SESSION[self::$sesskey] = $csrfdata;
    }


    private static function __is_logged_in()
    {
        if (class_exists('FWUser')) {
            $objFWUser = FWUser::getFWUserObject();
            if ($objFWUser->objUser->login()) {
                return true;
            }
        }
        return false;
    }


    /**
     * Remove the CSRF protection parameter from the query string and referrer
     */
    public static function cleanRequestURI()
    {
// This will remove the parameter from the first position in the query string
// and leave an URI like "index.php&name=value", which is invalid
        //$csrfUrlModifierPattern = '#(?:\&(?:amp\;)?|\?)?'.self::$formkey.'\=[a-zA-Z0-9_]+#';
// Better cut the parameter plus trailing ampersand, if any.
        $csrfUrlModifierPattern = '/'.self::$formkey.'\=[a-zA-Z0-9_]+\&?/';
// This will leave the URI valid, even if it's the last parameter;
// a trailing question mark or ampersand does no harm.
        !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] = preg_replace($csrfUrlModifierPattern, '', $_SERVER['QUERY_STRING'])    : false;
        !empty($_SERVER['REQUEST_URI'])  ? $_SERVER['REQUEST_URI']  = preg_replace($csrfUrlModifierPattern, '', $_SERVER['REQUEST_URI'])     : false;
        !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] = preg_replace($csrfUrlModifierPattern, '', $_SERVER['HTTP_REFERER'])    : false;
        !empty($_SERVER['argv'])         ? $_SERVER['argv']         = preg_grep($csrfUrlModifierPattern, $_SERVER['argv'], PREG_GREP_INVERT) : false;
    }

    public static function setFrontendMode()
    {
        self::$frontend_mode = true;
        @ini_set('url_rewriter.tags', 'area=href,frame=src,iframe=src,input=src,form=,fieldset=');
    }
}

CSRF::cleanRequestURI();

?>
