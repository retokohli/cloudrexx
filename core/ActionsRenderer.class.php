<?php
require_once ASCMS_MODEL_PATH."/entities/Cx/Model/ContentManager/Page.php";

use Cx\Model\ContentManager\Page;

class ActionsRenderer
{
    protected static $header = '<ul>';
    protected static $actionItem = '<li class="action-item" data-href="%s">%s</li>';
    protected static $footer = '</ul>';

    static function render($page)
    {
        global $_ARRAYLANG;

        $actions = array();

        if ($page->isActive()) {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=unpublish&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_UNPUBLISH']);
        } else {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=publish&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_PUBLISH']);
        }

        if ($page->isVisible()) {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=hidden&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_HIDE']);
        } else {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=visible&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_SHOW']);
        }

        $actions[] = sprintf(self::$actionItem, "index.php?cmd=jsondata&object=node&act=delete&id={$page->getNode()->getId()}", $_ARRAYLANG['TXT_CORE_CM_DELETE']);

        return self::$header.implode("\n",$actions).self::$footer;
    }

    static function renderNew($nodeId, $langId)
    {
        global $_ARRAYLANG;
        
        $actions = array();
        $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=publish&node=$nodeId&lang=$langId", $_ARRAYLANG['TXT_CORE_CM_PUBLISH']);
        $actions[] = sprintf(self::$actionItem, "index.php?cmd=jsondata&object=node&act=delete&id={$nodeId}", $_ARRAYLANG['TXT_CORE_CM_DELETE']);

        return self::$header.implode("\n",$actions).self::$footer;
    }
}