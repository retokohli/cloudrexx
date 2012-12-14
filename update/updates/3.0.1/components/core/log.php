<?php

function _logUpdate()
{

    //Contrexx 3.0.1 (timezone)
    try {
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'log` CHANGE `datetime` `datetime` TIMESTAMP NULL DEFAULT "0000-00-00 00:00:00"');
    } catch (UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

}
