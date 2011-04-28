<?php
if(!function_exists("json_encode")) {
    require_once(ASCMS_LIBRARY_PATH.'/PEAR/Services/JSON.php');

    class JsonWrapper {
        protected $pearJSON;
        protected static $instance;
        protected function __construct() {
            $this->pearJSON = new Services_JSON();
        }

        public static function getInstance() {
            if(!self::$instance)
                self::$instance = new JsonWrapper();

            return self::$instance;
        }

        public function encode($obj) {
            return $this->pearJSON->encode($obj);
        }
        public function decode($str) {
            return $this->pearJSON->decode($str);
        }
    }

    function json_encode($obj) {
        return JsonWrapper::getInstance()->encode($obj);
    }

    function json_decode($str) {
        return JsonWrapper::getInstance()->encode($str);
    }
}