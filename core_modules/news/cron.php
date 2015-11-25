<?php

require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';

init(\Cx\Core\Core\Controller\Cx::MODE_MINIMAL);

if (!defined('FRONTEND_LANG_ID')) {
    define('FRONTEND_LANG_ID', 1);
}

//To create/update the news RSS feed
$objNews = new newsManager();
$objNews->createRSS();