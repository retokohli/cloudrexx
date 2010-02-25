<?PHP

define('PARTNERS_TRIGGERS_DIR', dirname(dirname(__file__)).'/triggers/');

class Trigger {
    static function event($act, $target, $subject) {
        global $objDatabase;
        DBG::msg("trigger: {$act}_{$target}");

        $evt = "{$act}_{$target}";

        if (file_exists(PARTNERS_TRIGGERS_DIR.$evt.'.php')) {
            DBG::msg("trying to load code for $evt");
            include_once(PARTNERS_TRIGGERS_DIR.$evt.'.php');
        }

        if (function_exists($evt)) {
            DBG::msg("calling handler for $evt");
            $evt($subject, $objDatabase);
        }
    }
}

