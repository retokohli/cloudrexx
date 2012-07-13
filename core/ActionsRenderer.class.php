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
        
        $actions[] = sprintf('<li class="action-item new" onclick="cx.cm.showEditor();jQuery(\'#parent_node\').val(' . $page->getNode()->getId() . ');return false;">%3$s</li>', ' new', '', $_ARRAYLANG['TXT_CORE_CM_NEW']);

        if ($page->isActive()) {
            if ($page->getEditingStatus() == 'hasDraft' || $page->getEditingStatus() == 'hasDraftWaiting') {
                $actions[] = sprintf(self::$actionItem, ' publish', "index.php?cmd=jsondata&object=page&act=set&action=publish&pageId={$page->getId()}", $_ARRAYLANG['TXT_CORE_PUBLISH_DRAFT']);
            } else {
                $actions[] = sprintf(self::$actionItem, ' deactivate', "index.php?cmd=jsondata&object=page&act=set&action=deactivate&pageId={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_UNPUBLISH']);
            }
        } else {
            if ($page->getEditingStatus() == 'hasDraft' || $page->getEditingStatus() == 'hasDraftWaiting') {
                $actions[] = sprintf(self::$actionItem, ' publish', "index.php?cmd=jsondata&object=page&act=set&action=publish&pageId={$page->getId()}", $_ARRAYLANG['TXT_CORE_PUBLISH_DRAFT']);
            } else {
                $actions[] = sprintf(self::$actionItem, ' activate', "index.php?cmd=jsondata&object=page&act=set&action=activate&pageId={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_PUBLISH']);
            }
        }

        if ($page->isVisible()) {
            $actions[] = sprintf(self::$actionItem, ' hide', "index.php?cmd=jsondata&object=page&act=set&action=hide&pageId={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_HIDE']);
        } else {
            $actions[] = sprintf(self::$actionItem, ' show', "index.php?cmd=jsondata&object=page&act=set&action=show&pageId={$page->getId()}", $_ARRAYLANG['TXT_CORE_CM_SHOW']);
        }

        $actions[] = sprintf(self::$actionItem, ' delete', "index.php?cmd=jsondata&object=node&act=delete&id={$page->getNode()->getId()}", $_ARRAYLANG['TXT_CORE_CM_DELETE']);

        return self::$header.implode("\n",$actions).self::$footer;
    }

    static function renderNew($nodeId, $langId)
    {
        global $_ARRAYLANG;
        
        $actions = array();
        $actions[] = sprintf(self::$actionItem, ' activate', "index.php?cmd=jsondata&object=page&act=set&action=publish&nodeId={$nodeId}&lang={$langId}", $_ARRAYLANG['TXT_CORE_CM_PUBLISH']);
        $actions[] = sprintf(self::$actionItem, ' delete', "index.php?cmd=jsondata&object=node&act=delete&id={$nodeId}", $_ARRAYLANG['TXT_CORE_CM_DELETE']);

        return self::$header.implode("\n",$actions).self::$footer;
    }
}