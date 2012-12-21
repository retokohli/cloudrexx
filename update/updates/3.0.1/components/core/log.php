<?php

function _logUpdate()
{

    /********************************
     * EXTENSION:   Timezone        *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'log` CHANGE `datetime` `datetime` TIMESTAMP NULL DEFAULT "0000-00-00 00:00:00"');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

}
