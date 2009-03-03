<?PHP

define('DBG_PHP'           , 1);
define('DBG_ADODB'         , 2);
define('DBG_ADODB_TRACE'   , 4);
define('DBG_LOG_FILE'      , 8);
define('DBG_LOG_FIREPHP'   , 16);
define('DBG_ALL'           , 31);

// Redirect ADODB output to us instead of STDOUT.
define('ADODB_OUTP', 'DBG_log_adodb');

class DBG {
	private static $dbg_fh = null;
    private static $fileskiplength = 0;

	private static $enable_msg   = null;
	private static $enable_trace = null;
	private static $enable_dump  = null;
	private static $enable_time  = null;
    private static $firephp      = null;
    private static $last_time    = null;
    private static $start_time   = null;

    public function __construct($enable_firephp = true) {
        throw new Exception('This is a static class! No need to create an object!');
    }

    static function enable_firephp() {
        if (require_once('firephp/FirePHP.class.php')) {
            self::$firephp = FirePHP::getInstance(true);
        }
    }

    static function enable_all() {
        self::enable_msg  ();
        self::enable_trace();
        self::enable_dump ();
        self::enable_time ();
    }

    static function time() {
		if(self::$enable_time) {
            $t = self::$last_time;
            self::$last_time = microtime(true);
            $diff_last  = round(self::$last_time  - $t, 5);
            $diff_start = round(self::$last_time-self::$start_time, 5);
			$callers = debug_backtrace();
			$f = self::_cleanfile($callers[0]['file']);
			$l = $callers[0]['line'];
            $d = date('H:i:s');

			self::_log("TIME AT: $f:$l $d (diff: $diff_last, startdiff: $diff_start)");
		}
    }

	static function setup($file, $mode='a') {
		if (self::$dbg_fh) fclose(self::$dbg_fh);
	    if(!is_null(self::$firephp)) return true; //no need to setup ressources, we're using firephp
		self::$dbg_fh = fopen($file, $mode);
        $cinfo = debug_backtrace();

        self::$fileskiplength = strlen(dirname($cinfo[0]['file'])) +1;
	}

	static function enable_trace() {
        self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING TRACE XXXXXXXXXXXXXXXX');
		self::$enable_trace = 1;
	}

	static function disable_trace() {
		self::$enable_trace = 0;
	}

	static function enable_time() {
        self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING TIME XXXXXXXXXXXXXXXXX');
        if (!self::$enable_time) {
            self::$enable_time = 1;
            self::$start_time = microtime(true);
            self::$last_time  = microtime(true);
        }
	}

	static function enable_dump() {
        self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING DUMP XXXXXXXXXXXXXXXXX');
		self::$enable_dump = 1;
	}

	static function disable_dump() {
		self::$enable_dump = 0;
	}

	static function enable_msg() {
        self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING MSG XXXXXXXXXXXXXXXXXX');
		self::$enable_msg = 1;
	}

	static function disable_msg() {
		self::$enable_msg = 0;
	}

    static function _cleanfile($f) {
        return substr($f, self::$fileskiplength);
    }

	static function trace($level=0) {
		if (self::$enable_trace) {
			$callers = debug_backtrace();
			$f = self::_cleanfile($callers[$level]['file']);
			$l = $callers[$level]['line'];
			self::_log("TRACE:  $f : $l");
		}
	}

    static function calltrace() {
		if (self::$enable_trace) {
			$level = 1;
			$callers = debug_backtrace();
			$c = $callers[$level]['class'];
			$f = $callers[$level]['function'];
            self::trace($level);

            $sf = self::_cleanfile($callers[$level]['file']);
            $sl = $callers[$level]['line'];
			self::_log("        $c::$f FROM $sf : $sl");
		}
    }

    static function dump($val){
        if (!self::$enable_dump) return;

        if(!is_null(self::$firephp)){
            self::$firephp->log($val);
            return;
        }

        ob_start();
        print_r($val);
        $out = ob_get_clean();
        $out = str_replace("\n", "\n        ", $out);
        self::_log('DUMP:   '.$out);
    }

    static function stack(){
        $callers = debug_backtrace();
        self::_log("TRACE:  === STACKTRACE BEGIN ===");
        $err = error_reporting(E_ALL ^ E_NOTICE);
        foreach ($callers as $c) {
            $file = self::_cleanfile($c['file']);
            $line = $c['line'];
            $class= $c['class'];
            $func = $c['function'];
            self::_log("        $file : $line ($class::$func)");
        }
        error_reporting($err);
        self::_log("        === STACKTRACE END ====");
    }

	static function msg($message) {
		if(self::$enable_msg) {
			self::_log('LOGMSG: '.$message);
		}

	}

	static function log($text, $firephp_action = 'log', $additional_args = null) {
	    self::_log($text, $firephp_action, $additional_args);
	}

	private static function _log($text, $firephp_action = 'log', $additional_args = null) {
        if(!is_null(self::$firephp) && method_exists(self::$firephp, $firephp_action)){
            self::$firephp->$firephp_action($additional_args, $text);
        }else{
            if (self::$dbg_fh) {
                fputs(self::$dbg_fh, $text."\n");
            }
        }
	}
}

function DBG_log_adodb($msg, $newline) {
    DBG::log(strip_tags($msg));
}

