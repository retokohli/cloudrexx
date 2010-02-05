<?PHP

/**
 * This class helps transporting messages from one 
 * request to the next. The way it works is this:
 * - First request stores a message
 * - second request reads the stored messages. Reading
 *   the messages also deletes them, so stuff is never
 *   displayed twice.
 * Optionally, you can "tag" messages, so you know
 * whether it was an error message, success, and also
 * to keep messages from other modules private.
 */
class NGMessaging {
    /**
     * Saves a message that can be fetched again later.
     * @param string $msg - your message
     * @param string $tag - Tag your message "error", "success" 
     *                      or with your module's name to categorize them.
     */
    static function save($msg, $tag = "default") {
        if (isset($_SESSION['NGMessaging-DATA'])) {
            $data = $_SESSION['NGMessaging-DATA'];
        }
        else {
            $data = array();
        }
        $data[$tag][] = $msg;
        $_SESSION['NGMessaging-DATA'] = $data;
    }

    /**
     * Returns an array of messages stored for the given tag.
     * When read, the messages for the given tag are removed.
     * @param string $tag optional tag name.
     */
    static function fetch($tag = 'default') {
        if (isset($_SESSION['NGMessaging-DATA'][$tag])) {
            $data = $_SESSION['NGMessaging-DATA'][$tag];
            unset($_SESSION['NGMessaging-DATA'][$tag]);
            return $data;
        }
        else {
            return array();
        }
    }
}

