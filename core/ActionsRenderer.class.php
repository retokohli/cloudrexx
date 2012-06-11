<?php
require_once ASCMS_MODEL_PATH."/entities/Cx/Model/ContentManager/Page.php";

use Cx\Model\ContentManager\Page;

class ActionsRenderer
{
    protected static $header = '<ul>';
    protected static $actionItem = '<li class="action-item %1$s" data-href="%2$s">%3$s</li>';
    protected static $footer = '</ul>';

    static function render($page)
    {
        global $_ARRAYLANG;

        $actions = array();

        if ($page->isActive()) {
            $actions[] = sprintf(self::$actionItem, ' deactivate', "index.php?cmd=jsondata&object=page&act=setPageStatus&action=unpublish&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_UNPUBLISH']);
        } else {
            $actions[] = sprintf(self::$actionItem, ' activate', "index.php?cmd=jsondata&object=page&act=setPageStatus&action=publish&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_PUBLISH']);
        }

        if ($page->isVisible()) {
            $actions[] = sprintf(self::$actionItem, ' hide', "index.php?cmd=jsondata&object=page&act=setPageStatus&action=hidden&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_HIDE']);
        } else {
            $actions[] = sprintf(self::$actionItem, ' show', "index.php?cmd=jsondata&object=page&act=setPageStatus&action=visible&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_SHOW']);
        }

        if ($page->getEditingStatus() == 'hasDraftWaiting') {
            $actions[] = sprintf(self::$actionItem, ' publish', "index.php?cmd=content&act=publishDraft&page={$page->getId()}", $_ARRAYLANG['TXT_CORE_PUBLISH_DRAFT']);
        }

        $actions[] = sprintf(self::$actionItem, ' delete', "index.php?cmd=jsondata&object=node&act=delete&id={$page->getNode()->getId()}", $_ARRAYLANG['TXT_CORE_CM_DELETE']);

        return self::$header.implode("\n",$actions).self::$footer;
    }

    static function renderNew($nodeId, $langId)
    {
        global $_ARRAYLANG;
        
        $actions = array();
        $actions[] = sprintf(self::$actionItem, ' activate', "index.php?cmd=jsondata&object=page&act=setPageStatus&action=publish&node=$nodeId&lang=$langId", $_ARRAYLANG['TXT_CORE_CM_PUBLISH']);
        $actions[] = sprintf(self::$actionItem, ' delete', "index.php?cmd=jsondata&object=node&act=delete&id={$nodeId}", $_ARRAYLANG['TXT_CORE_CM_DELETE']);

        return self::$header.implode("\n",$actions).self::$footer;
    }
}