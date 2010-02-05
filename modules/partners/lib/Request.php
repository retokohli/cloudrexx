<?PHP

/**
 * This is a static class that helps reading request data.
 * Note that it automatically calls stripslashes() if needed,
 * so you always get un-screwed data.
 */
class Request {

    /**
     * Returns a variable from the GET parameters.
     */
    static function GET($key, $default = Null) {
        if (isset($_GET[$key]))
            return self::strip($_GET[$key]);
        return $default;
    }

    /**
     * Returns a variable from the POST parameters.
     */
    static function POST($key, $default = Null) {
        if (isset($_POST[$key]))
            return self::strip($_POST[$key]);
        return $default;
    }

    /**
     * Tries to get variable from GET, and if not found, tries
     * POST.
     */
    static function ANY($key, $default = Null) {
        if    (isset($_GET[$key]))    return self::strip($_GET [$key]);
        elseif(isset($_POST[$key]))   return self::strip($_POST[$key]);
        else                          return $default;
    }

    /**
     * Returns the given variable from POST. if not present,
     * this function will look it up in the session. If it is
     * in POST, put it in the session for caching.
     */
    static function cached_POST($key, $default = Null) {
        $val = Request::POST($key);
        if (is_null($val)) {
            if (isset($_SESSION["__REQUEST_CACHED_POST_$key"])) {
                return $_SESSION["__REQUEST_CACHED_POST_$key"];
            }
            return $default;
        }
        $_SESSION["__REQUEST_CACHED_POST_$key"] = $val;
        return $val;
    }

    /**
     * Returns the given variable from GET. if not present,
     * this function will look it up in the session. If it is
     * in GET, put it in the session for caching.
     */
    static function cached_GET($key, $default = Null) {
        $val = Request::GET($key);
        if (is_null($val)) {
            if (isset($_SESSION["__REQUEST_CACHED_GET_$key"])) {
                return $_SESSION["__REQUEST_CACHED_GET_$key"];
            }
            return $default;
        }
        $_SESSION["__REQUEST_CACHED_GET_$key"] = $val;
        return $val;
    }

    /**
     * Resets cached GET parameters (retreived with cached_GET())
     */
    static function reset_cached_GET($key) {
        unset($_SESSION["__REQUEST_CACHED_GET_$key"]);
    }
    /**
     * Resets cached POST parameters (retreived with cached_POST())
     */
    static function reset_cached_POST($key) {
        unset($_SESSION["__REQUEST_CACHED_POST_$key"]);
    }


    /**
     * Returns 'GET' or 'POST' depending on what kind
     * of request is currently going on.
     */
    static function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Returns true if the current request is a POST request.
     */
    static function is_post() {
        return self::method() == 'POST';
    }

    /**
     * Returns true if the current request is an AJAX call.
     */
    static function is_ajax() {
        $key = 'HTTP_X_REQUESTED_WITH';
        return (isset($_SERVER[$key]) and strtolower($_SERVER[$key]) == 'xmlhttprequest');
    }

    private static function strip($data) {
        if (!get_magic_quotes_gpc()) {
            return $data;
        }
        if (is_array($data)) {
            $out = array();
            foreach ($data as $k => $v) {
                $out[$k] = self::strip($v);
            }
            return $out;
        }
        else {
            return stripslashes($data);
        }

    }
}
