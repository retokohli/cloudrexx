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
        
        // This shows the empty editor, sets parent node and hides actions menu
        $actions[] = sprintf('<li class="action-item new">%3$s</li>', ' new', '', $_ARRAYLANG['TXT_CORE_CM_NEW']);

        if ($page->getEditingStatus() == 'hasDraft' || $page->getEditingStatus() == 'hasDraftWaiting') {
            $actions[] = sprintf(self::$actionItem, ' publish', '', $_ARRAYLANG['TXT_CORE_PUBLISH_DRAFT']);
        } else {
            if ($page->isActive()) {
                $actions[] = sprintf(self::$actionItem, ' deactivate', '', $_ARRAYLANG['TXT_CORE_CM_UNPUBLISH']);
            } else {
                $actions[] = sprintf(self::$actionItem, ' activate', '', $_ARRAYLANG['TXT_CORE_CM_PUBLISH']);
            }
        }

        if ($page->isVisible()) {
            $actions[] = sprintf(self::$actionItem, ' hide', '', $_ARRAYLANG['TXT_CORE_CM_HIDE']);
        } else {
            $actions[] = sprintf(self::$actionItem, ' show', '', $_ARRAYLANG['TXT_CORE_CM_SHOW']);
        }

        $actions[] = sprintf(self::$actionItem, ' delete', '', $_ARRAYLANG['TXT_CORE_CM_DELETE']);

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