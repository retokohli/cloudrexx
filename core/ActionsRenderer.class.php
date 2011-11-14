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
        $actions = array();

        if ($page->isActive()) {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=unpublish&page={$page->getId()}", "Unpublish");
        } else {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=publish&page={$page->getId()}", "Publish");
        }

        if ($page->isVisible()) {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=hidden&page={$page->getId()}", "Hide");
        } else {
            $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=visible&page={$page->getId()}", "Show");
        }

        $actions[] = sprintf(self::$actionItem, "index.php?cmd=jsondata&object=node&act=delete", "Delete Node");

        return self::$header.implode("\n",$actions).self::$footer;
    }

    static function renderNew($nodeId, $langId)
    {
        $actions = array();
        $actions[] = sprintf(self::$actionItem, "index.php?cmd=content&act=pageStatus&action=publish&node=$nodeId&lang=$langId", "Publish");
        $actions[] = sprintf(self::$actionItem, "index.php?cmd=jsondata&object=node&act=delete", "Delete Node");

        return self::$header.implode("\n",$actions).self::$footer;
    }
}