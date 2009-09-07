<?PHP

/**
 * This class provides protection against CSRF attacks.
 *
 * call CSRF::add_code() if the page contains vulnerable
 * links and forms, and use CSRF::check_code() to kill the
 * request if there's an invalid code.
 *
 * This class expects that the session has been set up
 * correctly and can be used through $_SESSION.
 *
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
    static $validity_count = 4;

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
    private static $formkey = '__csrf__';

    private static $current_code = NULL;

    private static function __get_code() {
        if (!empty(CSRF::$current_code)) {
            return CSRF::$current_code;
        }
        CSRF::$current_code = base64_encode(rand(1000000000000,9999999999999));
        CSRF::$current_code = preg_replace('#[\'"=%]#', '_', CSRF::$current_code);
        CSRF::__setkey(CSRF::$current_code, CSRF::$validity_count);
        return CSRF::$current_code;
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
    private static function __enhance_header($header) {
        $result = array();
        if (!preg_match('#^(\w+):\s*(.*)$#i', $header, $result)) {
            # don't know what to do with it
            return $header;
        }
        $hdr = $result[1];
        $url = $result[2];
        $key = CSRF::$formkey;
        $val = CSRF::__get_code();
        if (strstr($url, '?')) {
            $url .= "&$key=$val";
        }
        else {
            $url .= "?$key=$val";
        }
        return "$hdr: $url";
    }

    /**
     * Acts as a replacement for header() calls that handle URLs.
     * Only use it for headers in the form "Foo: an_url", for
     * instance "Location: index.php?foo=bar".
     */
    public static function header($header) {
        header(CSRF::__enhance_header($header));
    }

    /**
	 * Adds the CSRF protection code to the URI specified by $uri.
     */
    public static function enhanceURI($uri)
    {
        $key = CSRF::$formkey;
        $val = CSRF::__get_code();
        if (strstr($uri, '?')) {
            $uri .= "&amp;$key=$val";
        }
        else {
            $uri .= "?$key=$val";
        }
        return $uri;
    }

    /**
     * Call this to add a CSRF protection code to all the 
     * forms and links on the generated page. Note that
     * you don't need to pass any content, and nothing is
     * returned - this function uses PHP to change it's
     * output so as to insert the data.
     */
	public static function add_code() {
        if (CSRF::$already_added_code) {
            return;
        }
        CSRF::$already_added_code = true;
        $code = CSRF::__get_code();
        output_add_rewrite_var(CSRF::$formkey, $code);
	}

    /**
     * Adds a placeholder for the CSRF code to the given template.
     * This is so you can easily patch javascript code that handles
     * URLs, as this cannot be done by add_code().
     *
     * @param $tpl Template object
     */
    public static function add_placeholder($tpl) {
        if (!is_object($tpl)) {
            DBG::msg("CSRF::add_placeholder(): fix this call, that ain't a template object! (Stack follows)");
            DBG::stack();
        }
        $code = CSRF::__get_code();
        $name = CSRF::$formkey;
        $tpl->setGlobalVariable(array(
            "CSRF_PARAM"    => "$name=$code",
            "CSRF_KEY"      => "$code"
        ));
        return true;
    }

    /**
     * Call this if you need to protect critical work. 
     * This function will stop the request if it cannot
     * find a valid anti-CSRF code in the request.
     */
    public static function check_code() {

        if (CSRF::$already_checked) {
            return;
        }
        CSRF::$already_checked = true;

        # do not check if it's an AJAX request.. they're secure
        # by definition and also, they're much more delicate in
        # what can be returned - and they usually exceed the
        # request amount limit pretty quickly (see active_decrease etc)
        if (CSRF::__is_ajax()) {
            return;
        }

        $code = ($_SERVER['REQUEST_METHOD'] == 'GET')
            ? $_GET [CSRF::$formkey]
            : $_POST[CSRF::$formkey]
        ;

        CSRF::__cleanup();

        if(! CSRF::__getkey($code)) {
            CSRF::__kill();
        }
        else {
            CSRF::__reduce($code);

            if (CSRF::__getkey($code) < 0) {
                CSRF::__kill();
            }
        }
    }

    private static function __kill() {
        global $_ARRAYLANG, $_CORELANG;
        
        $data = ($_SERVER['REQUEST_METHOD'] == 'GET')
            ? $_GET
            : $_POST
        ;
        CSRF::add_code();

        // TODO: make this a nice little template
        $html = '
            <html><head>
            <title>'.$_ARRAYLANG['TXT_CSRF_TITLE'].'</title>
            <style type="text/css">
                * {
                    font-family: Arial,Helvetica,sans-serif;
                }
                div#message {
                    margin-left: auto;
                    margin-right: auto;
                    width: 500px;
                    border: 1px solid red; 
                    padding: 5px;
                    background-color: #ffefef;
                    margin-top: 100px;
                }
            </style>
            </head>
            <body>
            <div id="message">
                <h2>'.$_ARRAYLANG['TXT_CSRF_TITLE'].'</h2>
                '.$_ARRAYLANG['TXT_CSRF_DESCR'].'

                <p/>
                <form method="'.$_SERVER['REQUEST_METHOD'].'">
                _____ELEMENTS___
                <input type="submit" value="'.$_ARRAYLANG['TXT_CSRF_BUTTON'].'" />
                </form>
            </div>
            </body>
            </html>
        ';
        $elem_template = '<input type="hidden" name="_N_" value="_V_" />';
        $form = '';
        foreach ($data as $key => $value) {
            if ($key == CSRF::$formkey) {
                continue;
            }
            $elem = $elem_template;
            $elem = str_replace('_N_', htmlspecialchars($key),  $elem);
            $elem = str_replace('_V_', htmlspecialchars($value),$elem);
            $form .= $elem;
        }
        $html = str_replace('_____ELEMENTS___', $form, $html);
        die($html);
    }

    private static function __reduce($code) {
        foreach (array_keys($_SESSION[CSRF::$sesskey]) as $key) {
            $reduce = ($code == $key)
                ? CSRF::$active_decrease
                : CSRF::$unused_decrease
            ;
            $_SESSION[CSRF::$sesskey][$key] -= $reduce;
        }
    }

    private static function __is_ajax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
    }

    private static function __cleanup() {
        $del_candidates = array();
        foreach ($_SESSION[CSRF::$sesskey] as $key => $count) {
            if ($count < 0) {
                $del_candidates[] = $key;
            }
        }
        foreach ($del_candidates as $cand) {
            unset($_SESSION[CSRF::$sesskey][$cand]);
        }

    }

    private function __getkey($key) {
        return $_SESSION[CSRF::$sesskey][$key];
    }
    private function __setkey($key, $value) {
        if (!isset($_SESSION[CSRF::$sesskey])) {
            $_SESSION[CSRF::$sesskey] = array();
        }
        $csrfdata                 = $_SESSION[CSRF::$sesskey];
        $csrfdata[$key]           = $value;
        $_SESSION[CSRF::$sesskey] = $csrfdata;
    }
}

