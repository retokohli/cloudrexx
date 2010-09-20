<?php

class PartnerSettings
{
    private $__table = null;
    private $__data  = array();

    function __construct() {
        $this->__table = DBPREFIX."module_partners_settings";

        $rs = NGDb::query("SELECT `key`, `value` FROM {$this->__table}");
        foreach ($rs as $setting) {
            $this->__data[$setting->key] = $setting->value;
        }
    }

    function __get($key) {
        return unserialize($this->__data[$key]);
    }

    function __set($key, $value) {
        $sql = NGDb::parse("
            REPLACE INTO `{$this->__table}`
            SET `key`   = %0,
                `value` = %1
                ",
                $key, serialize($value)
        );
        NGDb::execute($sql);
        $this->__data[$key] = serialize($value);
    }

}

?>
